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
    public function __construct($frameworkConfig = array(), $appConfig = array())
    {
        // merge the framework and app configs
        $this->_config = \array_merge($frameworkConfig, $appConfig);
        
        $scanner = new \TheSeer\Tools\DirectoryScanner;
        
        // don't scan controllers, models & views folders
        $excludes = array('*/controllers/*', '*/models/*', '*/views/*');
        $scanner->setExcludes($excludes);
        // search for files named config/config.php
        $includes = array('*/config/config.php');
        $scanner->setIncludes($includes);
        
        // scan the modules' directory
        foreach($scanner(\MODULES_DIR, true) as $file)
        {
            include $file->getPathName();
            
            // get the module's name
            \preg_match('#'.\MODULES_DIR.'/(\w*)/config/#', $file->getPathName(), $name);
            
            // put the config options for module foo in $_config['modules'][foo]
            $this->_config['modules'][$name[1]] = $config;
        }
    }
    
    /**
     * Get the computed config
     * @return array 
     */
    public function getConfig()
    {
        return $this->_config;
    }
}