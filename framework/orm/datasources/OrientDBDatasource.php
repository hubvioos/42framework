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
/**
 * Library OrientDBDatasource
 *
 * @author mickael
 */

namespace framework\orm\datasources;

class OrientDBDatasourceException extends \Exception
{
	
}

class OrientDBDatasource implements \framework\orm\datasources\interfaces\IConnectionDatasource, \framework\orm\datasources\interfaces\IDbDatasource, \framework\orm\datasources\interfaces\IDatasource
{

	/**
	 * The connection to the database
	 * @var OrientDB 
	 */
	private $_link = null;

	/**
	 * The host
	 * @var string 
	 */
	private $_host = 'localhost';

	/**
	 * The connection's port
	 * @var int
	 */
	private $_port = 2424;

	/**
	 * The user name used for the connection to the host
	 * @var string 
	 */
	private $_user = '';

	/**
	 * The password used for the connection to the host
	 * @var string
	 */
	private $_password = '';

	/**
	 * The name of the active database
	 * @var string 
	 */
	private $_active = '';

	/**
	 * The configuration
	 * @var array 
	 */
	private $_config = array();

	/**
	 * Regex pattern the IDs must match
	 * @var string
	 */
	private $_pattern = '/^[^ \t\n\r]*$/';

	/**
	 * Constructor
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
	 * @param string $host The host's address
	 * @param string|int $port The host's port
	 * @param string $user The username used to connect to the host
	 * @param string $password The password used to connect to the host
	 */
	public function __construct ($host, $port, $user = '', $password= '')
	{
		$this->_host = $host;
		$this->_port = $port;
		$this->_user = $user;
		$this->_password = $password;

		try
		{
			$this->_link = new \OrientDB($this->_host, $this->_port);
			$this->_link->connect($this->_user, $this->_password);
		}
		catch (\Exception $e)
		{
			$message = 'Unable to connect to ' . $this->_getFullHost()
					. ' with user ' . $this->_user . ' and the password you specified';

			throw new \framework\orm\datasources\OrientDBDatasourceException($message, null, $e);
		}
	}

	/**
	 * Establish a connection to a database
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
	 * @param string $database The databases's name
	 * @param string $user The user's name 
	 * @param string $password The user's password
	 */
	public function connect ($database, $user = '', $password = '')
	{
		if (!$this->_link->isConnected())
		{
			$message = 'No connection established, unable to select a database.';
			throw new \framework\orm\datasources\OrientDBDatasourceException($message);
		}

		if ($this->_link->DBExists($database))
		{
			$this->_link->DBOpen($database, $user, $password);
			$this->_active = $database;

			$this->_config = $this->_link->configList();
		}
		else
		{
			$message = 'Database ' . $database . ' doesn\'t exist on host ' . $this->_getFullHost();
			throw new \framework\orm\datasources\OrientDBDatasourceException($message);
		}
	}

	/**
	 * Close the connection to the active database, not to the server
	 */
	public function close ()
	{
		try
		{
			$this->_link->DBClose();
			$this->_active = '';
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
		return $this->_link->DBExists($database);
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

		return $this->_link->DBCreate($name, $type);
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
			if ($this->_active == $database)
			{
				$this->close();
			}

			return $this->_link->DBDelete($database);
		}

		return true;
	}

	/**
	 * Get the value of a configuration option.
	 * If no option is specified, the entire config is returned.
	 * @param string $key The option
	 * @return mixed An empty string if the $key doesn't exist
	 */
	public function getConfig ($key = '')
	{
		if ($key == '')
		{
			return $this->_config;
		}

		return isset($this->_config[$key]) ? $this->_config[$key] : '';
	}

