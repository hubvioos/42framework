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

namespace framework\orm\models;

/**
 * Interface IModel
 * 
 * This interface must be implemented by every 
 * model that will be attached to a Mapper.
 */
interface IModel
{

	const RELATION_HAS_ONE = 1;
	const RELATION_HAS_MANY = 2;

	/**
	 * Get the unique identifier of the instance.
	 * @return int|string|NULL MUST return NULL if no ID exists yet (i.e. if it's never been stored in the datasource)
	 */
	public function getId ();

    /**
     * Set the unique identifier of the instance.
     * @abstract
     * @param $id
     */
    public function setId($id);
}