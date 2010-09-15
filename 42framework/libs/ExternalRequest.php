<?php
namespace Framework\Libs;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ExternalRequestException extends \Exception { }

class ExternalRequest
{
	protected $_url = null;

	protected $_params = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à Route.
	*/
	protected function __construct ($_url, $_params = null)
	{
		$this->_url = $_url;
		if ($_params !== null)
		{
			$this->_params = $_params;
		}
	}

	protected function __clone () { }

	public static function factory ($_url, $_params = null)
	{
		return new ExternalRequest($_url, $_params);
	}
	
	public function execute ()
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $this->_url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_HEADER, false);
		if ($this->_params !== false)
		{
			curl_setopt($c, CURLOPT_POST, true);
			curl_setopt($c, CURLOPT_POSTFIELDS, $this->_params);
		}
		$output = curl_exec($c);
		if($output === false)
		{
			trigger_error('Erreur curl : '.curl_error($c), E_USER_WARNING);
		}
		curl_close($c);
		return $output;
	}
	
	/**
	 * @return the $_url
	 */
	public function getUrl ()
	{
		return $this->_url;
	}

	/**
	 * @return the $_params
	 */
	public function getParams ()
	{
		return $this->_params;
	}
}
