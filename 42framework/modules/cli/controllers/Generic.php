<?php namespace Application\modules\cli\controllers;
use Framework;

defined('FRAMEWORK_DIR') or die('Invalid script access');

class CliGenericException extends \Exception { }

class CliGeneric extends CliCommand
{
	public function showDoc ()
	{
		
	}
}