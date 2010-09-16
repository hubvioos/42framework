<?php
/**
 * Copyright (C) 2010 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ModelException extends \Exception { }

class Model
{
	protected $_dataSources = array();
	
	protected $_dataSourcesConfig = array();
	
	public function __construct ()
	{
		if(!empty($this->_dataSourcesConfig))
		{
			foreach($this->_dataSourcesConfig as $name => $class)
			{
				$this->loadDatasource($name, $class);
			}
		}
	}
	
	public static function factory ()
	{
		return new self();
	}
	
	public function __get($key)
	{
		return $this->loadDatasource($key);
		
		throw new ModelException('Unknown datasource.');
	}
	
	protected function loadDatasource($name, $class = null)
	{
		if (!isset($this->_dataSources[$name]))
		{
			if ($class === null)
			{
				throw new ModelException(__METHOD__.' : invalid argument $class');
			}
			$this->_dataSources[$name] = new $class;
		}
		return $this->_dataSources[$name];
	}
}