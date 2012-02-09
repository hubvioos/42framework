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
namespace modules\cli;

class ConfigBuilder extends \modules\cli\AutoloadBuilder
{
	public function render ()
	{
		$entries = $this->formatEntries($this->classes);

		$replace = \array_merge($this->variables, array(
			'___CREATED___' => \date($this->dateformat, $this->timestamp ? $this->timestamp : \time()),
			'___CLASSLIST___' => $entries, //\implode(',' . $this->linebreak . $this->indent, $entries), 
			'___BASEDIR___' => $this->baseDir ? '__DIR__ . ' : '',
			'___AUTOLOAD___' => \uniqid('autoload')));
		return \str_replace(\array_keys($replace), \array_values($replace), $this->template);
	}

	protected function formatEntries ($config)
	{
		$funcClosure = function(&$item, $key)
		{
			if ($item instanceof \Closure)
			{
				$item = new \SuperClosure($item);
				$item = $item->getCode();
			}
		};
		
		$funcString = function(&$item, $key)
		{
			$arr = \explode("}'", $item);
			
			if (\count($arr) == 2)
			{
				$arr[0] = \stripslashes($arr[0]);
				$item = \implode("}", $arr);
			}
		};
		
		\array_walk_recursive($config, $funcClosure);
		
		$config = \var_export($config, true);
		
		$arr = \explode("'function", $config);
		\array_walk($arr, $funcString);
		$config = \implode("function", $arr);
		
		return $config;
	}
}