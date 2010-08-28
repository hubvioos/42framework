<?php namespace Framework\Modules\Cli;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CliException extends \Exception { }

class CliUtils
{
	/**
	 * Extract request params from the cli
	 * 
	 * @return array
	 */
	public static function extractParams ()
	{
		if ($_SERVER['argc'] === 1)
		{
			return array('action' => 'showDoc', 'params' => array('all'));
		}
		if ($_SERVER['argc'] === 2)
		{
			return array('action' => $_SERVER['argv'][1], 'params' => array());
		}
		
		$params = array('action' => '', 'params' => array());
		for ($i = 1; $i < $_SERVER['argc']; $i++)
		{
			if ($i === 1)
			{
				$params['action'] = $_SERVER['argv'][$i];
			}
			else 
			{
				$params['params'][] = $_SERVER['argv'][$i];
			}
		}
		return $params;
	}
}