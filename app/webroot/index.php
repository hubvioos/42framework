<?php
// définit le temps de départ de l'exécution de l'application
if (!defined('APP_START_MICROTIME'))
	define('APP_START_MICROTIME', microtime());
	
// définit la mémoire utilisée au début de l'exécution de l'application
if (!defined('APP_START_MEMORY_USAGE'))
	define('APP_START_MEMORY_USAGE', memory_get_usage());

// définit le "séparateur de dossier" selon l'OS
if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

// définit la racine web de l'application (dossier webroot)
if (!defined('ROOT'))
	define('ROOT', dirname(__FILE__));

// définit l'adresse du dossier contenant le code de l'application (dossier app)
if (!defined('APP'))
	define('APP', dirname(ROOT));

// définit l'adresse du dossier contenant le code du framework (dossier 42framework)
if (!defined('FRAMEWORK'))
	define('FRAMEWORK', dirname(APP).DS.'42framework');

// fonction permettant d'inclure automatiquement les fichiers contenant les classes appelées dans le code
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
    	/*case 'plugin':
    		$file = APP.DS.'plugins'.DS.$directories.'.php';
    		break;
    	case 'vendor':
    		$file = APP.DS.'vendors'.DS.$directories.'.php';
    		break;*/
    }
    
    require($file);
} // fin de autoload

// permet de charger automatiquement les classes appelées dans le code, en appelant la fonction autoload ci-dessus lorsque nécessaire
spl_autoload_register ('autoload');

// définit un raccourci pour le namespace du framework
use \framework\libs as F;

// démarrage de la session
session_start();

// inclusion du fichier de configuration de l'application
require(APP.DS.'config'.DS.'config.php');

// on charge la classe de gestion des erreurs
new F\ErrorHandler(F\Registry::get('displayErrors'), F\Registry::get('errorReporting'), F\Registry::get('logMode'));

// on charge le core du framework, on exécute la requête et on affiche le résultat
F\Core::getInstance()->execute()->display(false);

// on affiche les statistiques d'exécution, uniquement si le paramètre de la fonction display (ci-dessus) est à false
echo 'Execution Time : '.\framework\utils\Benchmark::elapsedTime().' s<br />';

echo 'Start Memory : '.\framework\utils\Benchmark::memoryUsage('appStartMemoryUsage').'<br />';
echo 'End Memory : '.\framework\utils\Benchmark::memoryUsage().'<br />';
?>
