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

	// <editor-fold defaultstate="collapsed" desc="attributes">
	/**
	 * The config we want to build
	 * @var array
	 */
	protected $_config = array(
		'modules' => array(),
		'routes' => array(),
		'events' => array(),
		'components' => array()
	);

	/**
	 * The framework's config (to be merged with the application's config)
	 * @var array
	 */
	protected $_frameworkConfig = array();

	/** The application's config (to be merged with the framework's config)
	 * @var array
	 */
	protected $_appConfig = array();
	
	/** The area's config (to be merged with the framework's config)
	 * @var array
	 */
	protected $_areaConfig = array();

	/**
	 * The modules config
	 * @var array 
	 */
	protected $_modulesConfig = array();

	/**
	 * The routes' config
	 * @var arary
	 */
	protected $_routesConfig = array();

	/**
	 * The events' config
	 * @var array 
	 */
	protected $_eventsConfig = array();

	/**
	 * The components' config
	 * @var array 
	 */
	protected $_componentsConfig = array();

	/**
	 * The modules list
	 * @var array
	 */
	protected $_modulesList = array();

	/**
	 * A string-indexed array containing the names of the config variable at each level
	 * Framework config variable, Application config variable, and Module config variable can have different names.
	 *                 - array['framework'] : framework config variable name
	 *                 - array['application'] : application config variable name
	 *                 - array['modules'] : module config variable name
	 * @var array
	 */
	protected $_variablesNames = array(
		'framework' => array('config' => 'config', 'routes' => 'routes', 'events' => 'events', 'components' => 'components'),
		'modules' => array('config' => 'config', 'events' => 'events', 'components' => 'components'),
		'application' => array('config' => 'config', 'routes' => 'routes', 'events' => 'events', 'components' => 'components'),
		'area' => array('config' => 'config', 'routes' => 'routes', 'events' => 'events', 'components' => 'components')
	);

	/**
	 * The only keys that will be used in the arrays passed as parameters
	 * @var array
	 */
	protected static $_allowedKeys = array('framework', 'modules', 'application', 'area');

	// </editor-fold>

	const DEPENDENCIES_SATISFIED = 1;
	const DEPENDENCIES_UNSATISFIED = -1;
	const DEPENDENCIES_SCHRODINGER = 0; // also known as HEADS_OR_TAILS, INCH_ALLAH, GOD_BLESS_U

	/**
	 * Constructor
	 * @param array $configFileName The name of config file WHITHOUT .php extension
	 * @param array $variablesNames A string indexed array containing the names of the config variables at each level
	 */
	public function __construct (array $variablesNames = array())
	{
		// Use parameters
		$this->setVariablesNames($variablesNames);
	}

	// <editor-fold defaultstate="collapsed" desc="getters">

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
	 * Get the area's config
	 * @return array $this->_areaConfig
	 */
	public function getAreaConfig ()
	{
		return $this->_areaConfig;
	}

	/**
	 * Get the modules' config
	 * @return array 
	 */
	public function getModulesConfig ()
	{
		return $this->_modulesConfig;
	}

	/**
	 * Get the routes' config
	 * @return array
	 */
	public function getRoutesConfig ()
	{
		return $this->_routesConfig;
	}

		
	/**
	 * Get the events' config
	 * @return array 
	 */
	public function getEventsConfig ()
	{
		return $this->_eventsConfig;
	}

	/**
	 * Get the components' config
	 * @return array 
	 */
	public function getComponentsConfig ()
	{
		return $this->_componentsConfig;
	}

	/**
	 * Get the modules list, sorted by levels (framework, modules and application)
	 * @return array $_modulesList
	 */
	public function getModulesList ()
	{
		return $this->_modulesList;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 * Set the framework's config
	 * @param array $frameworkConfig The new framework's config
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function setFrameworkConfig (array $frameworkConfig)
	{
		$this->_frameworkConfig = $frameworkConfig;

		return $this->_mergeInternalConfigs();
	}

	/**
	 * Set the application's config
	 * @param array $appConfig The new application's config
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function setAppConfig (array $appConfig)
	{
		$this->_appConfig = $appConfig;

		return $this->_mergeInternalConfigs();
	}
	
	/**
	 * Set the area's config
	 * @param array $areaConfig The new area's config
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function setAreaConfig (array $areaConfig)
	{
		$this->_areaConfig = $areaConfig;

		return $this->_mergeInternalConfigs();
	}

	/**
	 * Set the paths to directories to scan for the modules
	 * The keys of the array can ONLY be chosen among 'framework, 'application' or 'modules'.
	 * Any other key will be IGNORED
	 * @param array $variablesNames
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function setVariablesNames (array $variablesNames)
	{
		$this->_selectiveMerge($this->_variablesNames, $variablesNames);

		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="public methods">

	/**
	 * Build a minimal config based on the framework's and app' config only
	 * @return framework\libs\ConfigBuilder $this 
	 */
	public function buildMinimalConfig ()
	{
		// framework config
		$this->_findAndGetConfig(\FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'config', 'config.php', $this->_variablesNames['framework']['config'], $this->_frameworkConfig);

		// app config
		$this->_findAndGetConfig(\APP_DIR . \DIRECTORY_SEPARATOR . 'config', 'config.php', $this->_variablesNames['application']['config'], $this->_appConfig);
		
		// area config
		$this->_findAndGetConfig(\AREA_DIR . \DIRECTORY_SEPARATOR . 'config', 'config.php', $this->_variablesNames['area']['config'], $this->_areaConfig);

		return $this->_mergeInternalConfigs();
	}

	/**
	 * Merge the config array of each module (if existant) with the $this->_modulesConfig[moduleName] array
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function buildMinimalModulesConfig ()
	{
		// the order IS important
		$this->_findAllModules('modules');

		//$this->_mergeInternalConfigs();

		$this->_getAllModulesConfig('config');

		return $this->_mergeInternalConfigs();
	}

	/**
	 * Build the dependencies among the modules
	 * NOTE : this should be called after ConfigBuilder::buildMinimalModulesConfig()
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function buildModulesDependencies ()
	{
		foreach ($this->_modulesConfig as $moduleName => $options)
		{
			$this->_modulesConfig[$moduleName]['dependenciesSatisfied'] = $this->_checkModuleDependencies($options);
		}

		return $this->_mergeInternalConfigs();
	}

	/**
	 * Build the routes' config
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function buildRouteConfig ()
	{
		// framework routes
		$this->_findAndGetConfig(\FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'config', 'routes.php', $this->_variablesNames['framework']['routes'], $this->_routesConfig);

		// app routes
		$this->_findAndGetConfig(\APP_DIR . \DIRECTORY_SEPARATOR . 'config', 'routes.php', $this->_variablesNames['application']['routes'], $this->_routesConfig);
		
		// area routes
		$this->_findAndGetConfig(\AREA_DIR . \DIRECTORY_SEPARATOR . 'config', 'routes.php', $this->_variablesNames['area']['routes'], $this->_routesConfig);
		
		// modules routes
		$this->_getAllModulesConfig('routes', $this->_routesConfig);
		
		return $this->_mergeInternalConfigs();
	}

	/**
	 * Buil the events' config
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function buildEventsConfig ()
	{
		$this->_findAndGetConfig(\FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'config', 'events.php', $this->_variablesNames['framework']['events'], $this->_eventsConfig);

		$this->_findAndGetConfig(\APP_DIR . \DIRECTORY_SEPARATOR . 'config', 'events.php', $this->_variablesNames['application']['events'], $this->_eventsConfig);
		
		$this->_findAndGetConfig(\AREA_DIR . \DIRECTORY_SEPARATOR . 'config', 'events.php', $this->_variablesNames['area']['events'], $this->_eventsConfig);

		$this->_getAllModulesConfig('events', $this->_eventsConfig);
		
		//$this->_mergeInternalConfigs();
		
		foreach($this->_eventsConfig as $key => $event)
		{
			//Sort the listeners table by priority
			usort($this->_eventsConfig[$key], array($this, 'compareListenersByPriority')); 
		}
	
		return $this->_mergeInternalConfigs();
	}
	
	/**
	 * Compare two listeners according their priority level
	 * @param \framework\libs\Registry $a 
	 * @param \framework\libs\Registry $b
	 * @return int - The result of the compraison
	 */
	private function compareListenersByPriority ($a, $b)
	{
		//Check if the two listeners have the same priority
		if($a['priority'] === $b['priority'])
		{
			return -1;
		}
		
		return ($a['priority'] < $b['priority']) ? -1 : 1;
	}

	/**
	 * Buil the components' config
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function buildComponentsConfig ()
	{
		$this->_findAndGetConfig(\FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'config', 'components.php', $this->_variablesNames['framework']['components'], $this->_componentsConfig);

		$this->_findAndGetConfig(\APP_DIR . \DIRECTORY_SEPARATOR . 'config', 'components.php', $this->_variablesNames['application']['components'], $this->_componentsConfig);

		$modulesComponents = array();
		
		foreach ($this->_modulesList as $moduleName)
		{
			$this->_findAndGetConfig(
					\MODULES_DIR . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'config', 'components.php', $this->_variablesNames['modules']['components'], $modulesComponents, true, $moduleName);
		}

		foreach ($modulesComponents as $module => $components)
		{
			foreach ($components as $componentName => $componentData)
				$this->_componentsConfig[$module . '.' . $componentName] = $componentData;
		}
		
		return $this->_mergeInternalConfigs();
	}
	

	/**
	 * Build the full config at once
	 * i.e. build the modules list, build the modules' initial configs and dependencies
	 * merge the application and framework config and set into $_config
	 * @return framework\libs\ConfigBuilder $this
	 */
	public function buildConfig ()
	{	
		return $this->buildMinimalConfig()
						->buildMinimalModulesConfig()
						->buildModulesDependencies()
						->buildComponentsConfig()
						->buildRouteConfig()
						->buildEventsConfig();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="private methods">

	/**
	 * Put all the configs together into $_config
	 * @return framework\libs\ConfigBuilder $this
	 */
	private function _mergeInternalConfigs ()
	{
		// merge framework and application's configs for generic options
		// in case of duplicate options, application's config overrides framework's config
		$this->_config = \array_merge($this->_frameworkConfig, 
								$this->_appConfig, $this->_areaConfig, array('modules' => $this->_modulesConfig), 
								array('events' => $this->_eventsConfig), 
								array('components' => $this->_componentsConfig), 
								array('routes' => $this->_routesConfig));

		return $this;
	}

	/**
	 * Merge an 'external' array (i.e. from the user) with an 'internal' array (i.e. an instance variable)
	 * @param array $internal
	 * @param array $external
	 * @return framework\libs\ConfigBuilder $this
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

	/**
	 * Find a config file, extract the config it contains and append/merge it to/with an internal var
	 * @param string $directory The path to the directory we want to scan
	 * @param string $filename The config filename
	 * @param string $externalVarName The var we want to retrieve from the config file
	 * @param array $internal The internal var to wich we want to add the config options
	 * @param boolean $useKey Whether or not the retrieved infos should be put at a specified key
	 * @param string|number $key The key (will be ignored if $useKey === false)
	 * @return framework\libs\ConfigBuilder $this
	 */
	private function _findAndGetConfig ($directory, $filename, $externalVarName, &$internal, $useKey = false, $key = null)
	{
		unset(${$externalVarName});

		if (\file_exists($directory . \DIRECTORY_SEPARATOR . $filename))
		{
			include($directory . \DIRECTORY_SEPARATOR . $filename);

			if (isset(${$externalVarName}))
			{
				// if the retrieved var has to use a particular key
				if ($useKey === true && !\is_null($key))
				{
					if (isset($internal[$key]))
					{
						$internal[$key] = array_merge($internal[$key], ${$externalVarName});
					}
					else
					{
						$internal[$key] = ${$externalVarName};
					}
				}
				else
				{
					if ($internal === null)
					{
						$internal = ${$externalVarName};
					}
					else
					{
						$internal = \array_merge($internal, ${$externalVarName});
					}
					
				}
			}
		}

		return $this;
	}

	/**
	 * Find all the modules and build $this->_modulesList
	 * @return framework\libs\ConfigBuilder $this
	 */
	private function _findAllModules ()
	{
		$directoryIterator = new \DirectoryIterator(\MODULES_DIR);

		// scan the directory and add every module it contains to the modules list
		// except ./ and ../
		foreach ($directoryIterator as $file)
		{
			$moduleName = rtrim($file->getFilename(), '/\\');
			if ($file->isDir() and !$file->isDot())
			{
				$this->_modulesConfig[$moduleName] = array();
				$this->_modulesList[] = $moduleName;
			}
		}

		return $this;
	}

	/**
	 * Get the minimal modules' configs (i.e. without the dependency checking)
	 * of each module
	 */
	private function _getAllModulesConfig ($configType = 'config', &$internal = null)
	{
		$directory = \MODULES_DIR . \DIRECTORY_SEPARATOR;
		$configFile = \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . $configType . '.php';

		foreach ($this->_modulesList as $moduleName)
		{
			$pathToConfigFile = $directory . $moduleName . $configFile;

			// unset a variable that could have been included before
			unset(${$this->_variablesNames['modules'][$configType]});

			// get the config var in the config file for each module, if existant
			if (file_exists($pathToConfigFile))
			{
				require_once($pathToConfigFile);

				if (isset(${$this->_variablesNames['modules'][$configType]}))
				{
					if ($internal !== null)
					{
						$internal = array_merge($internal, ${$this->_variablesNames['modules'][$configType]});
					}
					// if the module has already some config options from another level
					// merge the new ones with them
					else if (isset($this->_modulesConfig[$moduleName]))
					{
						$this->_modulesConfig[$moduleName] = array_merge($this->_modulesConfig[$moduleName], ${$this->_variablesNames['modules'][$configType]});
					}
					else
					{
						$this->_modulesConfig[$moduleName] = array();
					}
				}
			}
			// or set an empty array
			else
			{
				if (!isset($this->_modulesConfig[$moduleName]))
				{
					$this->_modulesConfig[$moduleName] = array();
				}
			}
		}

		return $this;
	}

	/**
	 * Recursively check if a module's dependencies are satisfied
	 * @TODO : log check results ?
	 * @param array $options The module's configuration options
	 * @return boolean Whether or not the dependencies are satified
	 */
	private function _checkModuleDependencies (array $options = array())
	{
		if (isset($options['dependencies']))
		{
			foreach ($options['dependencies'] as $dependency => $minimalVersion)
			{
				// if the dependency isn't installed
				if (!isset($this->_modulesConfig[$dependency]))
				{
					return self::DEPENDENCIES_UNSATISFIED;
				}

				// if the dependency has already been marked as unsatisfied or SCHRODINGER
				if (isset($this->_modulesConfig[$dependency]['dependenciesSatisfied'])
						&& $this->_modulesConfig[$dependency]['dependenciesSatisfied'] != self::DEPENDENCIES_SATISFIED)
				{
					return $this->_modulesConfig[$dependency]['dependenciesSatisfied'];
				}

				// if no version is specified for the installed version, INCH ALLAH
				if (!isset($this->_modulesConfig[$dependency]['version']))
				{
					return self::DEPENDENCIES_SCHRODINGER;
				}

				// if the installed version of the dependency is outdated
				if ($this->_modulesConfig[$dependency]['version'] < $minimalVersion)
				{
					return self::DEPENDENCIES_UNSATISFIED;
				}

				// if the dependency has dependencies, check them (and so on...)
				// FUCK YEAH recursivity !
				if (isset($this->_modulesConfig[$dependency]['dependencies']))
				{
					return $this->_checkModuleDependencies($this->_modulesConfig[$dependency]);
				}
			}
		}

		// if everything went fine or if no dependency is necessary, well...
		return self::DEPENDENCIES_SATISFIED;
	}

	// </editor-fold>
	
	
	
	
}
