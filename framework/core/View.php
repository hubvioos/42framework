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
	protected $_extension = '.php';
	protected $_format = 'html';
	protected $_vars = array();
	protected $_renderedView = null;
	protected static $_globalsVars = array();

	public function __construct ($vars = false)
	{
		if ($vars !== false)
		{
			$this->_vars = $vars;
		}
	}
	
	public function getExtension ()
	{
		return $this->_extension;
	}

	public function setExtension ($extension)
	{
		$this->_extension = $extension;
	}

	public function getFormat ()
	{
		return $this->_format;
	}

	public function setFormat ($format)
	{
		$this->_format = $format;
	}
	
	public function setFile ($module, $file)
	{
		$this->_extension = $this->getConfig('viewExtension');

		$this->_file = $this->getComponent('dispatcher')->getViewPath($module, $file, $this->_extension, $this->_format);

		if (!$this->_file)
		{
			if ($this->_format !== null)
			{
				$areaFile = \AREA_DIR . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . $this->_format . \DIRECTORY_SEPARATOR . $file . $this->_extension;
			}
			else
			{
				$areaFile = \AREA_DIR . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . $file . $this->_extension;
			}

			if (\file_exists($areaFile))
			{
				$this->_file = $areaFile;
			}
			else
			{
				if ($this->_format !== null)
				{
					$globalFile = \APP_DIR . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . $this->_format . \DIRECTORY_SEPARATOR . $file . $this->_extension;
				}
				else
				{
					$globalFile = \APP_DIR . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . $file . $this->_extension;
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

	public function getBlock ($module, $file, $params = array(), $format = 'html', $alternativeView = null)
	{
		$arr = array(
			'module' => $module,
			'file' => $file,
			'class' => $alternativeView
		);

		return $this->createView($arr, $params, $format);
	}

	public function setTitle ($title)
	{
		self::setGlobal('title', $title);
	}

	public function addCss ($path, $priority = 50, $place = 'head')
	{
		$this->_setResource('css', $path, $priority, $place);
		return $this;
	}

	public function addJs ($path, $priority = 50, $place = 'head')
	{
		$this->_setResource('js', $path, $priority, $place);
		return $this;
	}

	protected function _setResource ($type, $path, $priority, $place)
	{
		$resources = self::getGlobal('resources');

		if ($resources === null)
		{
			$resources = array(
				'css' => array(),
				'js' => array()
			);
		}

		if (!isset($resources[$type][$place]))
		{
			$resources[$type][$place] = array();
		}

		if (!isset($resources[$type][$place][$priority]))
		{
			$resources[$type][$place][$priority] = array();
		}

		$resources[$type][$place][$priority][] = $path;

		self::setGlobal('resources', $resources);
	}

	public function displayResources ($type, $place)
	{
		$resources = self::getGlobal('resources');

		if (!isset($resources[$type][$place]))
		{
			$resources = array();
		}
		else
		{
			$resources = $resources[$type][$place];
		}

		\ksort($resources);

		$sortedResources = array();

		foreach ($resources as $res)
		{
			foreach ($res as $r)
			{
				array_push($sortedResources, $r);
			}
		}

		switch ($type)
		{
			case 'css':
				return $this->_displayCss($place, $sortedResources);
				break;
			case 'js':
				return $this->_displayJs($place, $sortedResources);
				break;
			default:
				return '';
		}
	}

	protected function _displayCss ($place, $resources)
	{
		$css = '';

		if ($place == 'style')
		{
			$css .= '<style>';

			foreach ($resources as $res)
			{
				$css .= $res;
			}

			$css .= '</style>';
		}
		else
		{
			foreach ($resources as $res)
			{
				$css .= '<link src="';
				$css .= $res;
				$css .= '" type="text/css" />';
			}
		}

		return $css;
	}

	protected function _displayJs ($place, $resources)
	{
		$js = '';

		if ($place == 'inline')
		{
			$js .= '<script>';

			foreach ($resources as $res)
			{
				$js .= $res;
			}

			$js .= '</script>';
		}
		else
		{
			foreach ($resources as $res)
			{
				$js .= '<script src="';
				$js .= $res;
				$js .= '"></script>';
			}
		}

		return $js;
	}

	public function getHelper ()
	{
		return $this->getComponent(func_get_args());
	}

}
