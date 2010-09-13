<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class accessDenied extends generic
{
	public function processAction ($request = null)
	{
		\Framework\Request::factory('errors', 'error403', array($request))->execute();
	}
}
