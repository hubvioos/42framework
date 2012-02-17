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

/**
 * Interface NewIMapper 
 */
interface IMapper
{
	/* Model manipultaion */

	/**
	 * Attach a model to the mapper.
	 * @param \framework\orm\models\IAttachableModel The model to attach.
	 */
	public function attach (\framework\orm\models\IAttachableModel $object);

	/**
	 * Detach a model from the mapper.
	 * @param mixed $model The model to detach or its id
	 */
	public function detach ($model);

	/* Datasource manipulation */

	/**
	 * Retrieve a model from the datasource based on its id.
	 * @param int|string|array $id The models' id(s).
	 * @param bool $attach Attach the retrieved model(s) if found.
	 */
	public function find ($id, $attach = true);

	/**
	 * Retrieve several models from the datasource.
	 * @param \framework\orm\Criteria A set of constraints the results must match. 
	 */
	public function findAll (\framework\orm\utils\Criteria $criteria = null, $attach = true);

	/**
	 * Save a model in the datasource. 
	 * @param \framework\orm\models\IAttachableModel The model to save.
	 */
	public function save (\framework\orm\models\IAttachableModel $model);

	/**
	 * Delete a model from the datasource.
	 * @param \framework\orm\Criteria|\framework\orm\models\IAttachableModel   
	 */
	public function delete ($criteria);



	/* Mapper manipulation */

	/**
	 * Customisable initialisation operations called at the end of the mapper's constructor.
	 */
	public function init ();

	/**
	 * Get the identifier of the entity where the models are stored in the datasource.
	 * (i.e. table name, cluster ID, collection name, ect...)
	 * @return string|int
	 */
	public function getEntityIdentifier ();

	/**
	 * Get the key used to retrieve the model from the components container. 
	 */
	public function getModelName ();

	/**
	 * Get all the attached models.
	 */
	public function getAttachedModels ();
}