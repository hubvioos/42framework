<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ViewException extends Exception { }

class View
{
	// adresse du fichier de vue à inclure
	protected $file;

	// variables supplèmentaires
	protected $vars = array();
	
	protected static $globalsVars = array();

	// on définit l'adresse du fichier de vue à inclure et on récupère les variables supplémentaires
	public function __construct ($file, $vars = false)
	{
		$this->file = ltrim($file, '/') . '.php';
		
		if (!file_exists($this->file))
		{
			throw new ViewException($this->file.' : View doesn\'t exist.');
		}
		
		if ($vars !== false)
		{
			$this->vars = $vars;
		}
	}
	
	public static function factory ($file, $vars)
	{
		return new View($file, $vars);
	}

	// assigne une variable supplémentaire au tableau vars
	public function __set ($name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	public function __get($name)
	{
		if (!isset($this->vars[$name]))
		{
			throw new ViewException ($name.' : var doesn\'t exist.');
			return null;
		}
		return $this->vars[$name];
	}
	
	// assigne une variable supplémentaire au tableau vars
	public static function setGlobal ($name, $value)
	{
		self::$globalsVars[$name] = $value;
	}
	
	public static function getGlobal($name)
	{
		if (!isset(self::$globalsVars[$name]))
		{
			throw new ViewException ($name.' : global var doesn\'t exist.');
			return null;
		}
		return self::$globalsVars[$name];
	}

	// effectue le rendu de la vue
	public function render ()
	{
		extract(self::$globalsVars);
		extract($this->vars);
		
		ob_start();
		include $this->file;
		return ob_get_clean();
	}

	// effectue le rendu de la vue et le retourne
	public function __toString ()
	{
		return $this->render();
	}
}