<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class accessDenied extends \Framework\Controller
{
	public function processAction ($request = null)
	{
		Request::factory('errors', 'error403', array($request))->execute();
	}
}
