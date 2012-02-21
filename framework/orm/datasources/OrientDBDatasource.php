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

namespace framework\orm\datasources;

class OrientDBDatasourceException extends \framework\orm\datasources\exceptions\DatasourceException
{
	
}

/**
 * Library OrientDBDatasource
 *
 * @author mickael
 */

class OrientDBDatasource extends \framework\core\FrameworkObject implements \framework\orm\datasources\interfaces\IConnectionDatasource, \framework\orm\datasources\interfaces\IDbDatasource, \framework\orm\datasources\interfaces\IDatasource
{

	/**
	 * The connection to the database
	 * @var \OrientDB
	 */
	protected $link = NULL;

	/**
	 * The host
	 * @var string 
	 */
	protected $host;

	/**
	 * The connection's port
	 * @var int
	 */
	protected $port;

	/**
	 * The user name used for the connection to the host
	 * @var string 
	 */
	protected $user = '';

	/**
	 * The password used for the connection to the host
	 * @var string
	 */
	protected $password = '';

	/**
	 * The name of the active database
	 * @var string 
	 */
	protected $active = '';

	/**
	 * The configuration of the datasource. Named "configuration" to prevent confusing with $this->getConfig().
	 * @var array 
	 */
	protected $configuration = array();

	/**
	 * Regex pattern the IDs must match
	 * @var string
	 */
	protected $pattern = '/^[^ \t\n\r]*$/';
	
	/**
	 *
	 * @var \framework\orm\utils\DatasourceTools
	 */
	protected $tools = NULL;

	/**
	 * Constructor
	 * @throws \framework\orm\datasources\exceptions\ConnectionException
	 * @param string $host The host's address
	 * @param string|int $port The host's port
	 * @param string $user The username used to connect to the host
	 * @param string $password The password used to connect to the host
	 */
	public function __construct ($user, $password, $host = 'localhost', $port = 2424)
	{
		$this->tools = $this->getComponent('orm.utils.DatasourceTools');
		
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;

		try
		{
			$this->link = new \OrientDB($this->host, $this->port);
			$this->link->connect($this->user, $this->password);
		}
		catch (\Exception $e)
		{
			throw new \framework\orm\datasources\exceptions\ConnectionException($this->_getFullHost(),
                \framework\orm\datasources\exceptions\ConnectionException::HOST, $e);
		}
	}

	/**
	 * Establish a connection to a database
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
     * @throws \framework\orm\datasources\exceptions\ConnectionException
	 * @param string $database The databases's name
	 * @param string $user The user's name 
	 * @param string $password The user's password
	 */
	public function connect ($database, $user = '', $password = '')
	{
		if (!$this->link->isConnected())
		{
			$message = 'No connection established, unable to select a database.';
			throw new \framework\orm\datasources\OrientDBDatasourceException($message);
		}

		if ($this->link->DBExists($database))
		{
			$this->link->DBOpen($database, $user, $password);
			$this->active = $database;

			$this->configuration = $this->link->configList();
		}
		else
		{
			throw new \framework\orm\datasources\exceptions\ConnectionException($database, 
					\framework\orm\datasources\exceptions\ConnectionException::DATABASE);
		}
	}

	/**
	 * Close the connection to the active database, not to the server
	 */
	public function close ()
	{
		try
		{
			if ($this->link->isDBOpen())
			{
				$this->link->DBClose();
			}

			$this->active = '';
		}
		catch (\Exception $e)
		{
			
		}
	}

	/* DATABASE LEVEL */

	/**
	 * Check if a database exists
	 * @param string $database The database's name
	 * @return bool 
	 */
	public function databaseExists ($database)
	{
		return $this->link->DBExists($database);
	}

	/**
	 * Create a database
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
	 * @param string $name The database's name
	 * @param string $type The database's type
	 * @return bool 
	 */
	public function createDatabase ($name, $type)
	{
		if ($type != \OrientDB::DB_TYPE_LOCAL && $type != \OrientDB::DB_TYPE_MEMORY)
		{
			throw new \framework\orm\datasources\OrientDBDatasourceException('Bad database type ' . $type);
		}

		return $this->link->DBCreate($name, $type);
	}

