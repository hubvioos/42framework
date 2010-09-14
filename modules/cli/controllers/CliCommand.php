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
	protected function _before($request)
	{
		if ($request->getState() == \Framework\Request::CLI_STATE)
		{
			$this->_response = \Framework\Request::factory('errors', 'error403', array($request))->execute();
			return false;
		}
		return true;
	}
	
	protected function _after($request, $actionResponse)
	{
		$this->setView(false);
		\Framework\View::setGlobal('layout', false);
	}
}