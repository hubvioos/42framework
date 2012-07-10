<?php

/**
 * Copyright (C) 2011 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
 * 
 * 42framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * 42framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */

//TODO: add 'read-only' attribute in fields definition

namespace framework\orm\mappers;

class MapperException extends \Exception
{
	
}

/**
 * Class Mapper
 * 
 * This class is the base class every mapper has to inherit in order to work properly.
 */
abstract class Mapper implements \framework\orm\mappers\IMapper
{
	const CREATE = 1;
	const UPDATE = 2;

    /**
     * The ComponentContainer
     * @var \framework\libs\ComponentsContainer
     */
    protected $container;

	/**
	 * Array containing the models attached to the mapper.
	 * @var array
	 */
	protected $attachedModels = array();

	/**
	 * The datasource used to store the data.
	 * @var \framework\orm\datasources\interfaces\IDatasource
	 */
	protected $datasource = null;

	/**
	 * Contains all the data needed to translate the models from and to the datasource.
	 * Example for a PostMapper that manages posts of a blog :
	 * <pre>array(
	 * 		'id'		=> array(
	 * 							'storageField'	=> NULL, // value is not explicitely stored in the datasaource
	 * 							'primary'		=> true,
	 * 							'type'			=> \framework\orm\types\Type::INTEGER
	 * 						),
	 * 		'title'		=> array(
	 * 							'storageField'	=> 'post_title',
	 * 							'type'			=> \framework\orm\types\Type::STRING
	 * 						),
	 * 		'content'	=> array(
	 * 							'storageField'	=> 'post_content',
	 * 							'type'			=> \framework\orm\types\Type::MEDIUM_TEXT
	 * 						),
	 * 		'user'		=> array(
	 * 							'storageField'	=> 'post_user_id',
	 * 							'type'			=> 'User',
	 * 							'relation'		=> '\framework\orm\models\IModel::RELATION_HAS_ONE',
	 * 							'internal'		=> true
	 * 						),
	 * 		'category'	=> array(
	 * 							'storageField'	=> 'post_category_id',
	 * 							'type'			=> 'Category',
	 * 							'relation'		=> '\framework\orm\models\IModel::RELATION_HAS_ONE',
	 * 							'internal'		=> true
	 * 						),
	 * 		'comments'	=> array(
	 * 							'storageField'	=> user_id,
	 * 							'type'			=> 'Comment',
	 * 							'relation'		=> '\framework\orm\models\IModel::RELATION_HAS_MANY',
	 * 							'internal'		=> false
	 * 						)
	 * );</pre>
	 * 
	 * @var array
	 */
	protected $fields = NULL;

	/**
	 * A Map of the non-relations fields for the Model managed by this Mapper
	 * @var \framework\orm\utils\Map 
	 */
	protected $nonRelations = NULL;

	/**
	 * A Map of the internal relations fields for the Model managed by this Mapper
	 * @var \framework\orm\utils\Map
	 */
	protected $internalRelations = NULL;

	/**
	 * A Map of the external relations fields for the Model managed by this Mapper
	 * @var \framework\orm\utils\Map
	 */
	protected $externalRelations = NULL;

    /**
     * The default behavior towards relations mapping
     * @var bool
     */
    protected $fetchRelations = true;

    /**
     * Map of the original values when the models are retrieved from the datasource.
     * @var array
     */
    protected $originalMaps = array();

    /**
     * @var array
     */
    protected $tempMaps = array();

