<?php

/**
 * Default routes 
 */
$routes = array(
	/*'module_action_params' => array(
		'pattern' => '/<:module>/<:action>/<:params|[/a-zA-Z0-9\_\-\+\%\s]+>(.<:format>)',
		'defaults' => array(
			'format' => 'html'
		)
	),*/
	/*'module_action' => array(
		'pattern' => '/<:module>/<:action>(.<:format>)',
		'defaults' => array(
			'format' => 'html'
		)
	),
	/*'module' => array(
		'pattern' => '/<:module>(.<:format>)',
		'defaults' => array(
			'action' => 'index',
			'format' => 'html'
		)
	),*/
	'default' => array(
		'pattern' => '/',
		'defaults' => array(
			'module' => 'website',
			'action' => 'index',
			'format' => 'html'
		)
	)
);