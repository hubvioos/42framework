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

namespace framework\core;

class View extends \framework\core\FrameworkObject
{

	protected $_file;
	protected $_format = null;
	protected $_vars = array();
	protected $_renderedView = null;
	protected static $_globalsVars = array();

	public function __construct ($module, $file, $vars = false, $format = null)
	{
		$viewExtension = $this->getConfig('viewExtension');

		$this->_format = $format;

		$this->_file = $this->getComponent('dispatcher')->getModulePath($module);

		if ($this->_format !== null)
		{
			$this->_file .= \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . $this->_format . \DIRECTORY_SEPARATOR . $file . $viewExtension;
		}
		else
		{
			$this->_file .= \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . $file . $viewExtension;
		}

		if (!\file_exists($this->_file))
		{
			if ($this->_format !== null)
			{
				$globalFile = \APP_DIR . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . $this->_format . \DIRECTORY_SEPARATOR . $file . $viewExtension;
			}
			else
			{
				$globalFile = \APP_DIR . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . $file . $viewExtension;
			}

			if (\file_exists($globalFile))
			{
				$this->_file = $globalFile;
			}
			else
			{
				throw new \RuntimeException('View not found : ' . $this->_file);
			}
		}

		if ($vars !== false)
		{
			$this->_vars = $vars;
		}
	}

	public function __set ($name, $value)
	{
		$this->_vars[$name] = $value;
	}

	public function __get ($name)
	{
		if (!isset($this->_vars[$name]))
		{
			return null;
		}
		return $this->_vars[$name];
	}

	public static function setGlobal ($name, $value)
	{
		self::$_globalsVars[$name] = $value;
	}

	public static function getGlobal ($name)
	{
		if (!isset(self::$_globalsVars[$name]))
		{
			return null;
		}
		return self::$_globalsVars[$name];
	}

	public function render ()
	{
		if ($this->_renderedView === null)
		{
			try
			{
				\extract(self::$_globalsVars);
				\extract($this->_vars);

				\ob_start();
				include $this->_file;
				$this->_renderedView = \ob_get_clean();
			}
			catch (\Exception $e)
			{
				\ob_end_clean();
				$this->_renderedView = $e->__toString();
			}
		}
		return $this->_renderedView;
	}

	public function getRenderedView ()
	{
		return $this->_renderedView;
	}

	public function setRenderedView ($view)
	{
		$this->_renderedView = $view;
		return $this;
	}

	public function __toString ()
	{
		return $this->render();
	}

	public function getLink ($routeName = null, $params = array())
	{
		return $this->getConfig('siteUrl') . \urldecode($this->getComponent('router')->url($params, $routeName));
	}

	public function getBlock ($block, $params = array(), $format = null)
	{
		list($module, $action) = \explode('/', $block);

		return $this->createView($module, $action, $params, $format);
	}

	public function setTitle ($title)
	{
		self::setGlobal('title', $title);
	}

	public function getHelper ()
	{
		return $this->getComponent(func_get_args());
	}

}
