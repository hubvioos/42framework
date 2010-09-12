<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class error503 extends generic
{
	public function processAction ($request = null)
	{
		$this->_response->status(503)->setBody(\Framework\View::factory('errors', 'error503'));				
		return true;
	}
}
