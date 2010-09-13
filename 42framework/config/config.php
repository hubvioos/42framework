<?php
defined('FRAMEWORK_DIR') or die('Invalid script access');

$config = array(
    'errorReporting' => E_ALL|E_STRICT,
	'displayErrors' => 1,
	'defaultModule' => 'website',
    'defaultAction' => 'index',
	'defaultLayout' => false,
	'defaultCharset' => 'utf-8',
	'defaultLanguage' => 'fr',
	'defaultTimezone' => 'Europe/Paris',
	'viewExtension' => '.php',
	'siteUrl' => 'http://localhost/',
	'routes' => array(),
	'historySize' => 2
	);