<?php namespace Application\modules\cli\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CliException extends \Exception { }

class CliCommand extends \Framework\Controller
{
	/**
	 * Executes the action corresponding to the current request
	 * 
	 * @param Framework\Request $request
	 */
	public function _before($request)
	{
		if (!$request->isInternal)
		{
			$this->_response = \Framework\Request::factory('errors', 'accesDenied', array($request))->execute();
			return false;
		}
		return true;
	}
	
	public function _after($request, $actionResponse)
	{
		$this->setView(false);
		\Framework\View::setGlobal('layout', false);
	}
}