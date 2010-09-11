<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class notFound extends \Framework\Controller
{
	public function processAction ($request = null)
	{
		\Framework\Request::factory('errors', 'error404', array($request))->execute();
	}
}
