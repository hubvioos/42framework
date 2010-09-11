<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class serviceUnavailable extends \Framework\Controller
{
	public function processAction ($request = null)
	{
		Request::factory('errors', 'error503', array($request))->execute();
	}
}
