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

class MongoDBDatasource extends \framework\core\FrameworkObject implements
    \framework\orm\datasources\interfaces\IDatasource,
    \framework\orm\datasources\interfaces\IConnectionDatasource,
    \framework\orm\datasources\interfaces\IDbDatasource
{

    /**
     * The Mongo object connection
     * @var \Mongo
     */
    protected $connection;

    /**
     * @var \MongoDB
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


    public function __construct($host = \Mongo::DEFAULT_HOST, $port = \Mongo::DEFAULT_PORT)
    {
        $this->host = $host;
        $this->port = $port;

        try
        {
            $this->connection = new \Mongo('mongodb://'.$this->host.':'.$this->port);
        }
        catch(\Exception $e)
        {
            throw new \framework\orm\datasources\exceptions\ConnectionException($this->host.':'.$this->port,
                \framework\orm\datasources\exceptions\ConnectionException::HOST, $e);
        }

    }

    public function getConnection ()
    {
        return $this->link;
    }

    public function connect ($database, $user = '', $password = '')
    {
        try
        {
            $this->link = $this->connection->selectDB($database);

            if($user !== '' && $password !== '')
            {
                $this->link->authenticate($user, $password);
            }

            $this->active = $database;
        }
        catch(\Exception $e)
        {
            throw new \framework\orm\datasources\exceptions\ConnectionException($database,
                \framework\orm\datasources\exceptions\ConnectionException::DATABASE, $e);
        }
    }

    public function close ()
    {
        $this->link = NULL;
        $this->connection->close();
    }

    /**
     * @param string|int $id
     * @param string $entity
     * @param \framework\orm\utils\Criteria $criteria
     * @return boolean
     */
    public function delete ($id, $entity, \framework\orm\utils\Criteria $criteria = NULL)
    {
        if($criteria === NULL)
        {
            try
            {
                $this->link->{$entity}->remove(array('_id' => new \MongoId($id)));
                return true;
            }
            catch(\Exception $e)
            {
                throw new \framework\orm\datasources\exceptions\DatasourceException('Unable to remove document with
                _id='.$id.'from collection '.$entity);
            }
        }
        else
        {
            try
            {
                $this->link->{$entity}->remove($this->parseCriteria($criteria), array('justOne' => true));
                return true;
            }
            catch(\Exception $e)
            {
                throw new \framework\orm\datasources\exceptions\DatasourceException('Unable to remove documents from
                '.$entity.' matching your criteria');
            }
        }
    }

    /**
     * @param array $primary An array of IDs
     * @param string $entity
     * @return \framework\orm\utils\Collection
     */
    public function find (array $primary, $entity)
    {
        /** @var $found \framework\orm\utils\Collection */
        $found = $this->getComponent('orm.utils.Collection');
        /** @var $collection \MongoCollection */
        $collection = $this->link->{$entity};

        foreach($primary as $id)
        {
            $doc = $collection->findOne(array('_id' => new \MongoId($id)));

            if($doc !== NULL)
            {
                $found->add($this->_documentToMap($doc));
            }
        }

        return $found;
    }

    /**
     * @param string $entity
     * @param \framework\orm\utils\Criteria $criteria
     * @return \framework\orm\utils\Collection
     */
    public function findAll ($entity, \framework\orm\utils\Criteria $criteria = NULL)
    {
        /** @var $found \framework\orm\utils\Collection */
        $found = $this->getComponent('orm.utils.Collection');

        if($criteria === NULL)
        {
            foreach($this->link->{$entity}->find () as $doc)
            {
                if($doc !== NULL)
                {
                    $found->add($this->_documentToMap($doc));
                }
            }
        }
        else
        {
            foreach($this->link->{$entity}->find($this->parseCriteria($criteria)) as $doc)
            {
                if($doc !== NULL)
                {
                    $found->add($this->_documentToMap($doc));
                }
            }
        }

        return $found;
    }

    /**
     *
     * @param string $entity Resource Name to use
     * @param mixed $data Can be a multi-dimensional array to insert many records or a single array to insert one record
     * @param mixed $type The type of resource to create if necessary
     * @throws exceptions\DatasourceException
     * @return int|boolean Last insert id (if supported by the DataSource and Resource) otherwise a boolean
     */
    public function create ($entity, $data, $type = NULL)
    {
        $document = $this->_mapToDocument($data);

        try
        {
            $this->link->{$entity}->insert($document);

            return $document['_id'].'';
        }
        catch(\Exception $e)
        {
            return false;
            //throw new \framework\orm\datasources\exceptions\DatasourceException('Unable to create '.$entity.'
            //document', $e);
        }
    }


    protected function _mapToDocument($map)
    {
        $doc = array();

        foreach($map as $specs)
        {
            // if it's not the object's id
            if($specs['storageField'] != 'id')
            {
                // relation handling
                if(\array_key_exists('relation', $specs))
                {
                    if($specs['internal'])
                    {
                        if($specs['relation'] == \framework\orm\models\IModel::RELATION_HAS_ONE)
                        {
                            $doc[$specs['storageField']] = $specs['value']['id']['value'];
                        }

                        if($specs['relation'] == \framework\orm\models\IModel::RELATION_HAS_MANY)
                        {
                            $doc[$specs['storageField']] = array();

                            foreach($specs['value'] as $relation)
                            {
                                $doc[$specs['storageField']][] = $relation['id']['value'];
                            }
                        }
                    }
                }
                // non relation handling
                else
                {
                    $doc[$specs['storageField']] = $specs['value'];
                }
            }
            // set the id field only if a non empty value is provided
            elseif(!empty($specs['value']))
            {
                $doc['_id'] = new \MongoId($specs['value']);
            }
        }

        return $doc;
    }


    protected function _documentToMap($doc)
    {
        $map = $this->getComponent('orm.utils.Map');

        foreach($doc as $name => $value)
        {
            if($name !== '_id')
            {
                $map[$name] = array(
                    'value' => $value
                );
            }
        }

        $map['id'] = array('value' => $doc['_id'].'');

        return $map;
    }

    /**
     * @param array|string $id An ID (primary key or RecordID) or array of IDs
     * @param string $entity The entity name
     * @param mixed $data
     * @param \framework\orm\utils\Criteria $where
     * @return boolean
     */
    public function update ($id, $entity, $data, \framework\orm\utils\Criteria $where = NULL)
    {
        $doc = $this->_mapToDocument($data);

        try
        {
            $this->link->{$entity}->update(array('_id' => new \MongoId($id)), $doc);
            return true;
        }
        catch(\Exception $e)
        {
            // TODO log things!
            return false;
        }
    }

    /**
     * @return \framework\orm\utils\Criteria
     */
    public function getNativeCriteria ()
    {
        return $this->getComponent('orm.utils.MongoDBCriteria');
    }

    /**
     * Get the string representation of a Criteria
     * @param \framework\orm\utils\Criteria
     * @return array
     */
    public function parseCriteria (\framework\orm\utils\Criteria $criteria)
    {
        $c = array();
        $constraints = $criteria->getConstraints();

        foreach($constraints as $constraint)
        {
            $elem = NULL;
            $field = NULL;
            switch($constraint[0])
            {
                case \framework\orm\utils\Criteria::CRITERIA:
                    if($constraint[1][0] == \framework\orm\utils\MongoDBCriteria::ASSOCIATION_NOT)
                    {
                        $not = $this->parseCriteria($constraint[1][1]);
                        list($key) = \array_keys($not);
                        $field = $key;
                        $elem = array('$not' => $not[$key]);
                    }
                    elseif($constraint[1][0] == \framework\orm\utils\MongoDBCriteria::ASSOCIATION_AND)
                    {

                    }
                    elseif($constraint[1][0] == \framework\orm\utils\MongoDBCriteria::ASSOCIATION_OR)
                    {

                    }
                    break;
                case \framework\orm\utils\Criteria::EQUALS:
                    $elem = $constraint[1][1];
                    break;
                case \framework\orm\utils\Criteria::NOT_EQUALS:
                    $elem = array('$ne' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\Criteria::LESS_THAN:
                    $elem = array('$lt' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\Criteria::LESS_THAN_OR_EQUAL:
                    $elem = array('$lte' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\Criteria::GREATER_THAN:
                    $elem = array('$gt' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\Criteria::GREATER_THAN_OR_EQUAL:
                    $elem = array('$gte' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\Criteria::IN:
                    $elem = array('$in' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\Criteria::NOT_IN:
                    $elem = array('$nin' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\Criteria::IS_NULL:
                    $field = $constraint[1];
                    $elem = array('$type' => 10);
                    break;
                case \framework\orm\utils\Criteria::IS_NOT_NULL:
                    $elem = array('$not' => array('$type' => 10));
                    break;
                case \framework\orm\utils\MongoDBCriteria::ALL:
                    $elem = array('$all' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\MongoDBCriteria::MODULO:
                    $elem = array('$mod' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\MongoDBCriteria::EXISTS:
                    $elem = array('$exists' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\MongoDBCriteria::TYPE:
                    $elem = array('$type' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\MongoDBCriteria::LIKE:
                    $elem = array('$regex' => $constraint[1][1]);
                    break;
                case \framework\orm\utils\MongoDBCriteria::SIZE:
                    $elem = array('$size' => $constraint[1][1]);
                    break;
            }

            if($elem !== NULL)
            {
                if($field === NULL)
                {
                    $field = $constraint[1][0];
                }

                if(isset($c[$field]))
                {
                    $c[$field] = \array_merge($c[$field], $elem);
                }
                else
                {
                    $c[$field] = $elem;
                }
            }
        }

        return $c;
    }

    /**
     * Execute a request.
     * @param $query
     * @return mixed
     */
    public function exec ($query)
    {
        // TODO: Implement exec() method.
    }

    /**
     * Execute a query to retrieve data.
     * @param $query
     * @return mixed
     */
    public function query ($query)
    {
        // TODO: Implement query() method.
    }


}