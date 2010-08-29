<?php
defined('FRAMEWORK_DIR') or die('Invalid script access');

$config = array(
    'defaultModule' => 'website',
    'defaultAction' => 'index',
	'defaultLayout' => FRAMEWORK_DIR.DS.'views'.DS.'layout'.DS.'defaultLayout.php',
	'defaultCharset' => 'utf-8',
	'defaultLanguage' => 'fr',
	'routes' => array()
	);