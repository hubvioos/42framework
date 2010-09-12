<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ModelException extends \Exception { }

class Model
{
	protected $_dataSources = array();
	
	protected $_dataSourcesConfig = array();
	
	public function __construct ()
	{
		if(!empty($this->_dataSourcesConfig))
		{
			foreach($this->_dataSourcesConfig as $name => $class)
			{
				$this->loadDatasource($name, $class);
			}
		}
	}
	
	public static function factory ()
	{
		return new self();
	}
	
	public function __get($key)
	{
		return $this->loadDatasource($key);
		
		throw new ModelException('Unknown datasource.');
	}
	
	protected function loadDatasource($name, $class = null)
	{
		if (!isset($this->_dataSources[$name]))
		{
			if ($class === null)
			{
				throw new ModelException(__METHOD__.' : invalid argument $class');
			}
			$this->_dataSources[$name] = new $class;
		}
		return $this->_dataSources[$name];
	}
}