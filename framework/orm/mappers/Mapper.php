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

	/**
	 * Array containing the model attached to the mapper.
	 * @var array 
	 */
	protected $attachedModels = array();

	/**
	 * The datasource used to store the data.
	 * @var \framework\orm\datasources\IDatasource
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
	 * 							'internal'	=> true
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
	 * 							'internal'	=> false
	 * 						)
	 * );</pre>
	 * 
	 * @var array
	 */
	protected $fields = NULL;
	protected $relations = NULL;
	protected $nonRelations = NULL;

	/**
	 * Constructor
	 * @param \framework\orm\datasources\IDatasource $datasource The datasource used to store the data.
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
				if ($this->relations == NULL)
				{
					$this->relations = array();
				}

				$this->relations[$name] = $specs;
				$this->nonRelations->removeProperty($name);
			}
		}
		
		$this->init();
	}

	/**
	 * Attach a new object to the mapper.
	 * @param \framework\orm\models\IAttachableModel $object 
	 * @return string The key where the object is stored in the mapper.
	 */
	public function attach (\framework\orm\models\IAttachableModel $model)
	{
		$this->attachedModels[(string) $model->getId()] = $model;

		return (string) $model->getId();
	}

	/**
	 * Dettach an object from the mapper.
	 * @param mixed $model The model to detach or its id
	 * @return bool
	 */
	public function detach ($model)
	{
		if ($model instanceof \framework\orm\models\IAttachableModel && $this->isAttached($model))
		{
			unset($this->attachedModels[$model->getId()]);
			return true;
		}
		elseif (\array_key_exists((string) $model, $this->attachedModels))
		{
			unset($this->attachedModels[(string) $model]);
			return true;
		}

		return false;
	}

	/**
	 * Retrieve a model from the datasource based on its id.
	 * @param int|string $id The model's id.
	 * @param bool $attach Attach the retrieved model(s) if found.
	 * @return \framework\orm\models\IAttachableModel
	 */
	public function find ($id, $attach = true)
	{
		if(\is_scalar($id))
		{
			$id = array($id);
		}
		
		$data = $this->datasource->find($id, $this->getEntityIdentifier());

		if (\count($data) == 0)
		{
			return null;
		}
		else
		{
			$result = NULL;

			if (\count($data) == 1)
			{
				$result = $this->_mapToModel($data[0]);

				if ($attach)
				{
					$this->attach($result);
				}
			}
			else
			{
				$result = $this->getComponent('orm.utils.Collection');

				foreach ($data as $map)
				{
					$newModel = $this->_mapToModel($map);

					if ($attach)
					{
						$this->attach($newModel);
					}

					$result[] = $newModel;
				}
			}

			return $result;
		}
	}

	/**
	 * Retrieve several models from the datasource.
	 * @param \framework\orm\utils\Criteria A set of constraints the results must match. 
	 * @return \framework\orm\utils\Collection
	 */
	public function findAll (\framework\orm\utils\Criteria $criteria = null)
	{
		$data = $this->datasource->findAll($this->getEntityIdentifier(), $criteria);
		$results = $this->getComponent('orm.utils.Collection');

		if (\count($data) != 0)
		{
			foreach ($data as $map)
			{
				$results[] = $this->_mapToModel($map);
			}
		}
		
		return $results;
	}

	/**
	 * Save a model in the datasource. 
	 * @param \framework\orm\models\IAttachableModel The model to save.
	 */
	public function save (\framework\orm\models\IAttachableModel &$model)
	{
		$response = NULL;

		if ($model->getId() === NULL)
		{
			$response = $this->_create($model);

			if ($response !== false)
			{
				$model = $this->find($response);				
				return $model;
			}

			throw new \framework\orm\mappers\MapperException('Unable to save new model (' . $this->getModelName() . ')');
		}
		else
		{
			$response = $this->_update($model);
			
			if ($response === true)
			{
				return $model;
			}
			
			throw new \framework\orm\mappers\MapperException('Unable to save model ' . $this->getModelName()
					. ' with id ' . $model->getId());
		}
		
		return $response;
	}
	
	/**
	 *
	 * @param \framework\orm\models\IAttachableModel $model
	 * @return type 
	 */
	protected function _create(\framework\orm\models\IAttachableModel &$model)
	{
		if($this->relations !== NULL)
		{
			$this->_saveRelations($model);
		}
		
		return $this->datasource->create($this->getEntityIdentifier(), $this->_modelToMap($model));
	}
	
	/**
	 *
	 * @param \framework\orm\models\IAttachableModel $model
	 * @return type 
	 */
	protected function _update(\framework\orm\models\IAttachableModel &$model)
	{
		if($this->relations !== NULL)
		{
			$this->_saveRelations($model);
		}
		
		return $this->datasource->update($model->getId(), $this->getEntityIdentifier(), $this->_modelToMap($model));
	}
	
	protected function _saveRelations(&$model)
	{
		foreach($this->relations as $name => $spec)
		{
			$mapper = $this->getMapper($spec['type']);
			$toSave = $model->{'get'.\ucfirst($name)}();
			$saved = $this->getComponent('orm.utils.Collection');
			
			foreach($toSave as $relation)
			{
				$saved->add($mapper->save($relation));
			}
			
			$model->{'set'.\ucfirst($name)}($saved);
		}
	}

	/**
	 * Delete a model from the datasource.
	 * @param \framework\orm\utils\Criteria|\framework\orm\models\IAttachableModel 
	 */
	public function delete ($criteria)
	{
		if ($criteria instanceof \framework\orm\utils\Criteria)
		{
			return $this->_deleteCriteria($criteria);
		}
		else
		{
			if ($criteria instanceof \framework\orm\models\IAttachableModel)
			{
				$criteria = $criteria->getId();
			}

			return $this->_deleteModel($criteria);
		}
	}
	
	/**
	 * Check if a model is attached to the mapper
	 * @param \framework\orm\models\IAttachableModel $model
	 * @return bool 
	 */
	public final function isAttached (\framework\orm\models\IAttachableModel $model)
	{
		return \in_array($model, $this->attachedModels, true);
	}

	/**
	 * Get the fields mapping configuration
	 * @return array 
	 */
	public final function getFields ()
	{
		return $this->fields;
	}

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
	 * @return array
	 */
	public final function getAttachedModels ()
	{
		return $this->attachedModels;
	}

	/**
	 * Get the datasource used by the mapper.
	 * @return \framework\orm\datasources\IDatasource
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
	
	

	protected function _deleteModel ($model)
	{
		return $this->datasource->delete($model, NULL);
	}

	protected function _deleteCriteria (\framework\orm\utils\Criteria $criteria)
	{
		return $this->datasource->delete(NULL, $criteria);
	}

	/**
	 * Transform a datasource map to a PHP-friendly model
	 * @param array|\ArrayObject $map 
	 * @return \framework\orm\models\IAttachableModel
	 */
	protected function _mapToModel ($map)
	{
		// get a new model's instance
		$model = $this->getModel($this->getModelName());

		// set all of its properties
		foreach ($this->nonRelations as $name => $spec)
		{
			$setter = 'set' . \ucfirst($name);

			if (\array_key_exists($spec['storageField'], $map) || \array_key_exists($name, $map))
			{
				if(\method_exists($model, $setter))
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
						$model->$setter($map[$name]['value']);
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
		if(\is_array($this->relations))
		{
			foreach ($this->relations as $name => $spec)
			{
				$setter = 'set' . \ucfirst($name);

				if(\array_key_exists($spec['storageField'], $map))
				{
					if (\method_exists($model, $setter))
					{
						$relationMapper = $this->getMapper($spec['type']);
						$entities = NULL;

						if ($spec['relation'] == \framework\orm\models\IAttachableModel::RELATION_HAS_ONE)
						{
							if($spec['internal'])
							{
								//$entities = $relationMapper->find($map[$name['storageField']]['value']);
								$entities = $relationMapper->find($spec['value']);
							}
							else//if(!$spec['internal'])
							{
								$criteria = $this->getComponent('orm.utils.Criteria');
								$criteria->equals($spec['storageField'], $model->getId())->limit(1);
								$entities = $relationMapper->findAll($criteria);
							}
						}
						elseif ($spec['relation'] == \framework\orm\models\IAttachableModel::RELATION_HAS_MANY)
						{
							if($spec['internal'])
							{
								$entities = $this->getComponent('orm.utils.Collection');

								foreach($map[$spec['storageField']]['value'] as $id)
								{
									$entities[] = $relationMapper->find($id);
								}
							}
							else//if(!$spec['internal'])
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
		$getter = '';

		foreach ($this->nonRelations as $name => $spec)
		{
			$getter = 'get' . \ucfirst($name);

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
		
		if($this->relations !== NULL)
		{	
			foreach ($this->relations as $name => $spec)
			{
				$getter = 'get' . \ucfirst($name);

				if (\method_exists($model, $getter))
				{
					$relationMapper = $this->getMapper($spec['type']);
					$relations = $model->$getter();
					
					$map[$name]['storageField'] = $spec['storageField'];
					$map[$name]['internal'] = $spec['internal'];
					$map[$name]['type'] = $relationMapper->getEntityIdentifier();
					$map[$name]['value'] = array();


					if($relations === NULL)
					{
						$map[$name]['value'] = NULL;
					}
					elseif(\is_array($relations) || $relations instanceof \ArrayAccess)
					{
						foreach($relations as $relation)
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
	public function __destruct()
	{
		if($this->datasource instanceof \framework\orm\datasources\interfaces\IConnectionDatasource)
		{
			$this->datasource->close();
		}
	}

	
	
	public function maps()
	{
		$maps = array();
		
		foreach ($this->attachedModels as $model)
		{
			$maps[] = $this->_modelToMap($model);
		}
		
		return $maps;
	}
	
}