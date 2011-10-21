<?php
/** 
 * @author Noah Goodrich
 * @date April 13, 2010
 *
*/

namespace Gacela\Mapper;

abstract class Mapper extends \framework\core\FrameworkObject implements iMapper {

	protected static $_deletedField = 'isDeleted';

	/**
	 * 
	 * @brief Contains the names of resources that are associations to Mapper::$_resource
	 * <a href="http://martinfowler.com/eaaCatalog/associationTableMapping.html">Association Table Mapping</a>
	 */
	protected $_associations = array();

	/**
	 * @brief Contains the names of resources that are dependent on Mapper::$_resource
	 * <a href="http://martinfowler.com/eaaCatalog/dependentMapping.html">Dependent Mapping</a>
	 */
	protected $_dependents = array();

	/**
	 * @brief Contains the names of resources that Mapper::$_resource inherits from based on Mapper::$_foreignKeys and shared
	 * primary keys
	 * <a href="http://martinfowler.com/eaaCatalog/concreteTableInheritance.html">Concrete Table Inheritance</a>
	 */
	protected $_inherits = array();

	/**
	 * @brief Registry of Model objects already loaded from the DataSource.
	 */
	protected $_models = array();

	/**
	 * @brief Model class name to create in _load()
	 */
	protected $_modelName = null;

	/**
	 * @brief Contains the primary key fields for the mapper.
	 * By default the primary key loads from Resource::getPrimaryKey()
	 * 
	 */
	protected $_primaryKey = array();

	/**
	 * @brief Contains the meta information necessary to load hasMany, belongsTo related data
	 * Also used by Mapper::$_associations to load related data and by Mapper::$_inherits to determine whether
	 * Concrete Table Inheritance is applicable.
	 */
	protected $_foreignKeys = array();

	/**
	 * @brief 
	 */
	protected $_resource = null;

	protected $_resourceName = null;

	/**
	 * @brief Instance of DataSource to use for the Mapper.
	 */
	protected $_source = 'db';

	protected function _dataToSave(\Gacela\DataSource\Resource $resource, $changed, $new)
	{
		$fields = $resource->getFields();

		$data = array_intersect_key((array) $new, $fields, array_flip($changed));

		foreach($data as $key => $val) {
			$data[$key] = $fields[$key]->transform($val);
		}

		return $data;
	}

	protected function _deleteResource($resource, $data)
	{
		$where = new \Gacela\Criteria();

		$primary = $this->_primaryKey($resource->getPrimaryKey(), $data);

		if(is_null($primary)) {
			return false;
		}

		foreach($primary as $key => $value) {
			$where->equals($key, $value);
		}

		$this->_source()->delete($resource->getName(), $where);

		return true;
	}

	protected function _doUpdate($resource, $data)
	{
		$primary = $this->_primaryKey($resource->getPrimaryKey(), (object) $data);
		$fields = $resource->getFields();
		
		if(is_null($primary)) {
			return false;
		} elseif($fields[key($primary)]->sequenced === false) {
			$rs = $this->_source()->find($primary, $resource);

			if(count($rs)) {
				return true;
			} else {
				return false;
			}
		}
		
		return true;
	}

	protected function _findAssociation($name, $data)
	{
		$data = $this->_primaryKey($this->_primaryKey, $data);

		if(!is_array($data)) {
			$refs = array_values($this->_associations[$name]['meta']->keys);

			foreach($refs as $key) {
				$data[$key] = null;
			}
		} else {
			foreach($this->_associations[$name]['meta']->keys as $key => $ref) {
				if($key != $ref) {
					$data[$ref] = $data[$key];
					unset($data[$key]);
				}
			}
		}

		return $this->getComponent('gacela')
					->loadMapper(\Gacela\Inflector::singularize($name))
					->findAllByAssociation($this->_resource->getName(), $data);

	}

	/**
	 * @return Mapper
	 */
	protected function _init()
	{
		// Everything loads in order based on what resources are needed first.
		$this->_initResource()
			->_initPrimaryKey()
			->_initForeignKeys($this->_resource->getRelations())
			->_initInherits()
			->_initAssociations()
			->_initDependents()
			->_initModelName();

		return $this;
	}

