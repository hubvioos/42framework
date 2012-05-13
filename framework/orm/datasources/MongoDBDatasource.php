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

class MongoDBDatasource implements \framework\orm\datasources\interfaces\IDatasource,
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
     * @param \framework\orm\utils\Criteria $where
     * @return boolean
     */
    public function delete ($id, $entity, \framework\orm\utils\Criteria $where = NULL)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param array $primary An array of IDs
     * @param string $entity
     * @return \framework\orm\utils\Collection
     */
    public function find (array $primary, $entity)
    {
        /** @var $found \framework\orm\utils\Collection */
        $found = $this->getComponent('orm.utils.collection');

        foreach($primary as $id)
        {
            $found->add($this->link->{$entity}->findOne(new \MongoId($id)));
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
        // TODO: Implement findAll() method.
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



    /**
     * @param array|string $id An ID (primary key or RecordID) or array of IDs
     * @param string $entity The entity name
     * @param mixed $data
     * @param \framework\orm\utils\Criteria $where
     * @return boolean
     */
    public function update ($id, $entity, $data, \framework\orm\utils\Criteria $where = NULL)
    {
        // TODO: Implement update() method.
    }

    /**
     * @return \framework\orm\utils\Criteria
     */
    public function getNativeCriteria ()
    {
        // TODO: Implement getNativeCriteria() method.
    }

    /**
     * Get the string representation of a Criteria
     * @param \framework\orm\utils\Criteria
     * @return string
     */
    public function parseCriteria (\framework\orm\utils\Criteria $criteria)
    {
        // TODO: Implement parseCriteria() method.
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