<?php namespace Framework\interfaces;
defined('FRAMEWORK_DIR') or die('Invalid script access');

interface iErrorHandler
{
	public function attach(iErrorHandlerListener $observer);
	public function detach(iErrorHandlerListener $observer);
	public function notify();
	/**
	 * @return \Exception
	 */
	public function getError();
}