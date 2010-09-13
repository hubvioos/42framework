<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class notFound extends generic
{
	public function processAction ($request = null)
	{
		\Framework\Request::factory('errors', 'error404', array($request))->execute();
	}
}
