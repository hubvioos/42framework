<?php
/*
// permet d'inclure automatiquement les fichiers contenant les classes appelées dans le code
function autoload($class)
{
    $file = APP.DS.str_replace('\\', '/', $class).'.php';
    require($file);
    
    /*$file = $class.'.php';
    
    if (file_exists($path = APP_LIBS . DS . $file) OR file_exists($path = APP_MODELS . DS . $file) OR file_exists($path = APP_MODULES . DS . $file) OR file_exists($path = APP_OBJECTS . DS . $file))
    {
    	require_once($path);
    }
}

// définie les routes de l'application sous forme de tableau de forme route => redirect
$routes = array(
	'article/:id/:suffix' => array('module' => 'produit', 'action' => 'view', 'id' => '[0-9]+', 'suffix' => '[a-zA-Z0-9_-]+'),
	'article' => array('module' => 'produit', 'action' => 'index')
);

$config = array(
	'defaultModule' => 'globals',
	'defaultAction' => 'index',
	'prefixes' => array('admin', 'membre')
);*/

// définie l'URL du site web
use \framework\libs as F;

if (!defined('APP_BASE_URL'))
	define('APP_BASE_URL', 'http://localhost:80/framework/');

// on charge la configuration de l'application
new F\Registry(array(
	'defaultModule' => 'globals',
	'defaultAction' => 'index',
	'prefixes' => array('admin', 'membre'),
	'errorReporting' => E_ALL | E_DEPRECATED,
	'displayErrors' => 1,
	'logMode' => 'none',
	'envMode' => 'dev',
	'defaultCharset' => 'utf-8',
	'defaultLanguage' => 'fr-fr',
	'defaultPageTitle' => '42medias.com',
	'database' => array(
		'driver' => 'mysql',
		'host' => 'localhost',
		'dbname' => 'testdb',
		'username' => 'root',
		'password' => 'root',
		'options' => array()
		),
	'routes' => array(
		'article/:num' => array('module' => 'produit', 'action' => 'view'),
		'article' => array('module' => 'produit', 'action' => 'index')
		)
	)
);
?>
