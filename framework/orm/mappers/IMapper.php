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
     * @abstract
     * @param \framework\orm\models\IModel The model to attach.
     */
    public function attach ($object);

    /**
     * @abstract
     * @param $model
     */
    public function isAttached($model);

    /**
     * Get all the attached models.
     * @abstract
     */
    public function getAttachedModels();

    /**
     * @abstract
     * @param $id
     */
    public function getAttachedModel($id);


    /* Datasource manipulation */

    /**
     * Send a request directly to the datasource
     * @abstract
     * @param string $req
     * @return mixed
     */
    public function exec($req);

    /**
     * Retrieve a model from the datasource based on its id.
     * @abstract
     * @param int|string|array $id The models' id(s).
     */
    public function find ($id);

    /**
     * Retrieve several models from the datasource.
     * @abstract
     * @param \framework\orm\utils\Criteria $criteria A set of constraints the results must match.
     * @return \framework\orm\utils\Collection
     */
    public function findAll (\framework\orm\utils\Criteria $criteria = null);

    /**
     * Save a model in the datasource.
     * @abstract
     * @param \framework\orm\models\IModel The model to save.
     */
    public function save (\framework\orm\models\IModel $model);

    /**
     * Save all the models attached to the mapper.)
     * @abstract
     */
    public function saveAll ();

    /**
     * Delete a model from the datasource.
     * @param \framework\orm\utils\Criteria|\framework\orm\models\IModel $criteria
     */
    public function delete ($criteria);



    /* Mapper manipulation */

    /**
     * @abstract
     * @param bool $fetch
     */
    public function fetchRelations($fetch = true);

    /**
     * Customisable initialisation operations called at the end of the mapper's constructor.
     * @abstract
     * @param $params
     * @return
     */
    public function init (array $params = array());

    /**
     * Get the identifier of the entity where the models are stored in the datasource.
     * (i.e. table name, cluster ID, collection name, ect...)
     * @abstract
     * @return string|int
     */
    public function getEntityIdentifier ();

    /**
     * Get the key used to retrieve the model from the components container.
     * @abstract
     */
    public function getModelName ();
}