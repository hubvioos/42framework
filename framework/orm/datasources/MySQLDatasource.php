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

/**
 * MySQLDatasource
 *
 * @author mickael
 */
class MySQLDatasource extends \framework\core\FrameworkObject implements \framework\orm\datasources\interfaces\IConnectionDatasource, \framework\orm\datasources\interfaces\IDbDatasource, \framework\orm\datasources\interfaces\IDatasource, \framework\orm\datasources\interfaces\ITransactionDatasource
{

	/**
	 * The PDO object used to perform requests
	 * @var \PDO
	 */
	protected $link;

	/**
	 * The hostname
	 * @var string
	 */
	protected $host;

	/**
	 * The connection's port number
	 * @var int
	 */
	protected $port;

	/**
	 * The user name
	 * @var string
	 */
	protected $user;

	/**
	 * The password used for the connection
	 * @var string
	 */
	protected $password;

	/**
	 * The name of the active database
	 * @var string
	 */
	protected $active = '';

	/**
	 * The pattern the identifiers must match to be considered as valid
	 * @var string
	 */
	protected $pattern = '#[0-9,a-z,A-Z$_]+#';

	/**
	 * The database configuration
	 * @var array
	 */
	protected $config = array();

    /**
     * @var \framework\orm\utils\DatasourceTools
     */
	protected $tools = NULL;

	public function __construct ($host = 'localhost', $port = 3306)
	{
		$this->tools = $this->getComponent('orm.utils.DatasourceTools');

		$this->host = $host;
		$this->port = $port;
	}

    /**
     * Get the connection
     * @return \PDO
     */
	public function getConnection ()
	{
		return $this->link;
	}

    /**
     * Connect to a database
     * @param string $database
     * @param string $user
     * @param string $password
     * @param array $driverOptions
     */
	public function connect ($database, $user = '', $password = '', $driverOptions = array())
	{
		$this->user = $user;
		$this->password = $password;

		try
		{
			$this->link = new \PDO('mysql:dbname=' . $database . ';host=' . $this->host
					. ';port=' . $this->port, $this->user, $this->password, $driverOptions);

			$this->link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			$this->active = $database;
		}
		catch (\Exception $e)
		{
			throw new \framework\orm\datasources\exceptions\ConnectionException($this->host
                . ', ' . $this->user . '@' . $database);
		}
	}

	/**
	 * Close the connection.
	 */
	public function close ()
	{
		unset($this->link);
	}

    /**
     * Execute a request.
     * @throws \framework\orm\datasources\exceptions\RequestException
     * @param $request
     * @return mixed
     */
	public function exec ($request)
	{
        try
        {
            return $this->link->exec($request);
        }
        catch(\Exception $e)
        {
            throw new \framework\orm\datasources\exceptions\RequestException($request, $e);
        }
	}

    /**
     * Execute a query to retrieve data.
     * @throws \framework\orm\datasources\exceptions\RequestException
     * @param $query
     * @return array
     */
	public function query ($query)
	{
        try
        {
            return $this->link->query($query);
        }
        catch(\Exception $e)
        {
            throw new \framework\orm\datasources\exceptions\RequestException($query, $e);
        }
	}

	/**
	 * Delete an entity in the datasource
	 * @param string|int $id
     * @param string $entity
	 * @param \framework\orm\utils\Criteria $where
	 * @return boolean
	 */
	public function delete($id, $entity, \framework\orm\utils\Criteria $where = NULL)
	{
		$this->_validateIdentifier($entity);

		if($where == NULL)
		{
			$req = $this->link->prepare('DELETE FROM '.$entity.' WHERE id = :id LIMIT 1');
			$req->bindValue(':id', $id);
		}
		else
		{
			$req = $this->link->prepare('DELETE FROM '.$entity.' WHERE '.$this->criteriaToString($where));
		}

		return $req->execute();
	}

    /**
     * Find one or several entities based on their ID
     * @param array $primary An array of IDs
     * @param string $entity
     * @return \framework\orm\utils\Collection
     */
	public function find(array $primary, $entity)
	{
		$this->_validateIdentifier($entity);

		$this->_retrieveTableConfig($entity);

		$found = $this->getComponent('orm.utils.Collection');

		$req = $this->link->prepare('SELECT * FROM '.$entity.' WHERE id = :id LIMIT 1');
		$req->bindParam(':id', $id);

		$i = 0;
		foreach($primary as $id)
		{
			$req->execute();
			$result = $req->fetch(\PDO::FETCH_ASSOC);

			if(\is_array($result))
			{
				foreach($result as $name => $value)
				{
					$found[$i][$name] = array(
						'value' => $value
					);
				}
				$i++;
			}
		}

		return $found;
	}