	protected function _initAssociations()
	{
		foreach($this->_foreignKeys as $name => $foreign) {
			$resource = $foreign['resource'];

			if(count($resource->getFields()) == count($resource->getPrimaryKey())) {
				$primary = $resource->getPrimaryKey();
				
				foreach($resource->getRelations() as $relation) {
					$keys = array_keys($relation->keys);

					foreach($keys as $key) {
						$i = array_search($key, $primary);

						if($i !== false) {
							unset($primary[$i]);
						}
					}
				}

				if(!count($primary)) {
					$this->_associations[$name] = $foreign;

					unset($this->_foreignKeys[$name]);
				}
			}
		}
		
		return $this;
	}

	protected function _initDependents()
	{
		// Run so that dependent relationships can't be accidentally loaded independently
		$_dependents = $this->_dependents;
		$this->_dependents = array();

		$fields = $this->getFields();

		foreach($_dependents as $name) {
			$dependent = $this->_foreignKeys[$name];

			unset($this->_foreignKeys[$name]);
			
			/**
			 * If the keyColumn of the primary resource is nullable, then all fields in the dependent relationship need to
			 * appear nullable.
			 */
			$nullable = false;
			foreach(array_keys($dependent['meta']->keys) as $key) {
				if($fields[$key]->null) {
					$nullable = true;
					break;
				}
			}

			if($nullable) {
				foreach($dependent['resource']->getFields() as $key => $val) {
					if(in_array($key, $this->_primaryKey)) {
						continue;
					}

					$val->override('null', true);
				}
			}

			$this->_dependents[$name] = $dependent;
		}
		
		return $this;
	}

	protected function _initForeignKeys($relations)
	{
		$plural = \Gacela\Inflector::pluralize($this->_resourceName);
		$single = \Gacela\Inflector::singularize($this->_resourceName);

		if(in_array($plural, array_keys($relations))) {
			unset($relations[$plural]);
		} elseif(in_array($single, array_keys($relations))) {
			unset($relations[$single]);
		}

		$this->_foreignKeys = array_merge($relations, $this->_foreignKeys);

		foreach($this->_foreignKeys as $relation => $meta) {
			// This foreign key is already initialized
			if(is_array($meta) && isset($meta['resource'])) {
				continue;
			}
			
			$meta = (object) $meta;

			$resource = $this->_source()->loadResource($meta->refTable);

			$this->_foreignKeys[$relation] = array('meta' => $meta, 'resource' => $resource);
		}

		return $this;
	}

	protected function _initInherits()
	{
		if(empty($this->_inherits)) {
			foreach($this->_foreignKeys as $name => $stuff) {
				if($stuff['meta']->type == 'hasMany') {
					continue;
				}

				if($this->_resource->getPrimaryKey() === $stuff['resource']->getPrimaryKey() && array_keys($stuff['meta']->keys) === $stuff['resource']->getPrimaryKey()) {
					$this->_inherits[$name] = $stuff;
					
					$relations = $stuff['resource']->getRelations();

					unset($relations[\Gacela\Inflector::singularize($this->_resourceName)]);
					unset($this->_foreignKeys[$name]);
					
					$this->_initForeignKeys($stuff['resource']->getRelations());
				}
			}
		} else {
			throw new \Exception('Not Implemented Yet');
		}
		
		return $this;
	}

	/**
	 * @return Mapper
	 */
	protected function _initModelName()
	{
		$classes = explode('\\', get_class($this));

		$pos = array_search('Mapper', $classes);

		$classes[$pos] = 'Model';

		$this->_modelName = "\\".join("\\", $classes);

		return $this;
	}

	protected function _initPrimaryKey()
	{
		if(empty($this->_primaryKey)) {
			$this->_primaryKey = $this->_resource->getPrimaryKey();
		}

		return $this;
	}

	/**
	 * @return Mapper
	 */
	protected function _initResource()
	{
		if(is_null($this->_resourceName)) {
			$class = explode('\\', get_class($this));
			$class = end($class);
			$class[0] = strtolower($class[0]);
			
			$this->_resourceName = \Gacela\Inflector::pluralize($class);
		}

		$this->_resource = $this->_source()->loadResource($this->_resourceName);
				
		return $this;
	}