    /**
     * Constructor
     * @param \framework\libs\ComponentsContainer $container
     * @param \framework\orm\datasources\interfaces\IDatasource $datasource The datasource used to store the data.
     * @param array $additionalParams
     * @throws MapperException
     */
	public function __construct (\framework\libs\ComponentsContainer $container,
                                 \framework\orm\datasources\interfaces\IDatasource $datasource,
                                 $additionalParams = array())
	{
        $this->container = $container;
		$this->datasource = $datasource;

		if (!isset($this->fields) || $this->fields === array() || $this->fields === NULL)
		{
			throw new \framework\orm\mappers\MapperException('No fields specified.');
		}

		// create the fields map
		$this->fields = new \framework\orm\utils\Map($this->fields);
		$this->nonRelations = clone $this->fields;

		// establish a list of the relations
		foreach ($this->fields as $name => $spec)
		{
			if ($spec['relation'] !== NULL)
			{
				if ($spec['internal'] == true)
				{
					if ($this->internalRelations == NULL)
					{
						$this->internalRelations = new \framework\orm\utils\Map();
					}

					$this->internalRelations->addPropertyFromArray($name, $spec);
				}
				elseif ($spec['internal'] == false)
				{
					if ($this->externalRelations == NULL)
					{
						$this->externalRelations = new \framework\orm\utils\Map();
					}

					$this->externalRelations->addPropertyFromArray($name, $spec);
				}

				$this->nonRelations->removeProperty($name);
			}
		}
		
		$this->init($additionalParams);
	}

    /**
     * Attach a new object to the mapper.
     * @param array|\Traversable|\framework\orm\models\IModel $model
     */
	public function attach ($model)
	{
        if($model instanceof \framework\orm\models\IModel)
        {
            if(!$this->isAttached($model))
            {
                if($model->getId() !== NULL)
                {
                    $this->attachedModels[(string) $model->getId()] = $model;
                    // cache a map of the original model
                    $this->originalMaps[(string) $model->getId()] = $this->_modelToMap($model);
                }
                else
                {
                    $this->attachedModels[] = $model;
                }
            }
        }
        if(\is_array($model) || $model instanceof \Traversable)
        {
            foreach($model as $_model)
            {
                $this->attach($_model);
            }
        }
	}

    /**
     * Send a request directly to the datasource
     * @param string $req
     * @return \framework\orm\utils\Collection
     */
    public function exec ($req)
    {
        if($this->datasource instanceof \framework\orm\datasources\interfaces\IDbDatasource)
        {
            /** @var $found \framework\orm\utils\Collection */
            $found = $this->container->getComponent('orm.utils.Collection');
            /** @var $data \framework\orm\utils\Collection */
            $data = $this->datasource->exec($req);

            if(!$data->isEmpty())
            {
                foreach ($data as $map)
                {
                    $model = $this->_mapToModel($map);
                    $id = (string) $model->getId();

                    if($this->isAttached($id))
                    {
                        $model = $this->getAttachedModel($id);
                    }
                    else
                    {
                        if($this->fetchRelations === true && $this->externalRelations != NULL)
                        {
                            $this->_findExternalRelations($model);
                        }

                        $this->attach($model);
                    }

                    $found->add($model);
                }
            }

            return $found;
        }
        else
        {
            throw new MapperException('The datasource associated to this mapper doesn\'t support requests.');
        }
    }


    /**
	 * Retrieve a model from the datasource based on its id.
	 * @param mixed $toFind An array of IDs or a unique ID.
	 * @return \framework\orm\models\IModel|\framework\orm\utils\Collection
	 */
	public function find ($toFind)
	{
		$alreadyFound = array();

        /** @var $attached \framework\orm\utils\Collection */
		$attached = $this->container->getComponent('orm.utils.Collection');

		/** @var $found \framework\orm\utils\Collection */
        $found = $this->container->getComponent('orm.utils.Collection');
		$toFind = $this->_wrapInArray($toFind);
		
		$searchForUniqueModel = (\count($toFind) == 1);
		
		foreach($toFind as $index => $id)
		{
			if($this->isAttached($id))
			{
				$attached->add($this->attachedModels[(string) $id]);
				$alreadyFound[] = $index;
			}
		}
		
		foreach($alreadyFound as $index)
		{
			unset($toFind[$index]);
		}
		
		if(\count($toFind) != 0)
		{
			$data = $this->datasource->find($toFind, $this->getEntityIdentifier());
			
			foreach ($data as $map)
			{
				$newModel = $this->_mapToModel($map);
                $id = (string) $newModel->getId();

                if($this->isAttached($id))
                {
                    $newModel = $this->getAttachedModel($id);
                }
                else
                {
                    if($this->fetchRelations === true && $this->externalRelations != NULL)
                    {
                        $this->_findExternalRelations($newModel);
                    }

                    $this->attach($newModel);
                }


				$found->add($newModel);
			}

		}
		
		$found->merge($attached);

        // return NULL if a unique model was expected but not found
		if($searchForUniqueModel)
		{
			return $found->isEmpty() ? NULL : $found[0];
		}

        // else, return a Collection
		return $found;
	}

