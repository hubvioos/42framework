<?php

/**
 * Copyright (C) 2011 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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
/**
 * Library ConfigBuilder
 *
 * @author mickael
 */

namespace framework\libs;

class ConfigBuilder
{

	/**
	 * The configuration options
	 * @var array 
	 */
	protected $_config = array();

	/**
	 * Constructor
	 * @param array $frameworkConfig
	 * @param array $appConfig 
	 */
	public function __construct (array $frameworkConfig = array(), array $appConfig = array())
	{
		// merge the framework and app configs
		$this->_config = \array_merge($frameworkConfig, $appConfig);

		// @TODO : remove on file tree modification
		$this->_config['modules'] = array(
			'website' => array('dependenciesSatisfied' => true), 
			'cli'=> array('dependenciesSatisfied' => true), 
			'errors' => array('dependenciesSatisfied' => true)
		);
		
		/* @var $scanner \TheSeer\Tools\DirectoryScanner */
		$scanner = new \TheSeer\Tools\DirectoryScanner;

		// don't scan controllers, models & views folders
		$excludes = array('*/controllers/*', '*/models/*', '*/views/*');
		$scanner->setExcludes($excludes);
		// search for files named config/config.php
		$includes = array('*/config/config.php');
		$scanner->setIncludes($includes);

		// scan the modules' directory
		foreach ($scanner(\MODULES_DIR, true) as $file)
		{
			$config = array();
			include $file->getPathName();
			
			if(isset($config) && \is_array($config))
			{
				$name = array();
				// get the module's name
				\preg_match('#^' . \MODULES_DIR . '/(\w*)/config/#', $file->getPathName(), $name);

				if (\count($name) === 2)
				{
					// put the config options for module foo in $_config['modules']['foo']
					$this->_config['modules'][$name[1]] = $config;
				}
			}
			unset($config);
		}
	}

	/**
	 * Get the computed config
	 * @return array 
	 */
	public function getConfig ()
	{
		return $this->_config;
	}

	public function checkDependencies ()
	{
		if (isset($this->_config['modules']) && \is_array($this->_config['modules']))
		{
			foreach ($this->_config['modules'] as $moduleName => $options)
			{
				$this->_config['modules'][$moduleName]['dependenciesSatisfied'] = $this->_checkModuleDependencies($options);
			}
		}
	}

	/**
	 * Recursively check if a module's dependencies are satisfied
	 * @param array $options The module's configuration options
	 * @return boolean Wether or not the dependencies are satified
	 */
	private function _checkModuleDependencies (array $options = array())
	{
		if (isset($options['dependencies']))
		{
			foreach ($options['dependencies'] as $dependency => $minimalVersion)
			{
				// if the dependency isn't installed
				if (!isset($this->_config['modules'][$dependency]))
				{
					return false;
				}

				// if the dependency has already been checked
				if (isset($this->_config['modules'][$dependency]['dependenciesSatisfied']))
				{
					if ($this->_config['modules'][$dependency]['dependenciesSatisfied'] === false)
					{
						return false;
					}
					else
					{
						continue;
					}
				}

				// if the installed version of the dependency is outdated
				if ($this->_config['modules'][$dependency]['version'] < $minimalVersion)
				{
					return false;
				}

				// if the dependency has dependencies, check them (and so on...)
				// FUCK YEAH recursivity !
				if (isset($this->_config['modules'][$dependency]['dependencies']))
				{
					return $this->_checkModuleDependencies($this->_config['modules'][$dependency]);
				}
			}
		}

		// if everything went fine, well...
		return true;
	}

}