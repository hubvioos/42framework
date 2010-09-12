<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class error404 extends generic
{
	public function processAction ($request = null)
	{
		$this->_response->status(404)->setBody(\Framework\View::factory('errors', 'error404'));
		return true;
	}
}