    /**
     * Retrieve several models from the datasource.
     * @param \framework\orm\utils\Criteria A set of constraints the results must match.
     * @return \framework\orm\utils\Collection
     */
	public function findAll (\framework\orm\utils\Criteria $criteria = null)
	{
		$data = $this->datasource->findAll($this->getEntityIdentifier(), $criteria);
		/** @var $found \framework\orm\utils\Collection */
        $found = $this->container->getComponent('orm.utils.Collection');

		if (!$data->isEmpty())
		{
			foreach ($data as $map)
			{
				$model = $this->_mapToModel($map);
				$id = (string) $model->getId();

                if($this->isAttached($id))
                {
                    $model = $this->getAttachedModel($id);
                }
                else
                {
                    if($this->fetchRelations === true && $this->externalRelations != NULL)
                    {
                        $this->_findExternalRelations($model);
                    }

                    $this->attach($model);
                }

				$found->add($model);
			}
		}

		return $found;
	}
	
	/**
	 * Find all the models by a PROPERTY value
	 * @param string $property
	 * @param mixed $value
	 * @return \framework\orm\utils\Collection 
	 */
	public function findBy($property, $value)
	{
        $property = \lcfirst($property);
        $name = '';

        if(\array_key_exists($property, $this->fields))
        {
            $name = $this->fields[$property]['storageField'];
        }
        else
        {
            $property = \ucfirst($property);
            if(\array_key_exists($property, $this->fields))
            {
                $name = $this->fields[$property]['storageField'];
            }
        }

        if($name === '')
        {
            throw new MapperException('Property "'.$property.'" not found in model.');
        }

        $criteria = $this->datasource->getNativeCriteria();
        $criteria->equals($name, $value);

        return $this->findAll($criteria);
	}
	
	/**
	 * Save a model in the datasource. 
	 * @param \framework\orm\models\IModel The model to save.
	 * @return \framework\orm\models\IModel The saved model on succes, NULL on failure.
	 */
	public function save (\framework\orm\models\IModel $model)
	{
		$r = NULL;

        if ($model->getId() === NULL)
        {
            $r = $this->_persist($model, self::CREATE);
        }
        else
        {
            if($this->_modelHasChanged($model))
            {
                $r = $this->_persist($model, self::UPDATE);
            }
            else
            {
                $r = $model;
            }

        }

		return $r;
	}

    /**
     * @param $model
     * @return bool
     */
    protected function _modelHasChanged(\framework\orm\models\IModel $model)
    {
        $map = $this->_modelToMap($model, true);
        $hasChanged = false;

        if(!$this->isAttached($model))
        {
            $this->attach($model);
            return true;
        }

        foreach($map as $name => $spec)
        {
            if(!$hasChanged)
            {
                $originalValue = $this->originalMaps[(string) $model->getId()][$name]['value'];
                $mapValue = $spec['value'];

                // no relation
                if(!isset($spec['relation']))
                {
                    if($originalValue !== $mapValue)
                    {
                        $hasChanged = true;
                    }
                }
                // HAS_ONE relation
                elseif($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_ONE)
                {
                    if($originalValue instanceof \framework\orm\utils\Map
                    && $mapValue instanceof \framework\orm\utils\Map)
                    {
                        $hasChanged = !($originalValue['id']['value'] == $mapValue['id']['value']);
                    }
                }
                // HAS_MANY relation
                else
                {
                    if(\is_array($originalValue) && \is_array($mapValue))
                    {
                        $grepId = function($map) {
                            return $map['id']['value'];
                        };

                        $originalIds = \array_map($grepId, $originalValue);
                        $mapIds = \array_map($grepId, $mapValue);

                        \sort($originalIds);
                        \sort($mapIds);

                        if($originalIds !== $mapIds)
                        {
                            $hasChanged = true;
                        }
                    }
                    else
                    {
                        //something went wrong...
                        $hasChanged = true;
                    }
                }
            }
        }

        return $hasChanged;
    }

