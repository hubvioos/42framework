<?php
namespace Application\modules\cli;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ActionsMapBuilder extends \TheSeer\Tools\AutoloadBuilder
{
	public function render ()
	{
		$entries = array();
		foreach ($this->classes as $module => $actions)
		{
			foreach ($actions as $action => $class)
			{
				$actionsMap[] = "'$action' => '$class'";
			}
			$entries[] = "'$module' => array(".
					$this->linebreak.$this->indent.$this->indent.join(',' . $this->linebreak . $this->indent. $this->indent, $actionsMap).
					$this->linebreak.$this->indent."),";
		}
		
		$replace = array_merge($this->variables, 
			array(
				'___CREATED___' => date($this->dateformat, $this->timestamp ? $this->timestamp : time()), 
				'___CLASSLIST___' => join(',' . $this->linebreak . $this->indent, $entries), 
				'___BASEDIR___' => $this->baseDir ? '__DIR__ . ' : '', 
				'___AUTOLOAD___' => uniqid('autoload')));
		return str_replace(array_keys($replace), array_values($replace), $this->template);
	}
}