	/**
     * Find all the entities matching a criteria. If the criteria is omited, retrieve all the entities.
	 * @param string $entity
	 * @param \framework\orm\utils\Criteria $criteria
	 * @return array
	 */
	public function findAll($entity, \framework\orm\utils\Criteria $criteria = NULL)
	{
		$this->_validateIdentifier($entity);

		if($criteria != NULL)
		{
			$req = $this->link->prepare('SELECT * FROM '.$entity.' WHERE '.$this->criteriaToString($criteria));
		}
		else
		{
			$req = $this->link->prepare('SELECT * FROM '.$entity);
		}


		$found = $this->getComponent('orm.utils.Collection');

		if($req->execute())
		{
			$i = 0;
			while($result = $req->fetch(\PDO::FETCH_ASSOC))
			{
				foreach($result as $name => $value)
				{
					$found[$i][$name] = array(
						'value' => $value
					);
				}

				$i++;
			}
		}


		return $found;
	}

	/**
	 * Get the datasource-friendly string representation of a criteria
	 * @param \framework\orm\utils\Criteria $criteria
	 * @return string
	 */
	public function criteriaToString(\framework\orm\utils\Criteria $criteria)
	{
		$string = '';

		foreach ($criteria->getConstraints() as $params)
		{
			switch ($params[0])
			{
				case \framework\orm\utils\Criteria::CRITERIA :
					if ($params[1][0] == \framework\orm\utils\Criteria::ASSOCIATION_AND)
					{
						$string .= ' AND ' . $this->criteriaToString($params[1][1]);
					}
					else if ($params[1][0] == \framework\orm\utils\Criteria::ASSOCIATION_OR)
					{
						$string .= ' OR ' . $this->criteriaToString($params[1][1]);
					}
					break;

				case \framework\orm\utils\Criteria::EQUALS :
					$string .= $params[1][0] . ' = ' . $this->tools->quoteParameter($params[1][1]);
					break;
				case \framework\orm\utils\Criteria::GREATER_THAN :
					$string .= $params[1][0] . ' > ' . $params[1][1];
					break;
				case \framework\orm\utils\Criteria::LESS_THAN :
					$string .= $params[1][0] . ' < ' . $params[1][1];
					break;
				case \framework\orm\utils\Criteria::GREATER_THAN_OR_EQUAL :
					$string .= $params[1][0] . ' >= ' . $params[1][1];
					break;
				case \framework\orm\utils\Criteria::LESS_THAN_OR_EQUAL :
					$string .= $params[1][0] . ' <= ' . $params[1][1];
					break;
				case \framework\orm\utils\Criteria::NOT_EQUALS :
					$string .= $params[1][0] . ' <> ' . $params[1][1];
					break;

				case \framework\orm\utils\Criteria::IS_NULL :
					$string .=  'ISNULL('.$params[1].')';
					break;
				case \framework\orm\utils\Criteria::LIKE :
					$string .= $params[1][0] . ' LIKE ' . $this->tools->quoteParameter($params[1][1]);
					break;
				case \framework\orm\utils\Criteria::IN :
					$values = '(';

					foreach ($params[1][1] as $value)
					{
						$values .= $this->tools->quoteParameter($value) . ', ';
					}

					$values = \rtrim($values, ', ') . ')';
					$string .= $params[1][0] . ' IN ' . $values;
					 break;

				case \framework\orm\utils\Criteria::LIMIT :
					$string .= ' LIMIT ' . $params[1][0] . ', ' . $params[1][1];
					break;

				case \framework\orm\utils\MySQLCriteria::BETWEEN :
					$string = $params[1][0].' BETWEEN '.$params[1][1][0].' AND '.$params[1][1][1];

				default:
					break;
			}
		}


		return $string;
	}

	/**
	 * Create a new entity
	 * @throws \Exception
	 * @param string $entity Table name to use
	 * @param mixed $data The data to store in the new row
	 * @param mixed $type NULL
	 * @return int|bool Last insert id
	 */
	public function create($entity, $data, $type = NULL)
	{
		$fields = '';
		$params = '';

		$this->_validateIdentifier($entity);

		foreach ($data as $spec)
		{
			if ($spec['storageField'] !== NULL)
			{
				$this->_validateIdentifier($spec['storageField']);
				$fields .= $spec['storageField'] . ', ';
				$params .= ':'.$spec['storageField'] . ', ';
			}
		}

		// get rid of the extra ", " at the end of each string
		$params = \substr($params, 0, \strlen($params) - 2);
		$fields = \substr($fields, 0, \strlen($fields) - 2);

		$query = $this->link->prepare('INSERT INTO ' . $entity . '(' . $fields . ')' . ' VALUES(' . $params . ')');

		$this->_bindQueryValuesFromMap($query, $data);

		if($query->execute())
		{
			return $this->link->lastInsertId();
		}

		return false;
	}

