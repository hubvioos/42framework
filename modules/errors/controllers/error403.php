<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class error403 extends generic
{
	public function processAction ($request = null)
	{
		$this->_response->status(403)->setBody(\Framework\View::factory('errors', 'error403'));
		return true;
	}
}
