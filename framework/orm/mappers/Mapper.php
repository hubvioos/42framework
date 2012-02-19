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

namespace framework\orm\mappers;

class MapperException extends \Exception
{
	
}

/**
 * Class Mapper
 * 
 * This class is the base class every mapper has to inherit in order to work properly.
 * It extends \framework\core\FrameworkObject so the common $this->getComponent() 
 * and $this->getConfig() are easily accessible
 */
abstract class Mapper extends \framework\core\FrameworkObject implements \framework\orm\mappers\IMapper
{

	const CREATE = 1;
	const UPDATE = 2;

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
	 * The key used to retrieve the model from the components container.
	 * @var string
	 */
	protected $modelName = '';

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
	 * 							'relation'		=> '\framework\orm\models\IAttachableModel::RELATION_HAS_ONE',
	 * 							'internal'		=> true
	 * 						),
	 * 		'category'	=> array(
	 * 							'storageField'	=> 'post_category_id',
	 * 							'type'			=> 'Category',
	 * 							'relation'		=> '\framework\orm\models\IAttachableModel::RELATION_HAS_ONE',
	 * 							'internal'		=> true
	 * 						),
	 * 		'comments'	=> array(
	 * 							'storageField'	=> user_id,
	 * 							'type'			=> 'Comment',
	 * 							'relation'		=> '\framework\orm\models\IAttachableModel::RELATION_HAS_MANY',
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
	 * Constructor
	 * @param \framework\orm\datasources\interfaces\IDatasource $datasource The datasource used to store the data.
	 * @throws \framework\orm\mappers\MapperException 
	 */
	public function __construct (\framework\orm\datasources\interfaces\IDatasource $datasource)
	{
		$this->datasource = $datasource;

		if (!isset($this->fields) || $this->fields === array() || $this->fields === NULL)
		{
			throw new \framework\orm\mappers\MapperException('No fields specified.');
		}

		// create the fields map
		$this->fields = new \framework\orm\utils\Map($this->fields);
		$this->nonRelations = clone $this->fields;

		// establish a list of the relations
		foreach ($this->fields as $name => $specs)
		{
			if ($specs['relation'] !== NULL)
			{
				if ($specs['internal'] == true)
				{
					if ($this->internalRelations == NULL)
					{
						$this->internalRelations = new \framework\orm\utils\Map();
					}

					$this->internalRelations->addPropertyFromArray($name, $specs);
				}
				elseif ($specs['internal'] == false)
				{
					if ($this->externalRelations == NULL)
					{
						$this->externalRelations = new \framework\orm\utils\Map();
					}

					$this->externalRelations->addPropertyFromArray($name, $specs);
				}

				$this->nonRelations->removeProperty($name);
			}
		}
		
		$this->init();
	}

    /**
     * Attach a new object to the mapper.
     * @param array|\Traversable|\framework\orm\models\IAttachableModel $models
     */
	public function attach ($models)
	{
        if($models instanceof \framework\orm\models\IAttachableModel)
        {
            if($models->getId() !== NULL)
            {
                $this->attachedModels[(string) $models->getId()] = $models;
            }
            else
            {
                $this->attachedModels[] = $models;
            }
        }
        if(\is_array($models) || $models instanceof \Traversable)
        {
            foreach($models as $model)
            {
                $this->attach($model);
            }
        }
	}

	/**
	 * Dettach an object from the mapper.
	 * @param int|string|array|\Traversable|\framework\orm\models\IAttachableModel $models The model to detach or its id or a traversable variable of these
	 * @return bool
	 */
	public function detach ($models)
	{
		if ($models instanceof \framework\orm\models\IAttachableModel && $this->isAttached($models))
		{
			unset($this->attachedModels[$models->getId()]);
			return true;
		}
        elseif (\is_scalar($models) && $this->isAttached($models))
        {
            unset($this->attachedModels[(string) $models]);
            return true;
        }
        elseif(\is_array($models) || $models instanceof \Traversable)
        {
            foreach($models as $model)
            {
                $this->detach($model);
            }
            return true;
        }

		return false;
	}

	/**
	 * Retrieve a model from the datasource based on its id.
	 * @param mixed $toFind An array of IDs or a unique ID.
	 * @param bool $attach Attach the retrieved model(s) if found.
	 * @return \framework\orm\models\IAttachableModel|\framework\orm\utils\Collection
	 */
	public function find ($toFind, $attach = true)
	{
		$alreadyFound = array();

        /** @var $attached \framework\orm\utils\Collection */
		$attached = $this->getComponent('orm.utils.Collection');

		/** @var $found \framework\orm\utils\Collection */
        $found = $this->getComponent('orm.utils.Collection');
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

				if($this->externalRelations != NULL)
				{
					$this->_findExternalRelations($newModel);
				}

				if ($attach)
				{
					$this->attach($newModel);
				}

				$found[] = $newModel;
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
     * @param bool $attach
     * @return \framework\orm\utils\Collection
     */
	public function findAll (\framework\orm\utils\Criteria $criteria = null, $attach = true)
	{
		$data = $this->datasource->findAll($this->getEntityIdentifier(), $criteria);
		$results = $this->getComponent('orm.utils.Collection');

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
				elseif($attach)
				{
					$this->attach($model);
				}
				
				$results[] = $model;
			}
		}

		return $results;
	}
	
	/**
	 * Find all the models by a PROPERTY value
	 * @param string $property
	 * @param mixed $value
	 * @return \framework\orm\utils\Collection 
	 */
	public function findBy($property, $value)
	{
		$criteria = $this->datasource->getNativeCriteria();
		$criteria->equals($this->fields[$property]['storageField'], $value);
		
		return $this->findAll($criteria);
	}
	
	/**
	 * Save a model in the datasource. 
	 * @param \framework\orm\models\IAttachableModel The model to save.
	 * @return \framework\orm\models\IAttachableModel The saved model on succes, NULL on failure.
	 */
	public function save (\framework\orm\models\IAttachableModel $model)
	{
		$r = NULL;
		
		if ($model->getId() === NULL)
		{
			$r = $this->_persist($model, self::CREATE);
		}
		else
		{
			$r = $this->_persist($model, self::UPDATE);
		}

		return $r;
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
	 * @param string|int|\framework\orm\utils\Criteria|\framework\orm\models\IAttachableModel|\Traversable|array
     * @return bool
	 */
	public function delete ($models)
	{
		if ($models instanceof \framework\orm\utils\Criteria)
		{
			return $this->_deleteCriteria($models);
		}
		elseif ($models instanceof \framework\orm\models\IAttachableModel)
        {
            if($this->_deleteModel($models->getId()))
            {
                $this->detach($models);
                return true;
            }

            return false;
        }
        elseif(\is_scalar($models))
        {
            if($this->_deleteModel($models))
            {
                $this->detach($models);
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
		if(\is_scalar($model))
		{
			return (\array_key_exists((string) $model, $this->attachedModels)
                && !is_null($this->attachedModels[(string) $model]));
		}
		elseif($model instanceof \framework\orm\models\IAttachableModel)
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
	 * @return \framework\orm\models\IAttachableModel
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
	 * Method that can be overriden by children classes and which is called at the end of the constructor.
	 */
	public function init ()
	{
		
	}
	
	/**
	 * Find the external relations of a model
	 * @param \framework\orm\models\IAttachableModel $model 
	 */
	protected function _findExternalRelations(\framework\orm\models\IAttachableModel $model)
	{
		foreach($this->externalRelations as $name => $spec)
		{
			$relationMapper = $this->getMapper($spec['type']);
			$key = $relationMapper->getDatasource()->getNativeCriteria()->equals($spec['storageField'], $model->getId());
			$relations = $relationMapper->findAll($key);
			
			if(\count($relations) > 0)
			{
				if($spec['relation'] == \framework\orm\models\IAttachableModel::RELATION_HAS_ONE)
				{
					$relations = $relations[0];
				}
			}
			
			$model->{$this->_propertySetter($name)}($relations);
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
	 * @param \framework\orm\models\IAttachableModel $model 
	 */
	protected function _saveInternalRelations($model)
	{
		foreach ($this->internalRelations as $name => $spec)
		{
			$relationMapper = $this->getMapper($spec['type']);
			$relations = $model->{$this->_propertyGetter($name)}();
			$saved = array();

			foreach ($this->_wrapInArray($relations) as $relation)
			{
				$saved[] = $relationMapper->save($relation);
			}
			
			if($spec['relation'] == \framework\orm\models\IAttachableModel::RELATION_HAS_ONE)
			{
				$saved = $saved[0];
			}
			
			$model->{$this->_propertySetter($name)}($saved);
		}
	}
	
	/**
	 * Save the external relations of a model
	 * @param \framework\orm\models\IAttachableModel $model
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
					'relation' => \framework\orm\models\IAttachableModel::RELATION_HAS_ONE,
					'type' => \framework\orm\types\Type::RELATION_KEY,
					'storageField' => $spec['storageField']
				);

				$id = false;
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
				
			if($spec['relation'] == \framework\orm\models\IAttachableModel::RELATION_HAS_ONE)
			{
				$saved = $saved[0];
			}

			$model->{$this->_propertySetter($name)}($saved);

		}
	}
	
	
	/**
	 * Perform the create and update operation in the datasource
	 * @param \framework\orm\models\IAttachableModel $model
	 * @param int $mode self::CREATE || self::UPDATE
	 * @return \framework\orm\models\IAttachableModel
	 * @throws \framework\orm\mappers\MapperException 
	 */
	protected function _persist (\framework\orm\models\IAttachableModel $model, $mode = self::CREATE)
	{
		if ($mode != self::CREATE && $mode != self::UPDATE)
		{
			throw new \framework\orm\mappers\MapperException('Wrong save mode.');
		}

		// persist the internal relations first since we might need their IDs
		// when saving the model afterwards
		if ($this->internalRelations != NULL)
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
			if ($this->datasource->update($model->getId(), $this->getEntityIdentifier(), $this->_modelToMap($model), NULL) == true)
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

		return $model;
	}
	
	/**
	 * Transform a datasource map to a PHP-friendly model
	 * @param array|\framework\orm\utils\Map $map
	 * @return \framework\orm\models\IAttachableModel
	 */
	protected function _mapToModel ($map)
	{
		// get a new model's instance
		$model = $this->getModel($this->getModelName());

		// set all of its properties
		foreach ($this->nonRelations as $name => $spec)
		{
			$setter = $this->_propertySetter($name);

			if (\array_key_exists($spec['storageField'], $map) || \array_key_exists($name, $map))
			{
				if (\method_exists($model, $setter))
				{
					// if a particular type if specified, convert it to a PHP format
					if ($spec['type'] !== \framework\orm\types\Type::UNKNOWN
							&& !\in_array($spec['type'], $this->getComponent('orm.transparentTypes')))
					{
						$model->$setter($this->getComponent($spec['type'])->convertToPHP($map[$spec['storageField']]['value']));
					}
					// else, use the value as provided
					else
					{
						$value = $map[$name]['value'];
						
						// convert numeric types into actual numeric values (int, float)
						if(\in_array($spec['type'], $this->getComponent('orm.numericTypes')))
						{
							$value = $value + 0;
						}
						$model->$setter($value);
					}
				}
				else
				{
					throw new \framework\orm\mappers\MapperException('Missing method ' . $setter
							. ' in model ' . \get_class($model));
				}
			}
		}

		// delay the relations mapping because we might need the model's id
		if ($this->internalRelations != NULL)
		{
			foreach ($this->internalRelations as $name => $spec)
			{
				$setter = $this->_propertySetter($name);

				if (\array_key_exists($spec['storageField'], $map))
				{
					if (\method_exists($model, $setter))
					{
						$relationMapper = $this->getMapper($spec['type']);
						$entities = NULL;

						if ($spec['relation'] == \framework\orm\models\IAttachableModel::RELATION_HAS_ONE)
						{
							if ($spec['internal'])
							{
								$entities = $relationMapper->find($map[$spec['storageField']]['value']);
							}
							else
							{
								$criteria = $this->getComponent('orm.utils.Criteria');
								$criteria->equals($spec['storageField'], $model->getId())->limit(1);
								$entities = $relationMapper->findAll($criteria);

								$entities = (\count($entities) == 0) ? NULL : $entities[0];
							}
						}
						elseif ($spec['relation'] == \framework\orm\models\IAttachableModel::RELATION_HAS_MANY)
						{
							if ($spec['internal'])
							{
								$entities = $this->getComponent('orm.utils.Collection');

								foreach ($map[$spec['storageField']]['value'] as $id)
								{
									$entities[] = $relationMapper->find($id);
								}
							}
							else
							{
								$criteria = $this->getComponent('orm.utils.Criteria');
								$criteria->equals($spec['storageField'], $model->getId());
								$entities = $relationMapper->findAll($criteria);
							}
						}

						$model->$setter($entities);
					}
					else
					{
						throw new \framework\orm\mappers\MapperException('Missing method ' . $setter
								. ' in model ' . \get_class($model));
					}
				}
			}
		}

		return $model;
	}

	/**
	 * Transform a PHP model to map of datasource-friendly values.
	 * @param \framework\orm\models\IAttachableModel $model 
	 * @return \framework\orm\utils\Map
	 */
	protected function _modelToMap (\framework\orm\models\IAttachableModel $model)
	{
		$map = new \framework\orm\utils\Map();

		foreach ($this->nonRelations as $name => $spec)
		{
			$getter = $this->_propertyGetter($name);

			// check if a getter is exists for the property
			if (\method_exists($model, $getter))
			{
				// if a particular type is specified, convert the value to a datasource-friendly format
				if ($spec['type'] !== \framework\orm\types\Type::UNKNOWN
						&& !\in_array($spec['type'], $this->getComponent('orm.transparentTypes')))
				{
					$map[$name]['value'] = $this->getComponent($spec['type'])->convertToStorage($model->$getter());
				}
				// else use the value as provided
				else
				{
					$map[$name]['value'] = $model->$getter();
				}

				$map[$name]['storageField'] = $spec['storageField'];
				$map[$name]['type'] = $spec['type'];
			}
			else
			{
				throw new \framework\orm\mappers\MapperException('Unable to retrieve ' . $name . ' property.');
			}
		}

		if ($this->internalRelations !== NULL)
		{
			foreach ($this->internalRelations as $name => $spec)
			{
				$getter = $this->_propertyGetter($name);

				if (\method_exists($model, $getter))
				{
					$relationMapper = $this->getMapper($spec['type']);
					$relations = $model->$getter();

					$map[$name]['storageField'] = $spec['storageField'];
					$map[$name]['internal'] = $spec['internal'];
					$map[$name]['type'] = $relationMapper->getEntityIdentifier();
					$map[$name]['value'] = array();

					if ($relations === NULL)
					{
						$map[$name]['value'] = NULL;
					}
					elseif (\is_array($relations) || $relations instanceof \Traversable)
					{
						foreach ($relations as $relation)
						{
							$map[$name]['value'][] = $relationMapper->_modelToMap($relation);
						}
					}
					else
					{
						$map[$name]['value'] = $relationMapper->_modelToMap($relations);
					}
				}
				else
				{
					throw new \framework\orm\mappers\MapperException('Missing method ' . $getter
							. ' in model ' . \get_class($model));
				}
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
		if(\strpos($method, 'findBy') === 0 && \is_scalar($arguments[0]) && !\is_object($arguments[0]))
		{
			return $this->findBy(\lcfirst(\substr($method, 6)), $arguments[0]);
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

}