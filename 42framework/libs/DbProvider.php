<?php
namespace framework\libs;

// La classe DbProvider se charge de se connecter à la base de données selon la configuration de l'application.
// Elle stocke la connection dans la propriété $connexions afin de pouvoir la retourner directement si nécessaire, au lieu d'effectuer une nouvelle connection.
// DbProvider peut gérer plusieurs connections différentes.
class DbProvider
{
	// stocke les différentes connections
	protected static $connexions = array();
	
	// retourne la connection demandée, en la créant si elle n'existe pas.
	public static function getConnexion($connexion = 'default')
	{
		if(!empty(self::$connexions[$connexion]))
		{
			self::createConnexion($connexion);
		}
		
		return self::$connexions[$connexion];
	}
	
	// crée une connection
	public static function createConnexion($connexion)
	{
		$dbConfig = Registry::get('databases.'.$connexion);
		
		// on teste le type de bdd. 'mongo' pour MongoDB, les autres types correspondent aux drivers de PDO
		if($dbConfig['type'] == 'mongo')
		{
			// pour de meilleures performances, la chaîne de connection n'est générée qu'une seule fois.
			// Les fois suivantes, elle est directement récupérée dans la configuration
			if(!empty($dbConfig['dsn']))
			{
				if(!empty($dbConfig['username']) && !empty($dbConfig['password']))
				{
					$auth = $dbConfig['username'].':'.$dbConfig['password'].'@';
				}
				else
				{
					$auth = '';
				}
				
				$servers = '';
				
				if(!empty($dbConfig['host']) && !empty($dbConfig['port']))
				{
					$servers = $dbConfig['host'].':'.$dbConfig['port'];
				}
				else
				{
					foreach($dbConfig['servers'] as $serv)
					{
						$servers .= ','.$serv['host'].':'.$serv['port'];
					}
					
					$servers = ltrim($servers, ',');
				}
				
				if(empty($dbConfig['dbname']))
				{
					$dbname = '';
				}
				else
				{
					$dbname = '/'.$dbConfig['dbname'];
				}
				
				$dsn = 'mongodb://'.$auth.$servers.$dbname;
				Registry::set('databases.'.$connexion.'dsn', $dsn);
			}
			else
			{
				$dsn = $dbConfig['dsn'];
			}
			
			self::$connexions[$connexion] = new \Mongo($dsn, $dbConfig['options']);
		}
		else
		{
			// pour de meilleures performances, la chaîne de connection n'est générée qu'une seule fois.
			// Les fois suivantes, elle est directement récupérée dans la configuration
			if(!empty($dbConfig['dsn']))
			{
				$dsn = $dbConfig['type'].':host='.$dbConfig['host'].';dbname='.$dbConfig['dbname'];
				Registry::set('databases.'.$connexion.'dsn', $dsn);
			}
			else
			{
				$dsn = $dbConfig['dsn'];
			}
			
			self::$connexions[$connexion] = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
		}
	}
	
	// supprime une connection
	public static function deleteConnexion($connexion)
	{
		self::$connexions[$connexion] = null;
	}
}
?>
