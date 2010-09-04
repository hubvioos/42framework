<?php
namespace Application\modules\cli;
use TheSeer\Tools;

defined('FRAMEWORK_DIR') or die('Invalid script access');

class ConfigBuilder extends AutoloadBuilder
{
	public function render ()
	{
		$entries = $this->formatEntries($this->classes);
		
		$replace = array_merge($this->variables, 
			array(
				'___CREATED___' => date($this->dateformat, $this->timestamp ? $this->timestamp : time()), 
				'___CLASSLIST___' => join(',' . $this->linebreak . $this->indent, $entries), 
				'___BASEDIR___' => $this->baseDir ? '__DIR__ . ' : '', 
				'___AUTOLOAD___' => uniqid('autoload')));
		return str_replace(array_keys($replace), array_values($replace), $this->template);
	}
	
	protected function formatEntries($config)
	{
		$entries = array();
		foreach ($config as $key => $value)
		{
			if (is_array($value))
			{
				if (!empty($value))
				{
					$v = 'array('.join(',', $this->formatEntries($value)).')';
				}
				else 
				{
					$v = array();
				}
			}
			else
			{
				$v = $value;
			}
			
			if (!is_array($v))
			{
				if (strpos($v, 'array(') !== false)
				{
					$entries[] = "'$key' => $v";
				}
				else 
				{
					if ($v === true)
					{
						$entries[] = "'$key' => true";
					}
					else if ($v === false)
					{
						$entries[] = "'$key' => false";
					}
					else
					{
						$entries[] = "'$key' => '$v'";
					}
				}
			}
			else
			{
				$entries[] = "'$key' => array()";
			}
		}
		return $entries;
	}
}