	/**
	 * Set a config options
	 * @param string $key the option's name
	 * @param string $value The new value
	 * @return bool
	 */
	public function setConfig ($key, $value)
	{
		if ($this->_link->configSet($key, $value))
		{
			// update the cache
			$this->_config[$key] = $value;
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
		if ($this->_link->isConnected())
		{
			if ($type != \OrientDB::DATACLUSTER_TYPE_LOGICAL && $type != \OrientDB::DATACLUSTER_TYPE_MEMORY
					&& $type != \OrientDB::DATACLUSTER_TYPE_PHYSICAL)
			{
				throw new \framework\orm\datasources\OrientDBDatasourceException('Bad cluster type ' . $type);
			}
			else
			{
				return $this->_link->dataclusterAdd($cluster, $type);
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
		if ($this->_link->isConnected())
		{
			return $this->_link->dataclusterRemove($clusterID);
		}
		else
		{
			throw new \framework\orm\datasources\OrientDBDatasourceException('Not connected!');
		}
	}

	/**
	 * Create a class. By default, this method also creates a new cluster named after the class.
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
	 * @param string $class The name of the new class
	 * @param string $parent The parent class from wich the new class inherits
	 * @param int|string $cluster The ID of the cluster the class will use or the name of a new cluster to create
	 * @param bool $createCluster Set to true if a new cluster must be created for the new class
	 * @param string $clusterType The type of the new cluster if one must be created. Will be ignored if $createCluster == false
	 * @return bool 
	 */
	public function createClass ($class, $parent = '', $cluster = null, $createCluster = false, $clusterType = \OrientDB::DATACLUSTER_TYPE_PHYSICAL)
	{
		if ($createCluster === true && is_string($cluster))
		{
			$cluster = $this->createCluster($cluster, $clusterType);
		}

		$clusterID = \filter_var($cluster, \FILTER_VALIDATE_INT);

		if (\preg_match($this->_pattern, $class) !== false)
		{
			$query = 'CREATE CLASS ' . $class;

			if ($parent != '')
			{
				if (\preg_match($this->_pattern, $parent) !== false)
				{
					$query .= ' EXTENDS ' . $parent;
				}
				else
				{
					throw new \framework\orm\datasources\OrientDBDatasourceException('Invalid parent class name '
							. $parent);
				}
			}

			if (\is_int($clusterID))
			{
				$query .= ' CLUSTER ' . $clusterID;
			}

			return $this->_link->query($query);
		}
		else
		{
			throw new \framework\orm\datasources\OrientDBDatasourceException('Invalid class name ' . $class);
		}
	}

	/* REQUEST LEVEL */

	/**
	 * Get the connection (a.k.a the OrientDB instance)
	 * @return OrientDB 
	 */
	public function getConnection ()
	{
		return $this->_link;
	}

	/**
	 * Execute a request and return the result.
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
	 * @param string $query 
	 * @return mixed
	 */
	public function exec ($query)
	{
		try
		{
			return $this->_link->query($query);
		}
		catch (\Exception $e)
		{
			throw new \framework\orm\datasources\OrientDBDatasourceException(
					'Unable to perform request ' . $query, null, $e);
		}
	}

	/**
	 * Execute a query and return the results in an array.
	 * @param string $query
	 * @return mixed
	 */
	public function query ($query)
	{
		return $this->_link->select($query);
	}

	/**
	 * IDbDatasource
	 */

	/**
	 * Create a record
	 * @param string $entity The ID of the cluster where the new record ahs to be created
	 * @param array|OrientDBRecord $data 
	 * @param mixed $type The record type @see OrientDBRecordTypes
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
						$dataValue = $this->_quote($dataValue);
						break;
					
					case \framework\orm\types\OrientDBBoolean::TYPE_IDENTIFIER :
						break;
					
					case \framework\orm\types\Type::UNKNOWN :
						if (\is_string($dataValue))
						{
							$dataValue = $this->_quote($dataValue);
							break;
						}
						
						throw new \framework\orm\datasources\OrientDBDatasourceException('Bad type for value "' . $dataValue.'"');
						break;
						
					default:
						if(\in_array($dataType, \framework\orm\types\Type::NUMERIC_TYPES))
						{
							break;
						}
						
						if(\in_array($dataType, \framework\orm\types\Type::TEXTUAL_TYPES))
						{
							$dataValue = $this->_quote($dataValue);
							break;
						}
						
						throw new \framework\orm\datasources\OrientDBDatasourceException('Unknown type ' . $dataType);
						break;
				}

				$values .= $dataValue . ', ';
			}

			$values = \rtrim($values, ', ');
			$fields = \rtrim($fields, ', ');

			$req = 'INSERT INTO ' . $entity . '(' . $fields . ')' . ' VALUES (' . $values . ')';


			$response = $this->_link->query($req);

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
	 * @param \framework\orm\Criteria $where 
	 */
	public function delete ($id, \framework\orm\Criteria $where = null)
	{
		return $this->_link->recordDelete($id);
	}

	/**
	 * Find elements from their IDs.
	 * @throws \framework\orm\datasources\OrientDBDatasourceException
	 * @param string|array $primary An ID or an array of IDs
	 * @param string|int The identifier of the entity where to look for (table name, cluster ID, ...)
	 * @param array $inherits
	 * @param array $dependents 
	 * @return array The elements in an array
	 */
	public function find ($primary, $entity, array $inherits = array(), array $dependents = array())
	{
		$found = array();
		$record = null;
		$asArray = null;

		if (!is_array($primary))
		{
			$primary = array($primary);
		}

		foreach ($primary as $id)
		{
			$record = $this->_link->recordLoad($id);
			
			if($record !== false)
			{
				$record->parse();
				$found[] = $this->_recordToMap($record, $id);
			}
		}

		return $found;
	}

	private function _recordToMap (\OrientDBRecord $record, $id)
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
				$map[$index]['value'] = $value->get();
			}
			elseif ($value instanceof \OrientDBTypeDate)
			{
				$map[$index]['value'] = \substr($value->getTime(), 0, 9);
			}
			else
			{
				$map[$index]['value'] = $value;
			}
		}

		$map['id']['value'] = $id;
		return $map;
	}

	private function _mapToRecord (array $map)
	{
		$record = new \OrientDBRecord();

		foreach ($map as $property => $spec)
		{
			if ($spec['storageField'] !== NULL)
			{
				$record->data->$property = $spec['value'];
			}
		}

		$record->parse();

		return $record;
	}

	/**
	 *
	 * @param \framework\orm\Criteria $criteria
	 * @param array $inherits
	 * @param array $dependents 
	 */
	public function findAll (\framework\orm\Criteria $criteria = null, array $inherits = array(), array $dependents = array())
	{
		
	}

	/**
	 *
	 * @param string $id
	 * @param string|OrientDBRecord $data
	 * @param \framework\orm\Criteria $where 
	 * @return bool Update status
	 */
	public function update ($id, $entity, $data, \framework\orm\Criteria $where = null)
	{
		// convert the $data to a record
		$record = $this->_mapToRecord($data);

		// return true on success
		return ($this->_link->recordUpdate($id, $record) !== -1);
	}

	/**
	 * Get the full host, i.e. host:port
	 * @return string
	 */
	protected function _getFullHost ()
	{
		return $this->_host . ':' . $this->_port;
	}
	
	/**
	 * Properly quote a string (i.e. escape the '"' character since it's the one we use to enclose string in requests).
	 * @param string $string The string to quote
	 * @return string 
	 */
	protected function _quote($string)
	{
		return '"'.  \str_replace('"', '\\"', $string).'"';
	}

	/**
	 * Not supported yet
	 */
	public function getQuery ()
	{
		return '';
	}

	/**
	 *
	 * @return \OrientDB
	 */
	public function getLink ()
	{
		return $this->_link;
	}

}