	/**
	 * Delete a database
	 * @param string $database The database's name
	 * @return bool TRUE 
	 */
	public function deleteDatabase ($database)
	{
		if ($this->databaseExists($database))
		{
			if ($this->active == $database)
			{
				$this->close();
			}

			return $this->link->DBDelete($database);
		}

		return true;
	}

	/**
	 * Get the value of a configuration option.
	 * If no option is specified, the entire config is returned.
	 * @param string $key The option
	 * @return mixed An empty string if the $key doesn't exist
	 */
	public function getConfiguration ($key = '')
	{
		if ($key == '')
		{
			return $this->configuration;
		}

		return isset($this->configuration[$key]) ? $this->configuration[$key] : '';
	}

	/**
	 * Set a config options
	 * @param string $key the option's name
	 * @param string $value The new value
	 * @return bool
	 */
	public function setConfiguration ($key, $value)
	{
		if ($this->link->configSet($key, $value))
		{
			// update the cache
			$this->configuration[$key] = $value;
			return true;
		}

		return false;
	}

	/* CLUSTER LEVEL */

	/**
	 * Create a new cluster
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
	 * @param string $cluster The cluster's name
	 * @param string $type The cluster's type
	 * @return int The created cluster's id 
	 */
	public function createCluster ($cluster, $type = \OrientDB::DATACLUSTER_TYPE_PHYSICAL)
	{
		if ($this->link->isConnected())
		{
			if ($type != \OrientDB::DATACLUSTER_TYPE_LOGICAL && $type != \OrientDB::DATACLUSTER_TYPE_MEMORY
					&& $type != \OrientDB::DATACLUSTER_TYPE_PHYSICAL)
			{
				throw new \framework\orm\datasources\OrientDBDatasourceException('Bad cluster type ' . $type);
			}
			else
			{
				return $this->link->dataclusterAdd($cluster, $type);
			}
		}
		else
		{
			throw new \framework\orm\datasources\OrientDBDatasourceException('Not connected!');
		}
	}

	/**
	 * Delete a cluster
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
	 * @param int $clusterID The cluster's ID (and _not_ its name)
	 * @return bool 
	 */
	public function deleteCluster ($clusterID)
	{
		if ($this->link->isConnected())
		{
			return $this->link->dataclusterRemove($clusterID);
		}
		else
		{
			throw new \framework\orm\datasources\OrientDBDatasourceException('Not connected!');
		}
	}

	/**
	 * Create a class. By default, this method also creates a new cluster named after the class.
	 * @throws \framework\orm\datasources\exceptions\WrongEntityFormatException
	 * @param string $class The name of the new class
	 * @param string $parent The parent class from wich the new class inherits
	 * @param int|string $cluster The ID of the cluster the class will use or the name of a new cluster to create
	 * @param bool $createCluster Set to true if a new cluster must be created for the new class
	 * @param string $clusterType The type of the new cluster if one must be created. Will be ignored if $createCluster == false
	 * @return bool 
	 */
	public function createClass ($class, $parent = '', $cluster = NULL, $createCluster = false, $clusterType = \OrientDB::DATACLUSTER_TYPE_PHYSICAL)
	{
		if ($createCluster === true && is_string($cluster))
		{
			$cluster = $this->createCluster($cluster, $clusterType);
		}

		$clusterID = \filter_var($cluster, \FILTER_VALIDATE_INT);

		if (\preg_match($this->pattern, $class) !== false)
		{
			$query = 'CREATE CLASS ' . $class;

			if ($parent != '')
			{
				if (\preg_match($this->pattern, $parent) !== false)
				{
					$query .= ' EXTENDS ' . $parent;
				}
				else
				{
					throw new \framework\orm\datasources\exceptions\WrongEntityFormatException($parent);
				}
			}

			if (\is_int($clusterID))
			{
				$query .= ' CLUSTER ' . $clusterID;
			}

			return $this->link->query($query);
		}
		else
		{
			throw new \framework\orm\datasources\exceptions\WrongEntityFormatException($class);
		}
	}

	/* REQUEST LEVEL */

	/**
	 * Get the connection (a.k.a the OrientDB instance)
	 * @return \OrientDB
	 */
	public function getConnection ()
	{
		return $this->link;
	}

