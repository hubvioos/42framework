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
namespace framework\Libs;

class ExternalRequest
{
	protected $_url = null;

	protected $_params = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à Route.
	*/
	public function __construct ($_url, $_params = null)
	{
		$this->_url = $_url;
		if ($_params !== null)
		{
			$this->_params = $_params;
		}
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
			throw new \RuntimeException('Erreur curl : '.curl_error($c));
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