	/**
	 * Update a row
	 * @param array|string $id An ID (primary key or RecordID) or array of IDs
	 * @param string $entity The entity name
	 * @param mixed $data
	 * @param \framework\orm\utils\Criteria $where
	 * @return boolean
	 */
	public function update($id, $entity, $data, \framework\orm\utils\Criteria $where = NULL)
	{
		$fields = '';
		$this->_validateIdentifier($entity);

		foreach ($data as $spec)
		{
			if ($spec['storageField'] !== NULL)
			{
				$this->_validateIdentifier($spec['storageField']);
				$fields .= $spec['storageField'] . ' = :'.$spec['storageField'].', ';
			}
		}

		// get rid of the extra ", "
		$fields = \substr($fields, 0, \strlen($fields) - 2);

		$idParameter = ':'.time();

		$query = $this->link->prepare('UPDATE ' . $entity . ' SET ' . $fields . ' WHERE id = '.$idParameter);

		$query->bindValue($idParameter, $id);

		$this->_bindQueryValuesFromMap($query, $data);

		return $query->execute();
	}

	/**
	 * Get a \framework\orm\utils\MySQLCriteria instance
	 * @return \framework\orm\utils\MySQLCriteria
	 */
	public function getNativeCriteria()
	{
		return $this->getComponent('orm.utils.MySQLCriteria');
	}


	/**
	 * Begin a transaction
     * @return bool
     */
	public function beginTransaction ()
	{
		return $this->link->beginTransaction();
	}

	/**
	 * Commit all the changes of the current transaction
     * @return bool
     */
	public function commit ()
	{
		return $this->link->commit();
	}

	/**
	 * Rollback all the changes of the current transaction
     * @return bool
     */
	public function rollBack ()
	{
		return $this->link->rollBack();
	}

	/**
	 * Get a table's configurtation
	 * @param string $key
	 * @return array
	 */
	public function getConfiguration ($key = '')
	{
		if ($key == '')
		{
			return $this->config;
		}

		return isset($this->config[$key]) ? $this->config[$key] : '';
	}



	/**
	 * Bind all the params from a map to a query
     * @param \PDOStatement $query
     * @param array|\framework\orm\utils\Map $map
	 * @throws \framework\orm\datasources\exceptions\WrongEntityFormatException
	 */
	protected function _bindQueryValuesFromMap($query, $map)
	{
		foreach ($map as $spec)
		{
			$dataType = $spec['type'];
			$dataValue = $spec['value'];
			$bindType = \PDO::PARAM_STR;

			switch ($dataType)
			{
				case \framework\orm\types\MySQLDate::TYPE_IDENTIFIER:
				case \framework\orm\types\MySQLDateTime::TYPE_IDENTIFIER:
				case \framework\orm\types\MySQLTimestamp::TYPE_IDENTIFIER:
				case \framework\orm\types\Type::RELATION_KEY:
					break;

				default:
					if(\in_array($dataType, $this->getComponent('orm.numericTypes')))
					{
						break;
					}
					elseif(\in_array($dataType, $this->getComponent('orm.textualTypes')))
					{
						break;
					}
					elseif(\in_array($dataType, $this->getComponent('orm.booleanTypes')))
					{
						$dataValue = ($dataValue) ? '1' : '0';
						$bindType = \PDO::PARAM_BOOL;
						break;
					}
					elseif($dataValue === NULL)
					{
						$bindType = \PDO::PARAM_NULL;
						break;
					}
					elseif(\array_key_exists('internal', $spec))
					{
						$dataValue = $dataValue['id']['value'];
						break;
					}


					throw new \framework\orm\datasources\exceptions\WrongDataTypeException($dataType, $dataValue);
					break;
			}

			$query->bindValue(':'.$spec['storageField'], $dataValue, $bindType);
		}
	}

	/**
	 * Get a table's config, i.e. each column's type
	 * @param string $table
	 * @throws \framework\orm\datasources\exceptions\WrongEntityFormatException
	 */
	protected function _retrieveTableConfig($table)
	{
		$this->_validateIdentifier($table);

		if(!\array_key_exists($this->active.'.'.$table, $this->config))
		{
			$result = $this->link->prepare('DESCRIBE '.$table);
			$result->execute();

			$patterns = array('#\(.+\)#', '#\s.*#');

			while($column = $result->fetch(\PDO::FETCH_ASSOC))
			{
				$type = \strtolower(\preg_replace($patterns, '', $column['Type']));
				$this->config[$this->active.'.'.$table][$column['Field']] = $type;
			}
		}
	}

	/**
	 *
	 * @param string $identifier
	 * @return bool
	 */
	protected function _validateIdentifier($identifier)
	{
		if(\strlen($identifier) > 64 || !\preg_match($this->pattern, $identifier))
		{
			throw new \framework\orm\datasources\exceptions\WrongEntityFormatException($identifier);
		}
	}


}