	/**
	 * Save all the attached models
	 * @return boolean 
	 */
	public function saveAll()
	{
		foreach ($this->attachedModels as $id => $model)
		{
			$this->save($model);
		}
		
		return true;
	}

	/**
	 * Delete a model from the datasource.
	 * @param string|int|\framework\orm\utils\Criteria|\framework\orm\models\IModel|\Traversable|array
     * @return bool
	 */
	public function delete ($models)
	{
		if ($models instanceof \framework\orm\utils\Criteria)
		{
			return $this->_deleteCriteria($models);
		}
		elseif ($models instanceof \framework\orm\models\IModel)
        {
            if($this->_deleteModel($models->getId()))
            {
                if($this->isAttached($models))
                {
                    unset($this->attachedModels[(string) $models->getId()]);
                }
                return true;
            }

            return false;
        }
        elseif(\is_scalar($models))
        {
            if($this->_deleteModel($models))
            {
                if($this->isAttached($models))
                {
                    unset($this->attachedModels[(string) $models->getId()]);
                }
                return true;
            }

            return false;
        }
        elseif(\is_array($models) || $models instanceof \Traversable)
        {
            foreach($models as $model)
            {
                if($this->delete($model) === false)
                {
                    throw new \framework\orm\mappers\MapperException('An error occured while deleting a '
                        . $this->getModelName().' model');
                }
            }

            return true;
        }
		
		throw new \framework\orm\mappers\MapperException('Wrong parameter type.');
	}

	/**
	 * Check if a model is attached to the mapper
	 * @param mixed $model
	 * @return bool 
	 */
	public final function isAttached ($model)
	{
        if($model === NULL)
        {
            return false;
        }
        
		if(\is_scalar($model))
		{
			return (\array_key_exists((string) $model, $this->attachedModels)
                && !is_null($this->attachedModels[(string) $model]));
		}
		elseif($model instanceof \framework\orm\models\IModel)
		{
			return \in_array($model, $this->attachedModels, true);
		}
		
		throw new \framework\orm\mappers\MapperException('Mapper::isAttached() can only take a model or an ID as parameter.');
	}

	/**
	 * Get the fields mapping configuration
	 * @return array 
	 */
	public final function getFields ()
	{
		return $this->fields;
	}

	/**
	 * Get an attached model 
	 * @param string|int $id The model's id
	 * @return \framework\orm\models\IModel
	 */
	public final function getAttachedModel ($id)
	{
		if (\array_key_exists((string) $id, $this->attachedModels))
		{
			return $this->attachedModels[(string) $id];
		}

		return NULL;
	}

	/**
	 * Get all the attached models.
	 * @return \framework\orm\utils\Collection
	 */
	public final function getAttachedModels ()
	{
		return new \framework\orm\utils\Collection($this->attachedModels);
	}

	/**
	 * Get the datasource used by the mapper.
	 * @return \framework\orm\datasources\interfaces\IDatasource
	 */
	public final function getDatasource ()
	{
		return $this->datasource;
	}

    /**
     * Get a new instance of the model this Mapper manages.
     * @return \framework\orm\models\IModel
     */
    public function getModel()
    {
        return $this->container->getComponent('model.'.$this->getModelName());
    }

    /**
     * Get the Mapper that manages a Model class
     * @param $modelName
     * @return \framework\orm\mappers\Mapper
     */
    public function getMapper($modelName)
    {
        return $this->container->getComponent('mapper.'.$modelName);
    }

    /**
     * Defin the behavior towards relations mapping. This only applies on the relations finding. Saving operations
     * are not impacted.
     * @param bool $fetch
     * @throws \framework\orm\mappers\MapperException
     */
    public function fetchRelations($fetch = true)
    {
        $this->fetchRelations = (bool) $fetch;
    }