	protected function _singleton()
	{
		return $this->getComponent('gacela');
	}

	/**
	 * @param \stdClass $data
	 * @return Model
	 */
	protected function _load(\stdClass $data)
	{
		return new $this->_modelName($data);
	}

	/**
	 * @param  $data
	 * @return null|string
	 */
	protected function _primaryKey($primaryKey, \stdClass $data)
	{
		$primary = array();
		foreach($primaryKey as $k) {
			if(!isset($data->$k) || is_null($data->$k)) {
				continue;
			}
			
			$primary[$k] = $data->$k;
		}
		
		if(!count($primary) || count($primary) != count($primaryKey)) {
			$primary = null;
		}
		
		return $primary;
	}

	protected function _saveResource($resource, $changed, $new, $old)
	{
		$data = $this->_dataToSave($resource, $changed, $new);
		
		if(empty($data)) {
			return false;
		}

		$test = array_merge((array) $new, $old);
		
		if($this->_doUpdate($resource, $test) === false) {
			$rs = $this->_source()->insert($resource->getName(), $data);
			
			$fields = $resource->getFields();
			
			if(count($resource->getPrimaryKey()) == 1 && $fields[current($resource->getPrimaryKey())]->sequenced === true) {
				$new->{current($resource->getPrimaryKey())} = $rs;
				$changed[] = current($resource->getPrimaryKey());
			}
		} else {
			$primary = $this->_primaryKey($resource->getPrimaryKey(), (object) $test);
			
			if(is_null($primary)) {
				throw new \Exception('Oops! primary key is null');
			}

			$where = new \Gacela\Criteria;

			foreach($primary as $k => $v) {
				$where->equals($k, $v);
			}

			$this->_source()->update($resource->getName(), $data, $where);
		}
		
		return array($changed, $new);
	}

	protected function _source()
	{
		return $this->getComponent('gacela')->getDataSource($this->_source);
	}

	public function __construct()
	{
		$this->init();
	}

	/**
	 * @brief - Not Yet Implemented
	 * @param $association
	 * @param $data
	 * @param bool $delete
	 * @return bool
	 */
	public function addAssociation($association, $data, $delete = false)
	{
		if($association instanceof \Gacela\Collection) {
			$model = $association->current();
		} else {
			$model = $association;
			$association = array($model);
		}
		
		$name = explode('\\', get_class($model));
		$name = end($name);
		$name = \Gacela\Inflector::pluralize($name);
		$name[0] = strtolower($name[0]);

		if(!isset($this->_associations[$name])) {
			return false;
		}

		$assoc = $this->_associations[$name];

		if($delete) {
			$criteria = new \Gacela\Criteria();

			foreach($assoc['meta']->keys as $key => $ref) {
				$criteria->equals($ref, $data->$key);
			}

			$this->_source()->delete($assoc['meta']->refTable, $criteria);
		}

		$toInsert = array();

		$me = array();

		foreach($assoc['meta']->keys as $key => $ref) {
			$me[$ref] = $data->$key;
		}

		foreach($association as $model) {
			$array = $me;

			foreach($assoc['resource']->getRelations() as $relation) {
				foreach($relation->keys as $key => $ref) {
					if(array_search($key, $assoc['meta']->keys) === false) {
						$array[$key] = $model->$ref;
					}
				}
			}
			
			$toInsert[] = $array;
		}

		return $this->_source()->insert($assoc['meta']->refTable, $toInsert);
	}

	public function debug($return = true)
	{
		$array = array(
			'associations' => array_keys($this->_associations),
			'dependents' => array_keys($this->_dependents),
			'inherits' => array_keys($this->_inherits),
			'other' => array_keys($this->_foreignKeys),
			'fields' => array_keys($this->getFields()),
			'lastDataSourceQuery' => $this->_source()->lastQuery(),
		);

		if($return) {
			return $array;
		} else {
			echo '<pre>'.print_r($array, true).'</pre>';
			return;
		}
	}

