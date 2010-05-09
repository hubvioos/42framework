<?php
$appStartMemoryUsage = memory_get_usage();
$appStartTime = microtime();

// définie le "séparateur de dossier" selon l'OS
if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

// définie la racine web de l'application (dossier webroot)
if (!defined('ROOT'))
	define('ROOT', dirname(__FILE__));

// définie l'adresse du dossier contenant le code de l'application (dossier app)
if (!defined('APP'))
	define('APP', dirname(dirname(__FILE__)));

// définie l'adresse du dossier contenant le code du framework (dossier 42framework)
if (!defined('FRAMEWORK'))
	define('FRAMEWORK', dirname(dirname(dirname(__FILE__))).DS.'42framework');

// définie l'URL du site web
//if (!defined('APP_BASE_URL'))
//	define('APP_BASE_URL', 'http://localhost:80/framework/');

// permet d'inclure automatiquement les fichiers contenant les classes appelées dans le code
function autoload($class)
{
    $directories = explode('\\', ltrim($class, '\\'));
    $directory = array_shift($directories);
    $directories = implode(DS, $directories);
    
    switch($directory)
    {
    	case 'framework':
    		$file = FRAMEWORK.DS.$directories.'.php';
    		break;
    	
    	case 'app':
    		$file = APP.DS.$directories.'.php';
    		break;
    }
    
    require($file);
}

// permet de charger automatiquement les classes appelées dans le code
spl_autoload_register ('autoload');

use \framework\libs as F;

session_start();
//session_destroy();

// on charge la classe de benchmark
$benchmark = new \framework\utils\Benchmark($appStartTime, $appStartMemoryUsage);

require(APP.DS.'config'.DS.'config.php');

/*// on charge la configuration de l'application
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
);*/

// on charge la classe de gestion des erreurs
new F\ErrorHandler(F\Registry::get('displayErrors'), F\Registry::get('errorReporting'), F\Registry::get('logMode'));

// on charge le core du framework, on exécute la requête et on affiche le résultat
F\Core::getInstance()->execute()->display(false);

// on affiche les statistiques d'exécution, uniquement si le paramètre de la fonction display (ci-dessus) est à false
echo 'Execution Time : '.$benchmark->elapsedTime().' s<br />';

echo 'Start Memory : '.$benchmark->memoryUsage('appStartMemoryUsage').'<br />';
echo 'End Memory : '.$benchmark->memoryUsage().'<br />';
?>