    /**
     * Method that can be overriden by children classes and which is called at the end of the constructor.
     * @param array $params
     */
	public function init (array $params = array())
	{
		
	}
	
	/**
	 * Find the external relations of a model
	 * @param \framework\orm\models\IModel $model
	 */
	protected function _findExternalRelations(\framework\orm\models\IModel $model)
	{
		foreach($this->externalRelations as $name => $spec)
		{
			$relationMapper = $this->getMapper($spec['type']);
			$key = $relationMapper->getDatasource()->getNativeCriteria()->equals($spec['storageField'], $model->getId());
			$relations = $relationMapper->findAll($key);

			if(\count($relations) > 0)
			{
				if($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_ONE)
				{
					$relations = $relations[0];
				}
			}

			$model->{$this->_propertySetter($name)}($relations);
		}
	}

    /**
     * Find the internal relations of a model
     * @param $model
     * @param $map
     */
    protected function _findInternalRelations($model, $map)
    {
        foreach ($this->internalRelations as $name => $spec)
        {
            if (\array_key_exists($spec['storageField'], $map))
            {
                $relationMapper = $this->getMapper($spec['type']);
                $values = $this->_wrapInArray($map[$spec['storageField']]['value']);

                if($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_ONE
                    && \count($values) > 1)
                {
                    $values = $values[0];
                }

                $entities = $relationMapper->find($values);

                // make sure we return a Collection for HAS_MANY relations
                if($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_MANY
                    && \count($this->_wrapInArray($entities)) == 1)
                {
                    $entities = new \framework\orm\utils\Collection($this->_wrapInArray($entities));
                }

                $model->{$this->_propertySetter($name)}($entities);
            }
        }
    }

	/**
	 * Delete a model 
	 * @param string|int $model
	 * @return bool
	 */
	protected function _deleteModel ($model)
	{
		return $this->datasource->delete($model, $this->getEntityIdentifier(), NULL);
	}

	/**
	 * Delete several models
	 * @param \framework\orm\utils\Criteria $criteria A set of contraints that the models must match
	 * @return bool
	 */
	protected function _deleteCriteria (\framework\orm\utils\Criteria $criteria)
	{
		return $this->datasource->delete(NULL, $this->getEntityIdentifier(), $criteria);
	}
	
	/**
	 * Save the internal relations of a model
	 * @param \framework\orm\models\IModel $model
	 */
	protected function _saveInternalRelations($model)
	{
		foreach ($this->internalRelations as $name => $spec)
		{
			$relationMapper = $this->getMapper($spec['type']);
			$relations = $model->{$this->_propertyGetter($name)}();

            /** @var $saved \framework\orm\utils\Collection */
			$saved = $this->container->getComponent('orm.utils.Collection');

            // save only the first element
            if($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_ONE && \count($relations) > 1)
            {
                $relations = $relations[0];
            }

			foreach ($this->_wrapInArray($relations) as $relation)
			{
				$saved->add($relationMapper->save($relation));
			}
			
			if($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_ONE && \count($saved) > 0)
			{
				$saved = $saved->first();
			}

			$model->{$this->_propertySetter($name)}($saved);
		}
	}
	
