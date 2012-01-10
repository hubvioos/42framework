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
		'isUnique' => true),
				
	/**
	 * ORM			
	 */
								
	'orm.numericTypes' => array(
		'callable' => function($c, $args)
		{
			return array(
				\framework\orm\types\Type::INTEGER,
				\framework\orm\types\Type::INT,
				\framework\orm\types\Type::TINYINT,
				\framework\orm\types\Type::SMALLINT,
				\framework\orm\types\Type::MEDIUMINT,
				\framework\orm\types\Type::BIGINT,
				\framework\orm\types\Type::FLOAT,
				\framework\orm\types\Type::DOUBLE,
				\framework\orm\types\Type::LONG,
				\framework\orm\types\Type::SHORT,
				\framework\orm\types\Type::DECIMAL,
				\framework\orm\types\Type::REAL
			);
		},
		'isUnique' => true
	),
	
			
	'orm.textualTypes' => array(
		'callable' => function($c, $args)
		{
			return array(
				\framework\orm\types\Type::STRING,
				\framework\orm\types\Type::TEXT,
				\framework\orm\types\Type::MEDIUMTEXT,
				\framework\orm\types\Type::TINYTEXT,
				\framework\orm\types\Type::CHAR,
				\framework\orm\types\Type::VARCHAR,
				\framework\orm\types\Type::VARCHAR2,
				\framework\orm\types\Type::ENUM
			);
		},
		'isUnique' => true
	),
			
			
	'orm.booleanTypes' => array(
		'callable' => function($c, $args)
		{
			return array(
				\framework\orm\types\Type::BOOL,
				\framework\orm\types\Type::BOOLEAN
			);
		},
		'isUnique' => true
	),

	'orm.transparentTypes' => array(
		'callable' => function($c, $args)
		{
			return \array_merge(
					$c->getComponent('orm.numericTypes'),
					$c->getComponent('orm.textualTypes'),
					$c->getComponent('orm.booleanTypes')
				);
		},
		'isUnique' => true
	),

	'orm.utils.Map' => array(
		'callable' => function()
		{
			return new \framework\orm\utils\Map();
		},
		'isUnique' => false
	),
				
	'orm.utils.Collection' => array(
		'callable' => function()
		{
			return new \framework\orm\utils\Collection();
		},
		'isUnique' => false
	),
				
	'orm.utils.Criteria' => array(
		'callable' => function($c, $args)
		{
			if(\count($args) == 1)
			{
				return new \framework\orm\utils\Criteria($args[0]);
			}
			if(\count($args) == 2)
			{
				return new \framework\orm\utils\Criteria($args[0], $args[1]);
			}
			
			return new \framework\orm\utils\Criteria();
		},
		'isUnique' => false
	),
				
	'orm.utils.OrientDBCriteria' => array(
		'callable' => function($c, $args)
		{
			if(\count($args) == 1)
			{
				return new \framework\orm\utils\OrientDBCriteria($args[0]);
			}
			if(\count($args) == 2)
			{
				return new \framework\orm\utils\OrientDBCriteria($args[0], $args[1]);
			}
			
			return new \framework\orm\utils\OrientDBCriteria();
		},
		'isUnique' => false
	),

	/* ADAPTERS */
	'OrientDBDateTimeAdapter' => array(
		'callable' => function($c, $args)
		{
			return new \framework\orm\types\adapters\OrientDBDateTimeAdapter();
		},
		'isUnique' => true
	),
	'OrientDBBooleanAdapter' => array(
		'callable' => function($c, $args)
		{
			return new \framework\orm\types\adapters\OrientDBBooleanAdapter();
		},
		'isUnique' => true
	),
	/* TYPES */
	'OrientDBDateTime' => array(
		'callable' => function($c, $args)
		{
			return new \framework\orm\types\OrientDBDateTime($c->getComponent('OrientDBDateTimeAdapter'));
		},
		'isUnique' => true
	),
	'OrientDBDate' => array(
		'callable' => function($c, $args)
		{
			return $c->getComponent('OrientDBDateTime');
		},
		'isUnique' => true
	),
	'OrientDBBoolean' => array(
		'callable' => function($c, $args)
		{
			return new \framework\orm\types\OrientDBBoolean($c->getComponent('OrientDBBooleanAdapter'));
		},
		'isUnique' => true
	)
);