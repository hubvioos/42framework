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
	 * An array containing the paths to the directories where the modules are stored.
	 * The array must contain absolute paths
	 * @var array
	 */
	protected $_modulesDirectories = array();

	/**
	 * The name of the config file (without extension .php)
	 * @var string
	 */
	protected $_configFileName = 'config';

	/**
	 * A string-indexed array containing the names of the config variable at each level
	 * Framework config variable, Application config variable, and Module config variable can have different names.
	 * 		- array['framework'] : framework config variable name
	 * 		- array['application'] : application config variable name
	 * 		- array['modules'] : module config variable name
	 * @var array
	 */
	protected $_variablesNames = array(
		'framework' => 'frameworkConfig',
		'application' => 'appConfig',
		'modules' => 'config'
	);

	/**
	 * The only keys that will be used in the arrays passed as parameters
	 * @var array 
	 */
	protected static $_allowedKeys = array('framework', 'application', 'modules');

	const DEPENDENCIES_SATISFIED = 1;
	const DEPENDENCIES_UNSATISFIED = -1;
	const DEPENDENCIES_SCHRODINGER = 0; // also known as HEALDS_OR_TAILS, INCH_ALLAH, GOD_BLESS_U

	/**
	 * Constructor
	 * @param array $configFileName The name of config file WHITHOUT .php extension
	 * @param array $variablesNames A string indexed array containing the names of the config variables at each level
	 */
	public function __construct ($configFileName = 'config', array $variablesNames =
	array('framework' => 'frameworkConfig', 'application' => 'appConfig', 'modules' => 'config'))
	{
		//Set file and variables name
		$this->_configFileName = $configFileName;
		$this->_selectiveMerge($this->_variablesNames, $variablesNames);

		// 3 levels here for the modules: 
		// "framework" overriden by "modules" overriden by "application"
		$this->_modulesDirectories['framework'] = \FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'modules';
		$this->_modulesDirectories['modules'] = \MODULES_DIR;
		$this->_modulesDirectories['application'] = \APP_DIR . \DIRECTORY_SEPARATOR . 'modules';

		// Get framework's & application's config
		include FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . $this->_configFileName . '.php';
		include APP_DIR . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . $this->_configFileName . '.php';

		$this->_frameworkConfig = ${$this->_variablesNames['framework']};
		$this->_appConfig = ${$this->_variablesNames['application']};

		$this->_config['modules'] = array();
		$this->_config['modulesLocation'] = array();
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
	 * Get the modules list, sorted by levels (framework, modules and application)
	 * @return array $_modulesList
	 */
	public function getModulesList ()
	{
		return $this->_modulesList;
	}

	/**
	 * Get the directories path to scan for the modules
	 * @return string 
	 */
	public function getModulesDirectories ()
	{
		return $this->_modulesDirectories;
	}

	/**
	 * Get the modules' config filename
	 * @return string 
	 */
	public function getConfigFileName ()
	{
		return $this->_configFileName;
	}

	/**
	 * Get the config variables' names at each level
	 * @return array 
	 */
	public function getVariablesNames ()
	{
		return $this->_variablesNames;
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
	 * Set the paths to directories to scan for the modules
	 * The keys of the array can ONLY be chosen among 'framework, 'application' or 'modules'.
	 * Any other key will be IGNORED.
	 * The directories should not end with a trailing slash or back-slash
	 * @param array $_modulesDirectory A string-indexed array where the keys represent the level of the module
	 * @return ConfigBuilder $this
	 */
	public function setModulesDirectories (array $modulesDirectories)
	{
		array_map(
				function($directoryPath)
				{
					return rtrim($directoryPath, '/\\ \t');
				}, $modulesDirectories);

		$this->_selectiveMerge($this->_modulesDirectories, $modulesDirectories);

		return $this;
	}

	/**
	 * Set the modules' config filename.
	 * @param string $_configFileName The filename, WITHOUT the '.php' extension
	 */
	public function setConfigFileName ($_configFileName)
	{
		$this->_configFileName = rtrim($_configFileName, '\.php/\\ \t');
	}

	/**
	 * Set the paths to directories to scan for the modules
	 * The keys of the array can ONLY be chosen among 'framework, 'application' or 'modules'.
	 * Any other key will be IGNORED
	 * @param array $variablesNames 
	 * @return ConfigBuilder $this
	 */
	public function setVariablesNames (array $variablesNames)
	{
		$this->_selectiveMege($this->_variablesNames, $variablesNames);

		return $this;
	}

	/**
	 * Build the modules list into $_config['modules']
	 * @param string $modulesDirectories The directory to scan for the modules
	 * @return ConfigBuilder $this
	 */
	public function buildModulesList (array $modulesDirectories = array())
	{
		if (!empty($modulesDirectories))
		{
			$this->setModulesDirectories($modulesDirectories);
		}

		// levels are framework / application / module
		foreach ($this->_modulesDirectories as $level => $moduleDirectory)
		{
			$this->_directoryIterator = new \DirectoryIterator($moduleDirectory);

			// scan the directory and add every module it contains to the modules list
			// except ./ and ../
			foreach ($this->_directoryIterator as $file)
			{
				$moduleName = rtrim($file->getFilename(), '/\\');
				if ($file->isDir() and !$file->isDot())
				{
					$this->_config['modules'][$moduleName] = array();
					$this->_modulesList[$level][] = $moduleName;
				}
			}
		}

		return $this;
	}

	/**
	 * Merge the config array of each module (if existant) with the $_config['modules'][moduleName] array
	 * @return ConfigBuilder $this
	 */
	public function buildInitModulesConfig ()
	{
		// the order IS important
		$this->_getAllModulesConfigFromLevel('framework')
				->_getAllModulesConfigFromLevel('modules')
				->_getAllModulesConfigFromLevel('application');

		return $this;
	}

	/**
	 * Build the dependencies among the modules
	 * @return ConfigBuilder $this 
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
	 * Build the $_config[modulesLocation] annuary of the config to locate every module
	 * @return ConfigBuilder $this
	 */
	public function buildModulesLocation ()
	{
		$this->_locateModulesFromLevel('framework')
				->_locateModulesFromLevel('modules')
				->_locateModulesFromLevel('application');

		return $this;
	}

	/**
	 * Build the full config at once
	 * i.e. build the modules list, build the modules' initial configs and dependencies
	 * merge the application and framework config and set into $_config
	 * @return ConfigBuilder $this
	 */
	public function buildConfig ()
	{
		return $this->buildModulesList()
						->buildInitModulesConfig()
						->buildModulesDependencies()
						->buildModulesLocation()
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

	private function _locateModulesFromLevel ($level = 'application')
	{
		foreach ($this->_modulesList[$level] as $moduleName)
		{
			$this->_config['modulesLocation'][$moduleName] = $level;
		}

		return $this;
	}

	/**
	 * Get the initial config (i.e. without the dependency checking)
	 * of each module of a given level
	 * @param string $level
	 */
	private function _getAllModulesConfigFromLevel ($level = 'application')
	{
		$directory = $this->_modulesDirectories[$level] . \DIRECTORY_SEPARATOR;
		$configFile = \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . $this->_configFileName . '.php';

		foreach ($this->_modulesList[$level] as $moduleName)
		{
			$pathToConfigFile = $directory . $moduleName . $configFile;

			// unset a variable that could have been included before
			unset(${$this->_variablesNames['modules']});

			// get the config var in the config file for each module, if existant
			if (file_exists($pathToConfigFile))
			{
				require_once($pathToConfigFile);

				if (isset(${$this->_variablesNames['modules']}))
				{
					// if the module has already some config options from another level
					// merge the new ones with them
					if (isset($this->_config['modules'][$moduleName]))
					{
						$this->_config['modules'][$moduleName] = array_merge($this->_config['modules'][$moduleName], ${$this->_variablesNames['modules']});
					}
					else
					{
						$this->_config['modules'][$moduleName] = ${$this->_variablesNames['modules']};
					}
				}
			}
			// or set an empty array
			else
			{
				if (!isset($this->_config['modules'][$moduleName]))
				{
					$this->_config['modules'][$moduleName] = array();
				}
			}
		}

		return $this;
	}

	/**
	 * Put all the configs together into $_config
	 * @return ConfigBuilder $this
	 */
	private function _mergeInternalConfigs ()
	{
		// save the modules config if already existant
		$modulesConfig = $this->_config['modules'];
		$modulesLocation = $this->_config['modulesLocation'];

		// merge framework and application's configs for generic options 
		// in case of duplicate options, application's config overrides framework's config
		$this->_config = array_merge($this->_frameworkConfig, $this->_appConfig);
		// add modules config for module specific options
		$this->_config['modules'] = $modulesConfig;
		$this->_config['modulesLocation'] = $modulesLocation;

		return $this;
	}

	/**
	 * Merge an 'external' array (i.e. from the user) with an 'internal' array (i.e. an instance variable)
	 * @param array $internal
	 * @param array $external
	 * @return ConfigBuilder $this
	 */
	private function _selectiveMerge (array &$internal, array $external)
	{
		// replace the value in the internal array 
		// only if the key is among the allowed keys
		foreach ($external as $key => $value)
		{
			if (\in_array($key, self::$_allowedKeys))
				$internal[$key] = $value;
		}
	}

}