	/**
	 * Save the external relations of a model
	 * @param \framework\orm\models\IModel $model
	 * @throws \framework\orm\mappers\MapperException 
	 */
	protected function _saveExternalRelations($model)
	{
		foreach ($this->externalRelations as $name => $spec)
		{
			$relationMapper = $this->getMapper($spec['type']);
			$relations = $model->{$this->_propertyGetter($name)}();
			$saved = array();

			foreach ($this->_wrapInArray($relations) as $relation)
			{
				$map = $relationMapper->_modelToMap($relation);
				$map[$spec['storageField']] = array(
					'value' => $model->getId(),
					'relation' => $spec['relation'],
					'type' => \framework\orm\types\Type::RELATION_KEY,
					'storageField' => $spec['storageField']
				);

				if ($map['id']['value'] == NULL)
				{
					$id = $relationMapper->getDatasource()->create($relationMapper->getEntityIdentifier(), $map);
					$relation->setId($id);
				}
				else
				{
					$id = $relationMapper->getDatasource()->update($map['id']['value'], 
							$relationMapper->getEntityIdentifier(), $map);
				}

				if ($id === false)
				{
					throw new \framework\orm\mappers\MapperException('Unable to save relation of type '
							. $relationMapper->getModelName() . ' for ' . $this->getModelName()
							. ' model with id: ' . $model->getId());
				}
				$saved[] = $relation;
			}
				
			if($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_ONE && \count($saved) > 0)
			{
				$saved = $saved[0];
			}

			$model->{$this->_propertySetter($name)}($saved);

		}
	}
	
	
	/**
	 * Perform the create and update operation in the datasource
	 * @param \framework\orm\models\IModel $model
	 * @param int $mode self::CREATE || self::UPDATE
	 * @return \framework\orm\models\IModel
	 * @throws \framework\orm\mappers\MapperException 
	 */
	protected function _persist (\framework\orm\models\IModel $model, $mode = self::CREATE)
	{
		if ($mode != self::CREATE && $mode != self::UPDATE)
		{
			throw new \framework\orm\mappers\MapperException('Wrong save mode.');
		}

		// persist the internal relations first since we might need their IDs
		// when saving the model afterwards
		if ($this->internalRelations !== NULL)
		{
			$this->_saveInternalRelations($model);
		}

		// then persist the model since we might need its ID 
		// when saving its external relations afterwards
		$id = false;
		if ($mode == self::CREATE)
		{
			$id = $this->datasource->create($this->getEntityIdentifier(), $this->_modelToMap($model));
			$model->setId($id);
		}
		else
		{
            // TODO: did we really need this ?
            /*
            if(\array_key_exists($model->getId(), $this->tempMaps))
            {
                $map = $this->tempMaps[$model->getId()];
            }
            else
            {
                $map = $this->_modelToMap($model, true);
            }
            */

            $map = $this->_modelToMap($model, true);

			if ($this->datasource->update($model->getId(), $this->getEntityIdentifier(), $map, NULL) == true)
			{
				$id = $model->getId();
			}
		}

		if ($id === false)
		{
			throw new \framework\orm\mappers\MapperException('Failed to save ' . $this->getModelName() . ' model.');
		}

        // finally persist the external relations last using the model's ID
        if ($this->externalRelations != NULL)
        {
            $this->_saveExternalRelations($model);
        }

        // clear and update the cached maps
        if(\array_key_exists($model->getId(), $this->tempMaps))
        {
            $this->originalMaps[(string) $model->getId()] = $this->tempMaps[$model->getId()];
            unset($this->tempMaps[$model->getId()]);
        }
        else
        {
            $this->originalMaps[(string) $model->getId()] = $this->_modelToMap($model);
        }

		return $model;
	}
	
	/**
	 * Transform a datasource map to a PHP-friendly model
	 * @param array|\framework\orm\utils\Map $map
	 * @return \framework\orm\models\IModel
	 */
	protected function _mapToModel ($map)
	{
		// get a new model's instance
		$model = $this->getModel();

		// set all of its properties
		foreach ($this->nonRelations as $name => $spec)
		{
			$setter = $this->_propertySetter($name);

			if (\array_key_exists($spec['storageField'], $map) || \array_key_exists($name, $map))
			{
                $key = (\array_key_exists($spec['storageField'], $map)) ? $spec['storageField'] : $name;

			    // if a particular type if specified, convert it to a PHP format
                if ($map[$key]['value'] !== NULL && $spec['type'] !== \framework\orm\types\Type::UNKNOWN
                        && !\in_array($spec['type'], $this->container->getComponent('orm.transparentTypes')))
                {
                    $model->$setter($this->container->getComponent($spec['type'])
                        ->convertToPHP($map[$key]['value']));
                }
                else
                {
                    //use the value as provided
                    $value = $map[$key]['value'];

                    // convert numeric types into actual numeric values (int, float)
                    if(\in_array($spec['type'], $this->container->getComponent('orm.numericTypes')))
                    {
                        $value = $value + 0;
                    }
                    $model->$setter($value);
                }
			}
		}

		// delay the relations mapping because we might need the model's id
		if ($this->fetchRelations === true && $this->internalRelations != NULL)
		{
            $this->_findInternalRelations($model, $map);
		}

		return $model;
	}

