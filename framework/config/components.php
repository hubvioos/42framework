<?php

$fcomponents = array(
	/**
	 * Core
	 * 
	 */
	'core' => array(
		'callable' => function ($c, $args)
		{
			/* @var $c ComponentsContainer */
			return new \framework\core\Core($c);
		},
		'isUnique' => true),
	'httpRequest' => array(
		'callable' => function ($c, $args)
		{
			/* @var $c ApplicationContainer */
			return new \framework\core\http\Request($c->getHistory());
		},
		'isUnique' => true),
	'httpResponse' => array(
		'callable' => function ($c, $args)
		{
			/* @var $c ApplicationContainer */
			return new \framework\core\http\Response();
		},
		'isUnique' => true),
	'request' => array(
		'callable' => function ($c, $args)
		{
			$params = $args[0];
			$state = isset($args[1]) ? $args[1] : null;

			return new \framework\core\Request($params, $state);
		},
		'isUnique' => false),
	'response' => array(
		'callable' => function ($c, $args)
		{
			return new \framework\core\Response();
		},
		'isUnique' => false),
	'dispatcher' => array(
		'callable' => function ($c, $args)
		{
			return new \framework\core\Dispatcher();
		},
		'isUnique' => false),
	'action' => array(
		'callable' => function ($c, $args)
		{
			$controller = $args[0];
			return new $controller;
		},
		'isUnique' => false),
	'model' => array(
		'callable' => function ($c, $args)
		{
			$module = $args[0];
			$action = $args[1];

			$model = 'application\\modules\\' . $module . '\\models\\' . $model;
			return new $model;
		},
		'isUnique' => false),
	'view' => array(
		'callable' => function ($c, $args)
		{
			$module = $args[0];
			$action = $args[1];
			$vars = isset($args[2]) ? $args[2] : false;
			$format = isset($args[3]) ? $args[3] : null;

			return new \framework\core\View($module, $action, $vars, $format);
		},
		'isUnique' => false),
	'eventManager' => array(
		'callable' => function ($c, $args)
		{
			/* @var $c ApplicationContainer */
			return new \framework\core\EventManager($c->_config['events']);
		},
		'isUnique' => true),
	/*
	 * Libs
	 *
	 */
	'errorHandler' => array(
		'callable' => function ($c, $args)
		{
			$errorHandler = new \framework\errorHandler\ErrorHandler();
			foreach ($c->_config['errorHandlerListeners'] as $lis)
			{
				$errorHandler->attach(new $lis());
			}
			$errorHandler->init($c->_config['errorReporting'], $c->_config['displayErrors']);
			return $errorHandler;
		},
		'isUnique' => true),
	'history' => array(
		'callable' => function ($c, $args)
		{
			/* @var $c ApplicationContainer */
			return new \framework\core\http\History('_history', $c->_config['historySize']);
		},
		'isUnique' => true),
	'route' => array(
		'callable' => function ($c, $args)
		{
			/* @var $c ApplicationContainer */
			// return new \framework\libs\Route($c->_config['routes']->toArray());
			return new \framework\libs\Route($c->_config);
		},
		'isUnique' => true),
	'router' => array(
		'callable' => function ($c, $args)
		{
			/* @var $c ApplicationContainer */
			return new \framework\libs\router\Router();
		},
		'isUnique' => true),
	'message' => array(
		'callable' => function ($c, $args)
		{
			/* @var $c ComponentsContainer */
			return new \framework\libs\Message('_flash');
		},
		'isUnique' => true),
	'session' => array(
		'callable' => function ($c, $args)
		{
			return new \framework\libs\Session();
		},
		'isUnique' => true),
	'event' => array(
		'callable' => function ($c, $args)
		{
			return new \framework\libs\Event($args[0], $args[1]);
		},
		'isUnique' => false),
	'cache' => array(
		'callable' => function ($c, $args)
		{
			if (!\array_key_exists($args[0], $c->_config['cache']))
			{
				throw new \InvalidArgumentException('The cache configuration  ' . $args[0] . ' doesn\'t exist');
			}

			return new \framework\libs\Cache($args[0], $c->_config['cache'][$args[0]]);
		},
		'isUnique' => false),
	/**
	 * View Helpers
	 * 
	 */
	'html' => array(
		'callable' => function ($c, $args)
		{
			return new \framework\helpers\HtmlHelper();
		},
		'isUnique' => false),
	'testevent' => array(
		'callable' => function ($c, $args)
		{
			return new \framework\helpers\TestEvent($args[0][0]);
		},
		'isUnique' => true)
);