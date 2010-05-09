<?php
namespace framework\libs;

class DbProvider
{
	protected static $instance = null;
	
	protected function __construct()
	{
		$dbType = Registry::get('database.driver');
		
		if($dbType == 'mongo')
		{
			
		}
		else
		{
			$dsn = Registry::get('database.driver').':host='.Registry::get('database.host').';dbname='.Registry::get('database.dbname');
			return new \PDO($dsn, Registry::get('database.username'), Registry::get('database.password'), Registry::get('database.options'));
		}
	}
	
	protected function __clone() {}
	
	public static function getInstance()
	{
		if(!isset(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
}
?>
