<?php namespace Framework\interfaces;
defined('FRAMEWORK_DIR') or die('Invalid script access');

interface iErrorHandlerListener
{
	public function update(iErrorHandler $subject);
}