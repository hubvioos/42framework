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

namespace framework\orm\datasources\interfaces;

interface IDatasource
{
	/**
	 * @abstract
	 * @param string|int $id
     * @param string $entity
	 * @param \framework\orm\utils\Criteria $where
	 * @return boolean
	 */
	public function delete($id, $entity, \framework\orm\utils\Criteria $where = NULL);

    /**
     * @abstract
     * @param array $primary An array of IDs
     * @param string $entity
     * @return \framework\orm\utils\Collection
     */
	public function find(array $primary, $entity);

    /**
     * @abstract
     * @param string $entity
     * @param \framework\orm\utils\Criteria $criteria
     * @return \framework\orm\utils\Collection
     */
	public function findAll($entity, \framework\orm\utils\Criteria $criteria = NULL);

	/**
	 * @abstract
	 * @throws \Exception
	 * @param string $entity Resource Name to use
	 * @param mixed $data Can be a multi-dimensional array to insert many records or a single array to insert one record
	 * @param mixed $type The type of resource to create if necessary
	 * @return int|boolean Last insert id (if supported by the DataSource and Resource) otherwise a boolean
	 */
	public function create($entity, $data, $type = NULL);

	/**
	 * @abstract
	 * @param array|string $id An ID (primary key or RecordID) or array of IDs
	 * @param string $entity The entity name
	 * @param mixed $data
	 * @param \framework\orm\utils\Criteria $where
	 * @return boolean
	 */
	public function update($id, $entity, $data, \framework\orm\utils\Criteria $where = NULL);
	
    /**
	 * @abstract
	 * @return \framework\orm\utils\Criteria
	 */
	public function getNativeCriteria();
	
	/**
	 * Get the string representation of a Criteria
	 * @abstract
	 * @param \framework\orm\utils\Criteria
	 * @return string 
	 */
	public function criteriaToString(\framework\orm\utils\Criteria $criteria);
}