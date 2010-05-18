<?php
use \framework\libs as F;

// définit l'URL du site web
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
	'databases' => array(
		'default' => array(
			'type' => 'mysql',
			'host' => 'localhost',
			'dbname' => 'testdb',
			'username' => 'root',
			'password' => 'root',
			'options' => array()
			)
		),
	'routes' => array(
		'article/:num' => array('module' => 'produit', 'action' => 'view'),
		'article' => array('module' => 'produit', 'action' => 'index')
		)
	)
);
?>