	/**
	 * @brief Called by the Model to delete the record represented by the identity field
	 * @param stdClass - The data from the Model
	 * @return true on success, false on failure
	 */
	public function delete(\stdClass $data)
	{
		$this->_source()->beginTransaction();
		
		if(!$this->_deleteResource($this->_resource, $data)) {
			$this->_source()->rollbackTransaction();
			return false;
		}

		foreach($this->_inherits as $inherits) {
			if(!$this->_deleteResource($inherits['resource'], $data)) {
				$this->_source()->rollbackTransaction();
				return false;
			}
		}

		foreach($this->_dependents as $dep) {
			if(!$this->_deleteResource($dep['resource'], $data)) {
				$this->_source()->rollbackTransaction();
				return false;
			}
		}

		$this->_source()->commitTransaction();
		
		return true;
	}

	/**
	 * @brief Returns a single instance of Mapper::$_modelName based on the identity field
	 * @param  $id integer|array
	 * @return Model
	 *
	 */
	public function find($id)
	{
		if(!is_object($id)) {
			if(is_scalar($id)) {
				$id = array(current($this->_primaryKey) => $id);
			}

			$id = (object) $id;
		}

		$primary = $this->_primaryKey($this->_primaryKey, $id);
		
		if(!is_null($primary)) {
			$data = current($this->_source()->find($primary, $this->_resource, $this->_inherits, $this->_dependents));
		}
		
		if(!isset($data) || empty($data)) {
			$data = new \stdClass();
		}
		
		return $this->_load($data);
	}

	/**
	 * @brief Returns a Collection of Model objects based on the Criteria specified
	 * @param \Gacela\Criteria|null $criteria
	 * @return \Gacela\Collection
	 */
	public function findAll(\Gacela\Criteria $criteria = null)
	{
		return new \Gacela\Collection(
						$this,
						$this->_source()->findAll($criteria, $this->_resource, $this->_inherits, $this->_dependents)
					);
	}

	/**
	 * @param  $relation
	 * @param array $data
	 * @return \Gacela\Collection
	 */
	public function findAllByAssociation($relation, array $data)
	{
		return new	 \Gacela\Collection(
						$this,
						$this->_source()->findAllByAssociation(
							$this->_resource,
							$this->_associations[$relation],
							$data,
							$this->_inherits,
							$this->_dependents
						)
					);
	}

	/**
	 * @brief Requests a related Model or Collection and returns it to the requesting Model.
	 * Uses Mapper::$_associations, Mapper::$_foreignKeys
	 *
	 * @param  $name - The name of the Model or Collection to return
	 * @param  $data - The data from the Model
	 * @return Model | Collection
	 */
	public function findRelation($name, $data)
	{
		if(isset($this->_associations[$name])) {
			return $this->_findAssociation($name, $data);
		} else {
			$relation = $this->_foreignKeys[$name];
			
			if($relation['meta']->type == 'hasMany') {
				$name = \Gacela\Inflector::singularize($name);
			}
			
			$criteria = new \Gacela\Criteria();

			foreach($relation['meta']->keys as $key => $ref) {
				if(empty($data->$key)) {
					$criteria = new \Gacela\Criteria;
					break;
				}

				$criteria->equals($relation['meta']->refTable.'.'.$ref, $data->{$key});
			}
			
			$result = $this->getComponent('gacela')->loadMapper($name)->findAll($criteria);

			if ($relation['meta']->type == 'belongsTo') {
				return $result->current();
			} elseif ($relation['meta']->type == 'hasMany') {
				return $result;
			}
		}
	}

	/**
	 * @brief Used by Model to get all of the fields available from mapper.
	 * @return A merged array of all fields from $_resource, $_inherits, $_dependents
	 */
	public function getFields()
	{
		$array = $this->_resource->getFields();

		foreach($this->_inherits as $stuff) {
			$array = array_merge($array, $stuff['resource']->getFields());
		}
		
		foreach($this->_dependents as $dependent) {
			$array = array_merge($dependent['resource']->getFields(), $array);
		}
		
		return $array;
	}