	/**
	 * Execute a request and return the result.
	 * @throws \framework\orm\datasources\exceptions\RequestException
	 * @param string $query 
	 * @return mixed
	 */
	public function exec ($query)
	{
		try
		{
			return $this->link->query($query);
		}
		catch (\Exception $e)
		{
			throw new \framework\orm\datasources\exceptions\RequestException($query, $e);
		}
	}

	/**
	 * Execute a query and return the results in an array.
	 * @param string $query
	 * @return \OrientDB
	 */
	public function query ($query)
	{
        try
        {
            return $this->link->select($query);
        }
        catch(\Exception $e)
        {
            throw new \framework\orm\datasources\exceptions\RequestException($query, $e);
        }
	}

	/**
	 * IDbDatasource
	 */

	/**
	 * Create a record
	 * @param string $entity The ID of the cluster where the new record ahs to be created
	 * @param array|\OrientDBRecord $data
	 * @param mixed $type The record type @see \OrientDBRecordTypes
     * @return int|bool
     */
	public function create ($entity, $data, $type = \OrientDB::RECORD_TYPE_DOCUMENT)
	{
		$fields = '';
		$toPersist = array();

		foreach ($data as $property => $spec)
		{
			if ($spec['storageField'] !== NULL)
			{
				$fields .= $spec['storageField'] . ', ';
				$toPersist[] = $property;
			}
		}

		if (!\preg_match('#^([a-zA-Z0-9_\-]+, )+$#', $fields))
		{
			throw new \framework\orm\datasources\OrientDBDatasourceException('Incorrect field names list: ' . $fields);
		}
		else
		{
			$values = '';

			foreach ($toPersist as $property)
			{
				$dataType = $data[$property]['type'];
				$dataValue = $data[$property]['value'];

				switch ($dataType)
				{
					case \framework\orm\types\OrientDBDateTime::TYPE_IDENTIFIER :
						$dataValue = $this->tools->quoteString($dataValue);
						break;

					case \framework\orm\types\OrientDBBoolean::TYPE_IDENTIFIER :
						break;
					
					case \framework\orm\types\Type::RELATION_KEY :
						$dataValue = $this->tools->quoteString($dataValue);
						break;
					
					case \framework\orm\types\Type::UNKNOWN :
						if (\is_string($dataValue))
						{
							$dataValue = $this->tools->quoteString($dataValue);
							break;
						}

						throw new \framework\orm\datasources\exceptions\WrongDataTypeException($dataType, $dataValue);
						break;

					default:
						if (\in_array($dataType, $this->getComponent('orm.numericTypes')))
						{
							break;
						}

						if (\in_array($dataType, $this->getComponent('orm.textualTypes')))
						{
							$dataValue = $this->tools->quoteString($dataValue);
							break;
						}

						if (\in_array($dataType, $this->getComponent('orm.booleanTypes')))
						{
							$dataValue = ($dataValue == true) ? 'true' : 'false';
							break;
						}
						
						if(\array_key_exists('internal', $data[$property]))
                        {
                            if($data[$property]['internal'] == true)
                            {
                                if($data[$property]['relation'] == \framework\orm\models\IAttachableModel::RELATION_HAS_ONE)
                                {
                                    //$dataValue = $this->tools->quoteString($dataValue['id']['value']);
                                    $dataValue = '#'.$dataValue['id']['value'];
                                    break;
                                }
                                elseif($data[$property]['relation'] ==
                                    \framework\orm\models\IAttachableModel::RELATION_HAS_MANY)
                                {
                                    $dataValue = '[';

                                    // build a string of IDs surrounded by square-barces [#3:21, #3:17, 3:56]
                                    foreach($data[$property]['value'] as $relation)
                                    {
                                        $dataValue .= '#'.$relation['id']['value'].', ';
                                    }

                                    $dataValue = \rtrim($dataValue, ', ').']';

                                    break;
                                }
                            }
                            else
                            {
                                $dataValue = '';
                            }

                            break;
                        }

						throw new \framework\orm\datasources\exceptions\WrongDataTypeException($dataType, $dataValue);
						break;
				}

				$values .= $dataValue . ', ';
			}

			// get rid of the extra ", " at the end of each string
			$values = \substr($values, 0, \strlen($values) - 2);
			$fields = \substr($fields, 0, \strlen($fields) - 2);

			$req = 'INSERT INTO ' . $entity . '(' . $fields . ')' . ' VALUES (' . $values . ')';

			$response = $this->link->query($req);

			if ($response instanceof \OrientDBRecord)
			{
				$response->parse();
				return $response->recordID;
			}

			return false;
		}
	}

