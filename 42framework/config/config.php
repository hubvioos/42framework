<?php
// permet d'inclure automatiquement les fichiers contenant les classes appelées dans le code
function autoload($class)
{
    $file = APP.DS.str_replace('\\', '/', $class).'.php';
    require($file);
    
    /*$file = $class.'.php';
    
    if (file_exists($path = APP_LIBS . DS . $file) OR file_exists($path = APP_MODELS . DS . $file) OR file_exists($path = APP_MODULES . DS . $file) OR file_exists($path = APP_OBJECTS . DS . $file))
    {
    	require_once($path);
    }*/
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
);
?>