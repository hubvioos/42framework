<?php
namespace framework\libs;

class Model
{
	protected $datasourcesUsed = array();
	
	public function __construct()
	{
		if(!empty($this->useDatasources))
		{
			foreach($this->useDatasources as $name => $class)
			{
				$this->loadDatasource($name, $class);
			}
		}
	}
	
	public function __get($key)
	{
		if(isset($this->datasourcesUsed[$key]))
		{
			return $this->datasourcesUsed[$key];
		}
		
		throw new Exception('La datasource appelée n\'est pas chargée !');
	}
	
	public function loadDatasource($name, $class = null)
	{
		if($class == null)
		{
			$class = $name;
		}
		
		if(in_array($class, array('PdoDatasource', 'MongoDatasource')))
		{
			$model = 'framework\libs\datasources\\'.$class;
		}
		else
		{
			$model = 'app\models\\'.$class;
		}
		
		$this->datasourcesUsed[$name] = new $model;
		
		return $this->datasourcesUsed[$name];
	}
}
?>