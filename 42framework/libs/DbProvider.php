<?php
namespace framework\libs;

class DbProvider
{
	protected static $connexions = array();
	
	public function getConnexion($connexion = 'default')
	{
		if(!empty(self::$connexions[$connexion]))
		{
			self::$connexions[$connexion] = $this->createConnexion($connexion);
		}
		
		return self::$connexions[$connexion];
	}
	
	public function createConnexion($connexion)
	{
		$dbConfig = Registry::get('databases.'.$connexion);
		
		if($dbConfig['type'] == 'mongo')
		{
			
		}
		else
		{
			$dsn = $dbConfig['type'].':host='.$dbConfig['host'].';dbname='.$dbConfig['dbname'];
			self::$connexions[$connexion] = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
		}
	}
	
	public function deleteConnexion($connexion)
	{
		self::$connexions[$connexion] = null;
	}
}
?>
