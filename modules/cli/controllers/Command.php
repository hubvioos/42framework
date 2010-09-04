<?php namespace Application\modules\cli\controllers;
use Framework;

defined('FRAMEWORK_DIR') or die('Invalid script access');

class CliException extends \Exception { }

class CliCommand extends \Framework\Controller
{
	/**
	 * Executes the action corresponding to the current request
	 * 
	 * @param Framework\Request $request
	 */
	public function before($request)
	{
		if (!$request->isInternal)
		{
			$this->response = \Framework\Request::factory('errors', 'accesDenied', array($request))->execute();
			return false;
		}
		return true;
	}
	
	public function after($request, $actionResponse)
	{
		$this->setView(false);
		\Framework\View::setGlobal('layout', false);
	}
}