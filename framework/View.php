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

class ViewException extends \Exception { }

class View extends FrameworkObject
{
	// adresse du fichier de vue à inclure
	protected $_file;

	// variables supplèmentaires
	protected $_vars = array();
	
	protected $_renderedView = null;
	
	protected static $_globalsVars = array();

	// on définit l'adresse du fichier de vue à inclure et on récupère les variables supplémentaires
	public function __construct ($module, $file, $vars = false)
	{
		$config = $this->getContainer()->getConfig();
		$this->_file = MODULES_DIR.DS.$module.DS.'views'.DS.$file.$config['viewExtension'];
		
		if (!file_exists($this->_file))
		{
			$globalFile = APPLICATION_DIR.DS.'views'.DS.$file.$config['viewExtension'];
			if (file_exists($globalFile))
			{
				$this->_file = $globalFile;
			}
			else
			{
				throw new ViewException($this->_file.' : View not found.');
			}
		}
		
		if ($vars !== false)
		{
			$this->_vars = $vars;
		}
	}

	// assigne une variable supplémentaire au tableau vars
	public function __set ($name, $value)
	{
		$this->_vars[$name] = $value;
	}
	
	public function __get($name)
	{
		if (!isset($this->_vars[$name]))
		{
			return null;
		}
		return $this->_vars[$name];
	}
	
	// assigne une variable supplémentaire au tableau vars
	public static function setGlobal ($name, $value)
	{
		self::$_globalsVars[$name] = $value;
	}
	
	public static function getGlobal($name)
	{
		if (!isset(self::$_globalsVars[$name]))
		{
			return null;
		}
		return self::$_globalsVars[$name];
	}

	// effectue le rendu de la vue
	public function render ()
	{
		if ($this->_renderedView === null)
		{
			extract(self::$_globalsVars);
			extract($this->_vars);
			
			ob_start();
			include $this->_file;
			$this->_renderedView = ob_get_clean();
		}
		return $this->_renderedView;
	}
	
	public function getRenderedView()
	{
		return $this->_renderedView;
	}
	
	public function setRenderedView($view)
	{
		$this->_renderedView = $view;
		return $this;
	}

	// effectue le rendu de la vue et le retourne
	public function __toString ()
	{
		return $this->render();
	}
}