<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class error403 extends \Framework\Controller
{
	protected $_response = null;
	
	public function processAction ($request = null)
	{
		$this->_response = \Framework\Response::getInstance()
							->clearResponse()
							->reset()
							->status(403)
							->setBody(\Framework\View::factory('errors', 'error403'));
					
		return true;
	}
	
	public function _after ($request, $actionResponse)
	{
		\Framework\Core::getInstance()->render($this->_response);
	}
}
