<?php
namespace framework\libs;

class DbProvider
{
	protected static $instance = null;
	
	protected function __construct() {}
	
	protected function __clone() {}
	
	public static function getInstance()
	{
		if(!isset(self::$instance))
		{
			$dsn = Registry::get('database.driver').':host='.Registry::get('database.host').';dbname='.Registry::get('database.dbname');
			self::$instance = new \PDO($dsn, Registry::get('database.username'), Registry::get('database.password'), Registry::get('database.options'));
		}
		
		return self::$instance;
	}
}
?>