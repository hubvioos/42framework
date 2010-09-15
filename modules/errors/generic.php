<?php namespace Application\modules\errors;
defined('FRAMEWORK_DIR') or die('Invalid script access');

abstract class generic extends \Framework\Controller
{
	protected $_response = null;
	
	public function _before(\Framework\Request $request = null)
	{
		$this->_response = \Framework\Response::getInstance()
							->clearResponse()
							->reset();
		return true;
	}
	
	public function _after (\Framework\Request $request, $actionResponse)
	{
		\Framework\Core::getInstance()->render($this->_response);
	}
}
