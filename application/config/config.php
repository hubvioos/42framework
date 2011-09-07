<?php


$appConfig = array(
    'siteUrl' => 'http://localhost/42framework/',
    'routes' => array('testroute' => array('module' => 'test', 'action' => 'qwerty', 'params' => array())),
	'events' => array(
		'afterIndex' => array(
			'\\application\\modules\\website\\controllers\\kuku::kaka'
			)
		),
	'environment' => \framework\core\Core::DEV
    );