	/**
	 *
	 * @param string $id
	 * @param string $entity
	 * @param \framework\orm\utils\Criteria $where
     * @return bool True on success, false otherwise
	 */
	public function delete ($id, $entity, \framework\orm\utils\Criteria $where = NULL)
	{
		return $this->link->recordDelete($id);
	}

	/**
	 * Find elements from their IDs.
	 * @param array $primary An array of IDs
	 * @param string|int The identifier of the entity where to look for (table name, cluster ID, ...)
	 * @return \framework\orm\utils\Collection The Collection of elements
	 */
	public function find (array $primary, $entity)
	{
		$found = $this->getComponent('orm.utils.Collection');
		$record = null;

		foreach ($primary as $id)
		{
			$record = $this->link->recordLoad($id);

			if ($record !== false)
			{
				$record->parse();
				$found[] = $this->_recordToMap($record, $id);
			}
		}

		return $found;
	}

	/**
	 * @param $entity
	 * @param \framework\orm\utils\Criteria $criteria
	 * @return \framework\orm\utils\Collection
	 */
	public function findAll ($entity, \framework\orm\utils\Criteria $criteria = NULL)
	{
		if (\strpos(' ', $entity) !== false)
		{
			throw new \framework\orm\datasources\exceptions\WrongEntityFormatException($entity);
		}

		$entities = $this->getComponent('orm.utils.Collection');

		try
		{
			$data = $this->query('SELECT FROM ' . $entity . ' WHERE ' . $this->criteriaToString($criteria));
			
			foreach ($data as $record)
			{
				$record->parse();
				$entities[] = $this->_recordToMap($record, $record->recordID);
			}
		}
		catch (\Exception $e)
		{
			throw new \framework\orm\datasources\OrientDBDatasourceException('Unable to find all entities '
					. $entity);
		}

		return $entities;
	}

    /**
     *
     * @param string $id
     * @param $entity
     * @param string|\OrientDBRecord $data
     * @param \framework\orm\utils\Criteria $where
     * @return bool Update status
     */
	public function update ($id, $entity, $data, \framework\orm\utils\Criteria $where = NULL)
	{
		// convert the $data to a record
		$record = $this->_mapToRecord($data);

		// return true on success
		return ($this->link->recordUpdate($id, $record) !== -1);
	}

	/**
	 *
	 * @return \framework\orm\utils\OrientDBCriteria 
	 */
	public function getNativeCriteria ()
	{
		return $this->getComponent('orm.utils.OrientDBCriteria');
	}

	
	protected function _recordToMap (\OrientDBRecord $record, $id)
	{
		// get the array representing the data (quite dirty ATM...)
		$map = (array) $record->data;
		$map = \array_splice($map, 0, 1);
		$map = \array_pop($map);

		foreach ($map as $index => $value)
		{
			$map[$index] = array();

			if ($value instanceof \OrientDBTypeLink)
			{
				$map[$index]['value'] = $value->clusterID . ':' . $value->recordPos;
			}
			elseif ($value instanceof \OrientDBTypeDate)
			{
				$map[$index]['value'] = \substr($value->getTime(), 0, 10);
			}
			elseif (\is_array($value))
			{
				$map[$index]['value'] = array();

				foreach ($value as $link)
				{
					if ($link instanceof \OrientDBTypeLink)
					{
						$map[$index]['value'][] = $link->clusterID . ':' . $link->recordPos;
					}
				}
			}
			else
			{
				$map[$index]['value'] = $value;
			}
		}

		$map['id']['value'] = $id;
		return $map;
	}

