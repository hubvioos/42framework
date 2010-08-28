<?php namespace Application\modules\cli;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CliUtils
{
	/**
	 * Extract request params from the cli
	 * 
	 * @return array
	 */
	public static function extractParams ()
	{
		return array('action' => 'compileAutoload', 'params' => array());
		if ($argc === 1)
		{
			return array('action' => 'showDoc', 'params' => array('all'));
		}
		if ($argc === 2)
		{
			return array('action' => $argv[1], 'params' => array());
		}
		
		$params = array('action' => '', 'params' => array());
		for ($i = 1; $i < $argc; $i++)
		{
			if ($i === 1)
			{
				$params['action'] = $argv[$i];
			}
			else 
			{
				$params['params'][] = $argv[$i];
			}
		}
		return $params;
	}
}