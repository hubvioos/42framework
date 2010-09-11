<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class forbidden extends \Framework\Controller
{
	public function processAction ($request = null)
	{
		\Framework\Request::factory('errors', 'error403', array($request))->execute();
	}
}
