<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class serviceUnavailable extends generic
{
	public function processAction ($request = null)
	{
		\Framework\Request::factory('errors', 'error503', array($request))->execute();
	}
}
