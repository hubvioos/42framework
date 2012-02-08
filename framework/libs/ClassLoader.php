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
 * 
 * 
 * Inspired by the Doctrine ClassLoader (licensed under the LGPL) : http://www.doctrine-project.org
 */

namespace framework\libs;

class ClassLoader
{
	protected $_namespaces = array();
	protected $_extensions = array();
	protected $_separators = array();
	protected $_defaultPath = null;

	public function __construct ($defaultPath = './')
	{
		$this->setDefaultPath($defaultPath);
	}

	public function register ()
	{
		spl_autoload_register(array($this, 'loadClass'));
	}

	public function unregister ()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	}

	/**
	 * Registers a new top-level namespace to match.
	 *
	 * @param string $namespace The namespace name to add.
	 * @param string $path The path to the namespace (without the namespace name itself).
	 * @param string $extension The namespace file extension.
	 */
	public function addNamespace ($namespace, $path = null, $extension = '.php', $separator = '\\')
	{
		if (isset($this->_namespaces[(string) $namespace]))
		{
			throw new \DomainException('The namespace ' . $namespace . ' is already added.');
		}

		if ($path !== null)
		{
			$length = \strlen($path);
			if ($length == 0 || $path[$length - 1] != \DIRECTORY_SEPARATOR)
			{
				$path .= \DIRECTORY_SEPARATOR;
			}
			$this->_namespaces[(string) $namespace] = $path;
		}
		else
		{
			$this->_namespaces[(string) $namespace] = $this->_defaultPath;
		}

		$this->_extensions[(string) $namespace] = $extension;
		$this->_separators[(string) $namespace] = $separator;
	}

	/**
	 * Checks if the specified top-level namespace is available.
	 *
	 * @param string $namespace The namespace name to check.
	 */
	public function hasNamespace ($namespace)
	{
		return isset($this->_namespaces[(string) $namespace]);
	}

	/**
	 * Removes a registered top-level namespace.
	 *
	 * @param string $namespace The namespace name to remove.
	 */
	public function removeNamespace ($namespace)
	{
		if (!isset($this->_namespaces[(string) $namespace]))
		{
			throw new \DomainException('The namespace ' . $namespace . ' is not available.');
		}
		unset($this->_namespaces[(string) $namespace]);
		unset($this->_extensions[(string) $namespace]);
		unset($this->_separators[(string) $namespace]);
	}

	/**
	 * Sets the default path used by the namespaces. Note that it does not affect
	 * the already added namespaces.
	 *
	 * @param string $defaultPath The new default path.
	 */
	public function setDefaultPath ($defaultPath)
	{
		if ($defaultPath[\strlen($defaultPath) - 1] != \DIRECTORY_SEPARATOR)
		{
			$defaultPath .= \DIRECTORY_SEPARATOR;
		}
		$this->_defaultPath = $defaultPath;
	}

	/**
	 * Returns the default path used by the namespaces.
	 *
	 * @return string The current default path.
	 */
	public function getDefaultPath ()
	{
		return $this->_defaultPath;
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $className The name of the class to load.
	 * @return void
	 */
	public function loadClass ($className)
	{
		foreach ($this->_namespaces as $namespace => $path)
		{
			if (\strpos($className, $namespace) === 0)
			{
				$length = \strlen($namespace.$this->_separators[$namespace]);
				$classFile = \str_replace($this->_separators[$namespace], \DIRECTORY_SEPARATOR, \substr($className, $length));
				
				require $path . $classFile . $this->_extensions[$namespace];
				
				return true;
			}
		}
		return false;
	}

}