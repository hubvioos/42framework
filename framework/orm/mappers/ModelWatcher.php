<?php
/**
 * Created by Mickael GOETZ (mitch@hubvioos.com).
 * 28/02/12 - 23:33
 * All right reserved.
 */

namespace framework\orm\mappers;

class ModelWatcher
{
    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var \framework\orm\utils\Map
     */
    protected $fields;

    /**
     * @var array
     */
    protected $models;

    /**
     * @var \framework\libs\ComponentsContainer
     */
    protected $container;

    public function __construct ($modelName, $fields, $container)
    {
        $this->modelName = $modelName;
        $this->fields = $fields;
    }

    /**
     * Add a new model to watch.
     * @param \framework\orm\models\IAttachableModel $model
     */
    public function watch(\framework\orm\models\IAttachableModel $model)
    {
        $this->models[\spl_object_hash($model)] = $this->getMap($model);
    }

    /**
     * Returns true if the model has been modified since it was attached to the mapper. This only checks the model's
     * DIRECT attributes, i.e. if a relation's property has changed but the related object is still the same,
     * this will return true.
     * @param \framework\orm\models\IAttachableModel $model
     * @return bool
     */
    public function hasChanged(\framework\orm\models\IAttachableModel $model)
    {
        if($model->getId() === NULL)
        {
            return true;
        }

        return ($this->container->getComponent('mapper.'.$this->modelName)
            ->modelToMap($model) === $this->models[\spl_object_hash($model)]);
    }

    /**
     * @param $hash
     * @param $map
     */
    public function addMap($hash, $map)
    {
        $this->models[$hash] = $map;
    }

    /**
     * @param \framework\orm\models\IAttachableModel $model
     * @return array
     */
    protected function getMap(\framework\orm\models\IAttachableModel $model)
    {
        $this->models[\spl_object_hash($model)] = $this->container->
            getComponent('mapper'.$this->modelName)->modelToMap($model);
    }
}
