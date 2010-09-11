<?php namespace Application\modules\errors\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class error404 extends \Framework\Controller
{
	protected $_response = null;
	
	public function processAction ($request = null)
	{
		$this->_response = \Framework\Response::getInstance()
							->clearResponse()
							->reset()
							->status(404)
							->setBody(\Framework\View::factory('errors', 'error404');
					
		return true;
	}
	
	public function _after ($request, $actionResponse)
	{
		\Framework\Core::getInstance()->render($this->_reponse);
	}
}