	/**
	 * @brief Provides the Model with the names of related Models
	 * @return An array of all the relation names whether as $_associations or $_foreignKeys meaning belongsTo or hasMany
	 */
	public function getRelations()
	{
		$relations = array();
		foreach($this->_foreignKeys as $key => $array) {
			$relations[$key] = $array['meta']->keys;
		}

		foreach($this->_associations as $key => $array) {
			$relations[$key] = $array['meta']->keys;
		}

		return $relations;
	}

	public function init()
	{
		$this->_init();
	}

	/**
	 * @brief Loads a new instance of $_modelName from the $data provided.
	 * @param \stdClass $data
	 * @return Model
	 */
	public function load(\stdClass $data)
	{
		return $this->_load($data);
	}

	/**
	 * @brief - Not Yet Implemented
	 * @param $association
	 * @param $data
	 * @return bool
	 */
	public function removeAssociation($association, $data)
	{
		if($association instanceof \Gacela\Collection) {
			$model = $association->current();
		} else {
			$model = $association;
			$association = array($model);
		}

		$name = explode('\\', get_class($model));
		$name = end($name);
		$name = \Gacela\Inflector::pluralize($name);
		$name[0] = strtolower($name[0]);
		
		if(!isset($this->_associations[$name])) {
			return false;
		}

		$assoc = $this->_associations[$name];
		
		$main = new \Gacela\Criteria;
		
		$me = new \Gacela\Criteria;

		foreach($assoc['meta']->keys as $key => $ref) {
			$me->equals($ref, $data->$key);
		}

		foreach($association as $model) {
			$sub = $me;

			foreach($assoc['resource']->getRelations() as $relation) {
				foreach($relation->keys as $key => $ref) {
					if(array_search($key, $assoc['meta']->keys) === false) {
						$sub->equals($key, $model->$ref);
					}
				}
			}

			$main->criteria($sub, true);
		}

		$this->_source()->delete($assoc['meta']->refTable, $main);
	}

	/**
	 * @brief Save is called by Model, the Mapper is responsible for knowing whether to call insert() or update() on the DataSource for $_resource, $_inherits, and $_dependents.
	 * @param array $changed - An array of the changed fields
	 * @param \stdClass $new - The data from the Model
	 * @param array $old - The original data from the Model
	 * @return bool|\stdClass - FALSE on failure, the modified $data on success.
	 */
	public function save(array $changed, \stdClass $new, array $old)
	{
		$this->_source()->beginTransaction();
		
		foreach($this->_dependents as $dependent) {

			// This all looks like a nasty hack. Here's to hoping to some more brilliant minds to provide input. -- ndg
			$data = array('old' => array(), 'new' => array(), 'changed' => array());

			// Setup for differing key names
			foreach($dependent['meta']->keys as $key => $ref) {
				if(!empty($old[$key])) {
					$data['old'][$ref] = $old[$key];
				}

				if(isset($new->$key) && (!isset($data['old'][$ref]) || $data['old'][$ref] != $new->$key)) {
					$data['changed'][] = $ref;
					$data['new'][$ref] = $new->$key;
				}
			}

			foreach($dependent['resource']->getFields() as $name => $field) {
				if(in_array($name, $changed)) {
					$data['new'][$name] = $new->$name;
					$data['changed'][] = $name;
				}
			}

			$rs = $this->_saveResource($dependent['resource'], $data['changed'], (object) $data['new'], $data['old']);

			if (is_array($rs)) {
				foreach($dependent['meta']->keys as $key => $ref) {
					$changed[] = $key;
					$new->$key = $rs[1]->$ref;
				}
			}
		}

		foreach($this->_inherits as $parent) {
			$rs = $this->_saveResource($parent['resource'], $changed, $new, $old);

			if(is_array($rs)) {
				list($changed, $new) = $rs;
			}
		}
		
		$rs = $this->_saveResource($this->_resource, $changed, $new, $old);
		
		if($rs === false) {
			$this->_source()->rollbackTransaction();
			return false;
		}
		
		list($changed, $new) = $rs;

		$this->_source()->commitTransaction();
		
		return (object) $new;
	}
}
