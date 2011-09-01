<?php 
/**
 * Copyright (C) 2011 - K√©vin O'NEILL, Fran√ßois KLINGLER - <contact@42framework.com>
 * 
 * 42framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * 42framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */
namespace application\modules\cli;
use TheSeer\Tools;

class ConfigBuilder extends \application\modules\cli\AutoloadBuilder
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