    /**
     * Transform a PHP model to map of datasource-friendly values.
     * @param \framework\orm\models\IModel $model
     * @param bool $cache Whether or not the map should be cached
     * @return \framework\orm\utils\Map
     */
	protected function _modelToMap (\framework\orm\models\IModel $model, $cache = false)
	{
		$map = new \framework\orm\utils\Map();

		foreach ($this->nonRelations as $name => $spec)
		{
			$getter = $this->_propertyGetter($name);

			// check if a getter exists for the property
			if (\method_exists($model, $getter))
			{
                $value = $model->$getter();
                if ($value !== NULL && $spec['type'] !== \framework\orm\types\Type::UNKNOWN
						&& !\in_array($spec['type'], $this->container->getComponent('orm.transparentTypes')))
				{
					$map[$name]['value'] = $this->container->getComponent($spec['type'])->convertToStorage($value);
				}
				// else use the value as provided
				else
				{
					$map[$name]['value'] = $value;
				}

				$map[$name]['storageField'] = $spec['storageField'];
				$map[$name]['type'] = $spec['type'];
			}
			else
			{
				throw new \framework\orm\mappers\MapperException('Unable to retrieve ' . $name . ' property.');
			}
		}

        // we only need to map the internal relations
		if ($this->internalRelations !== NULL)
		{
			foreach ($this->internalRelations as $name => $spec)
			{
				$getter = $this->_propertyGetter($name);

                $relationMapper = $this->getMapper($spec['type']);
                $relations = $model->$getter();

                $map[$name]['storageField'] = $spec['storageField'];
                $map[$name]['internal'] = $spec['internal'];
                $map[$name]['relation'] = $spec['relation'];
                $map[$name]['type'] = $relationMapper->getEntityIdentifier();
                $map[$name]['value'] = array();

                if ($relations === NULL)
                {
                    $map[$name]['value'] = NULL;
                }
                elseif($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_MANY)
                {
                    foreach ($relations as $relation)
                    {
                        $map[$name]['value'][] = $relationMapper->_modelToMap($relation);
                    }
                }
                elseif($spec['relation'] == \framework\orm\models\IModel::RELATION_HAS_ONE)
                {
                    $map[$name]['value'] = $relationMapper->_modelToMap($relations);
                }
			}
		}

        if($cache === true)
        {
            if($model->getId() !== NULL)
            {
                $this->_cacheMap($map, $model->getId());
            }
        }

		return $map;
	}

	/**
	 * Close the connection on destruction if one is opened 
	 */
	public function __destruct ()
	{
		if ($this->datasource instanceof \framework\orm\datasources\interfaces\IConnectionDatasource)
		{
			$this->datasource->close();
		}
	}
	
	/**
	 * Magic!
	 * @param string $method
	 * @param array $arguments
	 * @return mixed 
	 */
	public function __call ($method, $arguments)
	{
		// findByFoo('bar')
		if(\strpos($method, 'findBy') === 0 && \count($arguments) != 0)
		{
			return $this->findBy(\substr($method, 6), $arguments[0]);
		}
	}

	/**
	 * Compute the name of the getter for $property
	 * @param string $property
	 * @return string
	 */
	protected function _propertyGetter ($property)
	{
		return 'get' . \ucfirst($property);
	}

	/**
	 * Compute the name of the setter for $property
	 * @param string $property
	 * @return string
	 */
	protected function _propertySetter ($property)
	{
		return 'set' . \ucfirst($property);
	}
	
	/**
	 * Return the passed argument wrapped in an array 
	 * if it's not already one or an instance of \Traversable
      	 * @param mixed $a
	 * @return array
	 */
	protected function _wrapInArray($a)
	{
		return (!\is_array($a) && !($a instanceof \Traversable)) ? array($a) : $a;
	}

    protected function _cacheMap($map, $id)
    {
        // cache the map so we don't need to compute it again
        $this->tempMaps[$id] = $map;
    }

}
