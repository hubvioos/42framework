<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ModelException extends \Exception { }

class Model
{
	public function __construct ()
	{
		
	}
	
	public static function factory ()
	{
		return new self();
	}
}