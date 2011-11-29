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
 * Class NewMapper
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
	 * 							'type'			=> \framework\orm\types\Type::TYPE_TRANSPARENT // value doesn't need to be converted
	 * 						),
	 * 		'title'		=> array(
	 * 							'storageField'	=> 'post_title',
	 * 							'type'			=> \framework\orm\types\Type::TYPE_TRANSPARENT
	 * 						),
	 * 		'content'	=> array(
	 * 							'storageField'	=> 'post_content',
	 * 							'type'			=> \framework\orm\types\Type::TYPE_TRANSPARENT
	 * 						),
	 * 		'user'		=> array(
	 * 							'storageField'	=> 'post_user_id',
	 * 							'type'			=> 'model.user',
	 * 							'relation'		=> 'hasOne',
	 * 							'relationField'	=> 'user_id'
	 * 						),
	 * 		'category'	=> array(
	 * 							'storageField'	=> 'post_category_id',
	 * 							'type'			=> 'model.category',
	 * 							'relation'		=> 'hasOne',
	 * 							'relationField'	=> 'category_id'
	 * 						),
	 * 		'comments'	=> array(
	 * 							'storageField'	=> null,
	 * 							'type'			=> 'model.comment',
	 * 							'relation'		=> 'hasMany',
	 * 							'relationField'	=> 'post_id'
	 * 						)
	 * );</pre>
	 * 
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Constructor
	 * @param \framework\orm\datasources\IDatasource $datasource The datasource used to store the data.
	 * @throws \framework\orm\mappers\MapperException 
	 */
	public function __construct (\framework\orm\datasources\interfaces\IDatasource $datasource)
	{
		$this->datasource = $datasource;

		if (!isset($this->modelName) || $this->modelName === '')
		{
			throw new \framework\orm\mappers\MapperException('No model name specified.');
		}

		if (!isset($this->fields) || $this->fields === array() || $this->fields === NULL)
		{
			throw new \framework\orm\mappers\MapperException('No fields specified.');
		}
		
		
		$this->fields = new \framework\orm\utils\Map($this->fields);
		$this->fields->removeProperty('value');
		/*

		foreach ($this->fields as $name => $spec)
		{
			if (!\array_key_exists('type', $spec))
			{
				$this->fields[$name]['type'] = \framework\orm\types\Type::UNKNOWN;
			}
		}
		*/
		
		$this->init();
	}

	/**
	 * Attach a new object to the mapper.
	 * @param \framework\orm\models\IAttachableModel $object 
	 * @return string The key where the object is stored in the mapper.
	 */
	public function attach (\framework\orm\models\IAttachableModel $model)
	{
		$this->attachedModels[$model->getId()] = $model;

		return $model->getId();
	}

	/**
	 * Dettach an object from the mapper.
	 * @param mixed $model The model to detach or its id
	 * @return bool
	 */
	public function detach ($model)
	{
		if (\is_object($model))
		{
			if ($model instanceof \framework\orm\models\IAttachableModel)
			{
				if ($this->isAttached($model))
				{
					unset($this->attachedModels[$model->getId()]);
					return true;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			if (\array_key_exists($model, $this->attachedModels))
			{
				unset($this->attachedModels[$model]);
				return true;
			}
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
		$data = $this->datasource->find($id, $this->getEntityIdentifier());

		if ($data == array())
		{
			return null;
		}
		else
		{
			if (\count($data) == 1)
			{
				return $this->mapToModel($data[0]);
			}
			else
			{
				$results = array();
				foreach ($data as $map)
				{
					$newModel = $this->mapToModel($map);

					if ($attach)
					{
						$this->attach($newModel);
					}

					$results[] = $newModel;
				}

				return $results;
			}
		}
	}

	/**
	 * Retrieve several models from the datasource.
	 * @param \framework\orm\Criteria A set of constraints the results must match. 
	 * @return array
	 */
	public function findAll (\framework\orm\Criteria $criteria = null)
	{
		$data = $this->datasource->findAll($criteria);

		if ($data == array())
		{
			return $data;
		}
		else
		{
			$results = array();

			foreach ($data as $map)
			{
				$results[] = $this->mapToModel($map);
			}

			return $results;
		}
	}

	/**
	 * Save a model in the datasource. 
	 * @param \framework\orm\models\IAttachableModel The model to save.
	 */
	public function save (\framework\orm\models\IAttachableModel $model)
	{
		$response = NULL;

		if ($model->getId() === NULL)
		{
			$response = $this->datasource->create($this->getEntityIdentifier(), $this->modelToMap($model));

			if ($response !== false)
			{
				return $this->find($response);
			}

			throw new \framework\orm\mappers\MapperException('Unable to save new model (' . $this->getModelName() . ')');
		}
		else
		{
			$response = $this->datasource->update($model->getId(), $this->getEntityIdentifier(), $this->modelToMap($model));

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
	 * Delete a model from the datasource.
	 * @param \framework\orm\Criteria|\framework\orm\models\IAttachableModel 
	 */
	public function delete ($criteria)
	{
		if($criteria instanceof \framework\orm\Criteria)
		{
			return $this->_deleteCriteria($criteria);
		}
		else
		{
			if($criteria instanceof \framework\orm\models\IAttachableModel)
			{
				$criteria = $criteria->getId();
			}
			
			return $this->_deleteModel($criteria);
		}
	}

	
	protected function _deleteModel($model)
	{
		return $this->datasource->delete($model, NULL);
	}
	
	protected function _deleteCriteria($criteria)
	{
		return $this->datasource->delete(NULL, $criteria);
	}
	
	
	/**
	 * Transform a datasource map to a PHP-friendly model
	 * @param array|\ArrayObject $map 
	 * @return \framework\orm\models\IAttachableModel
	 */
	protected function mapToModel ($map)
	{
		$map = new \framework\orm\utils\Map($map);
		
		// get a new model's instance
		$model = $this->getComponent($this->getModelName());
		$setter = '';

		// set all of its properties
		foreach ($this->fields as $name => $spec)
		{
			$setter = 'set' . \ucfirst($name);

			if (\method_exists($model, $setter))
			{
				// if a particular type if specified, convert it to a PHP format
				if ($spec['type'] !== \framework\orm\types\Type::UNKNOWN)
				{
					$model->$setter($this->getComponent($spec['type'])->convertToPHP($map[$name]['value']));
				}
				// else, use the value as provided
				else
				{
					$model->$setter($map[$name]['value']);
				}
			}
			else
			{
				throw new \framework\orm\mappers\MapperException('Missing method ' . $setter . ' in model ' . \get_class($model));
			}
		}

		return $model;
	}

	/**
	 * Transform a PHP model to map of datasource-friendly values.
	 * @param \framework\orm\models\IAttachableModel $model 
	 * @return \framework\orm\utils\Map
	 */
	protected function modelToMap (\framework\orm\models\IAttachableModel $model)
	{
		$map = new \framework\orm\utils\Map();
		$getter = '';

		foreach ($this->fields as $name => $spec)
		{
			$getter = 'get' . \ucfirst($name);

			// check if a getter is exists for the property
			if (\method_exists($model, $getter))
			{
				// if a particular type is specified, convert the value to a datasource-friendly format
				if ($spec['type'] !== \framework\orm\types\Type::UNKNOWN)
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

		return $map;
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
		if (\array_key_exists($id, $this->attachedModels))
		{
			return $this->attachedModels[$id];
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

	/**
	 * Get the identifier of the entity where the models are stored in the datasource.
	 * (i.e. table name, cluster ID, collection name, ect...)
	 * @return string|int
	 */
	public abstract function getEntityIdentifier ();

	/**
	 * Get the key used to retrieve the model from the components container.
	 * @return string
	 */
	public abstract function getModelName ();
}