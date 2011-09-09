<?php


$appConfig = array(
    'siteUrl' => 'http://127.0.0.1/f42/',
    'routes' => array('testroute' => array('module' => 'test', 'action' => 'qwerty', 'params' => array())),
	'events' => array(
		'afterIndex' => array(
			'\\framework\\modules\\website\\controllers\\kuku::kaka'
			)
		),
	'environment' => \framework\core\Core::DEV
    );
