<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ViewException extends \Exception { }

class View
{
	// adresse du fichier de vue à inclure
	protected $_file;

	// variables supplèmentaires
	protected $_vars = array();
	
	protected static $_globalsVars = array();

	// on définit l'adresse du fichier de vue à inclure et on récupère les variables supplémentaires
	public function __construct ($module, $file, $vars = false)
	{
		$this->_file = MODULES_DIR.DS.$module.DS.'views'.DS.$file;
		
		if (!file_exists($this->_file))
		{
			$globalFile = APPLICATION_DIR.DS.'views'.DS.$file;
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
	
	public static function factory ($module, $file, $vars = false)
	{
		return new View($module, $file, $vars);
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
		View::$_globalsVars[$name] = $value;
	}
	
	public static function getGlobal($name)
	{
		if (!isset(View::$_globalsVars[$name]))
		{
			return null;
		}
		return View::$_globalsVars[$name];
	}

	// effectue le rendu de la vue
	public function render ()
	{
		extract(View::$_globalsVars);
		extract($this->_vars);
		
		ob_start();
		include $this->_file;
		return ob_get_clean();
	}

	// effectue le rendu de la vue et le retourne
	public function __toString ()
	{
		return $this->render();
	}
}