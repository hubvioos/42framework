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
	 * The config we want to build
	 * @var array
	 */
	protected $_config = array();

	/**
	 * The framework's config (to be merged with the application's config)
	 * @var array 
	 */
	protected $_frameworkConfig = array();

	/** The application's config (to be merged with the framework's config)
	 * @var array 
	 */
	protected $_appConfig = array();

	/**
	 * The modules list
	 * @var array
	 */
	protected $_modulesList = array();

	/**
	 * DirectoryIterator
	 * @var \DirectoryIterator
	 */
	protected $_directoryIterator = null;

	/**
	 * The path to directory containing the modules we want to include in the config
	 * @var string
	 */
	protected $_modulesDirectory = \MODULES_DIR;

	/**
	 * The name of the config file (without extension .php)
	 * @var string
	 */
	protected $_configFileName = 'config';

	/**
	 * The names of the variable
	 * Framework config variable, Application config variable, and Module config variable can have different names.
	 * 		- array[0] : framework config variable name
	 *		- array[1] : application config variable name
	 *		- array[2] : module config variable name
	 * @var array
	 */
	protected $_variablesNames = array('framework' => 'frameworkConfig',
														   'app' => 'appConfig',
														   'module' => 'config');

	const DEPENDENCIES_SATISFIED = 1;
	const DEPENDENCIES_UNSATISFIED = -1;
	const DEPENDENCIES_SCHRODINGER = 0; // also known as HEADS_OR_TAILS, INCH_ALLAH, GOD_BLESS_U

	/**
	 * Constructor
	 * Can take the framework's and app's configs as argument
	 * @param array $configFileName - The name of config file (whithout .php extension)
	 * @param array $appConfig - The
	 */
	public function __construct ($configFileName = 'config', $variablesNames = array('framework' => 'frameworkConfig',
																															  'app' => 'appConfig',
																															  'module' => 'config'))
	{

		//Set file and variables name
		$this->_configFileName = $configFileName;
		$this->_variablesNames = $variablesNames;
		
		//Include config files of the framework and the application config file
		include FRAMEWORK_DIR.DS.'config'.DS.$this->_configFileName.'.php';
		include APP_DIR.DS.'config'.DS.$this->_configFileName.'.php';

		//Set framework & application config
		$this->_frameworkConfig = ${$this->_variablesNames['framework']};
		$this->_appConfig = ${$this->_variablesNames['app']};

		$this->_config['modules'] = array();
		$this->_mergeInternalConfigs();
	}

	
	/**
	 * Get the computed configuration
	 * @return array $_config
	 */
	public function getConfig ()
	{
		return $this->_config;
	}
	
	/**
	 * Get the framework's config
	 * @return array $this->_frameworkConfig; 
	 */
	public function getFrameworkConfig ()
	{
		return $this->_frameworkConfig;
	}
	
	/**
	 * Get the application's config
	 * @return array $this->_appConfig
	 */
	public function getAppConfig ()
	{
		return $this->_appConfig;
	}

	/**
	 * Get the modules list
	 * @return array $_modulesList
	 */
	public function getModulesList ()
	{
		return $this->_modulesList;
	}
	
	/**
	 * Get the directory path to scan for the modules
	 * @return string 
	 */
	public function getModulesDirectory ()
	{
		return $this->_modulesDirectory;
	}

		
	
	
	/**
	 * Set the framework's config
	 * @param array $frameworkConfig The new framework's config
	 * @return ConfigBuilder $this
	 */
	public function setFrameworkConfig (array $frameworkConfig)
	{
		$this->_frameworkConfig = $frameworkConfig;

		return $this->_mergeInternalConfigs();
	}
	
	/**
	 * Set the application's config
	 * @param array $appConfig The new application's config
	 * @return ConfigBuilder $this
	 */
	public function setAppConfig (array $appConfig)
	{
		$this->_appConfig = $appConfig;

		return $this->_mergeInternalConfigs();
	}

	/**
	 * Set the path to directory to scan for the modules
	 * @param string $_modulesDirectory
	 * @return ConfigBuilder 
	 */
	public function setModulesDirectory ($modulesDirectory)
	{
		$this->_modulesDirectory = rtrim($modulesDirectory, '/\\');
		
		return $this;
	}

	/**
	 * Build the modules list into $_config['modules']
	 * @param string $modulesDirectory The directory to scan for the modules
	 * @return ClassBuilder $this
	 */
	public function buildModulesList ($modulesDirectory = '')
	{
		if($modulesDirectory !== '')
		{
			$this->setModulesDirectory($modulesDirectory);
		}
		
		$this->_directoryIterator = new \DirectoryIterator($this->_modulesDirectory);

		// scan the directory and add every module it contains to the modules list
		// except ./ and ../
		foreach ($this->_directoryIterator as $file)
		{
			$moduleName = rtrim($file->getFilename(), '/\\');
			if ($file->isDir() and !$file->isDot())
			{
				$this->_config['modules'][$moduleName] = array();
				$this->_modulesList[] = $moduleName;
			}
		}
		return $this;
	}

	/**
	 * Merge the $config array of each module (if existant) with the $_config['modules'][moduleName] array
	 * @return ClassBuilder $this
	 */
	public function buildInitModulesConfig ()
	{

		foreach ($this->_modulesList as $moduleName)
		{		
			$pathToConfigFile = $this->_modulesDirectory . DIRECTORY_SEPARATOR . $moduleName
					. DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $this->_configFileName . '.php';

			// get the $config var in the config/config.php file for each module, if existant
			if (file_exists($pathToConfigFile))
			{ 
				require_once($pathToConfigFile);
				if (isset($$varName))
				{
					$this->_config['modules'][$moduleName] = ${$this->_variablesNames['module']};
				}
				unset(${$this->_variablesNames['module']});
			}
			// or set an empty array
			else
			{
				$this->_config['modules'][$moduleName] = array();
			}
		}

		return $this;
	}

	/**
	 * Build the dependencies among the modules
	 * @return ClassBuilder $this 
	 */
	public function buildModulesDependencies ()
	{
		foreach ($this->_config['modules'] as $moduleName => $options)
		{
			$this->_config['modules'][$moduleName]['dependenciesSatisfied'] = $this->_checkModuleDependencies($options);
		}

		return $this;
	}


	/**
	 * Build the full config at once
	 * i.e. build the modules list, build the modules' initial configs and dependencies
	 * merge the application and framework config and set into $_config
	 * @return ClassBuilder $this
	 */
	public function buildConfig ()
	{
		return $this->buildModulesList()
						->buildInitModulesConfig()
						->buildModulesDependencies()
						->_mergeInternalConfigs();
	}

	
	/**
	 * Recursively check if a module's dependencies are satisfied
	 * @TODO : log check results ?
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
					return self::DEPENDENCIES_UNSATISFIED;
				}

				// if the dependency has already been marked as unsatisfied or SCHRODINGER
				if (isset($this->_config['modules'][$dependency]['dependenciesSatisfied'])
						&& $this->_config['modules'][$dependency]['dependenciesSatisfied'] != self::DEPENDENCIES_SATISFIED)
				{
					return $this->_config['modules'][$dependency]['dependenciesSatisfied'];
				}

				// if no version is specified for the installed version, INCH ALLAH
				if (!isset($this->_config['modules'][$dependency]['version']))
				{
					return self::DEPENDENCIES_SCHRODINGER;
				}

				// if the installed version of the dependency is outdated
				if ($this->_config['modules'][$dependency]['version'] < $minimalVersion)
				{
					return self::DEPENDENCIES_UNSATISFIED;
				}

				// if the dependency has dependencies, check them (and so on...)
				// FUCK YEAH recursivity !
				if (isset($this->_config['modules'][$dependency]['dependencies']))
				{
					return $this->_checkModuleDependencies($this->_config['modules'][$dependency]);
				}
			}
		}

		// if everything went fine or if no dependency is necessary, well...
		return self::DEPENDENCIES_SATISFIED;
	}
	
	/**
	 * Put all the configs together into $_config
	 * @return ConfigBuilder 
	 */
	private function _mergeInternalConfigs ()
	{
		// save the modules config if already existant
		$modulesConfig = $this->_config['modules'];

		// merge framework and application's configs for generic options 
		// in case of duplicate options, application's config overrides framework's config
		$this->_config = array_merge($this->_frameworkConfig, $this->_appConfig);
		// add modules config for module specific options
		$this->_config['modules'] = $modulesConfig;

		return $this;
	}

}