	protected function _mapToRecord ($map)
	{
		$record = new \OrientDBRecord();
		
		foreach ($map as $property => $spec)
		{
			if ($spec['storageField'] !== NULL)
			{
				if(\is_array($spec['value']) || $spec['value'] instanceof \Traversable)
				{
					$data = array();
					
					foreach($spec['value'] as $value)
					{
						$data[] = new \OrientDBTypeLink($value['id']['value']);
					}
					
					$record->data->$spec['storageField'] = $data;
				}
				else
				{
					$record->data->$spec['storageField'] = $spec['value'];
				}
			}
		}

		$record->parse();

		return $record;
	}	
	
	/**
	 * Get the full host, i.e. host:port
	 * @return string
	 */
	protected function _getFullHost ()
	{
		return $this->host . ':' . $this->port;
	}

	/**
	 * Get the string representation of a criteria.
	 * @param \framework\orm\utils\Criteria $criteria
	 * @return string 
	 */
	public function criteriaToString (\framework\orm\utils\Criteria $criteria)
	{
		$string = '';
		$constraints = $criteria->getConstraints();

		foreach ($constraints as $params)
		{
			switch ($params[0])
			{
				case \framework\orm\utils\Criteria::CRITERIA :
					if ($params[1][0] == \framework\orm\utils\Criteria::ASSOCIATION_AND)
					{
						$string .= ' AND ' . $this->criteriaToString($params[1][1]);
					}
					elseif ($params[1][0] == \framework\orm\utils\Criteria::ASSOCIATION_OR)
					{
						$string .= ' OR ' . $this->criteriaToString($params[1][1]);
					}
                    elseif ($params[1][0] == \framework\orm\utils\Criteria::ASSOCIATION_NOT)
                    {
                        $string .= ' NOT ' . $this->criteriaToString($params[1][1]);
                    }
					break;

				case \framework\orm\utils\Criteria::EQUALS :
					$string .= $params[1][0] . ' = ' . $this->tools->quoteParameter($params[1][1]);
					break;
				case \framework\orm\utils\Criteria::GREATER_THAN :
					$string .= $params[1][0] . ' > ' . $this->tools->quoteParameter($params[1][1]);
					break;
				case \framework\orm\utils\Criteria::LESS_THAN :
					$string .= $params[1][0] . ' < ' . $this->tools->quoteParameter($params[1][1]);
					break;
				case \framework\orm\utils\Criteria::GREATER_THAN_OR_EQUAL :
					$string .= $params[1][0] . ' >= ' . $this->tools->quoteParameter($params[1][1]);
					break;
				case \framework\orm\utils\Criteria::LESS_THAN_OR_EQUAL :
					$string .= $params[1][0] . ' <= ' . $this->tools->quoteParameter($params[1][1]);
					break;
				case \framework\orm\utils\Criteria::NOT_EQUALS :
					$string .= $params[1][0] . ' <> ' . $this->tools->quoteParameter($params[1][1]);
					break;

				case \framework\orm\utils\Criteria::IS_NULL :
					$string .= $params[1] . ' is null';
					break;
				case \framework\orm\utils\Criteria::LIKE :
					$string .= $params[1][0] . ' like ' . $this->tools->quoteParameter($params[1][1]);
					break;
				case \framework\orm\utils\Criteria::IN :
					$values = '[';

					foreach ($params[1][1] as $value)
					{
						$values .= $this->tools->quoteParameter($value) . ', ';
					}

					$values = \rtrim($values, ', ') . ']';
					$string .= $params[1][0] . ' in ' . $values;
					break;

				case \framework\orm\utils\Criteria::LIMIT :
					$string .= ' limit ' . $params[1][0] . ', ' . $params[1][1];
					break;

				case \framework\orm\utils\OrientDBCriteria::CONTAINS_TEXT :
					$string .= $params[1][0] . ' containsText ' . $this->tools->quoteString($params[1][1]);
					break;
				case \framework\orm\utils\OrientDBCriteria::MATCHES :
					$string .= $params[1][0] . ' matches ' . $this->tools->quoteString($params[1][1]);
					break;

				default:
					break;
			}
		}

		/*
		  if($criterias > 0 && (\substr($string, 0, 1) == '(' && \substr($string, \strlen($string)-1, 1) == ')'))
		  {
		  $string = \trim($string, '()');
		  }
		 * 
		 */

		return $string;
	}

	/**
	 * Close the connection on object destruction 
	 */
	public function __destruct ()
	{
		$this->close();
	}

}