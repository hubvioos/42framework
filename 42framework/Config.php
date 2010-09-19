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

class Config
{
	/**
	 * @var array
	 */
	public static $config = array();

	/**
	 * @param array $data
	 */
	public static function init (Array $config = array(), $configPath = null)
	{
		if (empty($config))
		{
			require $configPath;
		}
		self::$config = $config;
	}

	/**
	 * @return array
	 */
	public static function getConfig ()
	{
		return self::$config;
	}

	/**
	 * @param string $key
	 * @return mixed (value corresponding to $key or null)
	 */
	public static function get ($key)
	{
		if (strpos($key, '.'))
		{
			$key = explode('.', $key);
			$taille = sizeof($key);
			$value = null;
			
			for ($i = 0; $i < $taille; $i++)
			{
				if ($i == 0)
				{
					$value = self::$config[$key[0]];
				}
				else
				{
					$value = $value[$key[$i]];
				}
			}
			return $value;
		}
		return isset(self::$config[$key]) ? self::$config[$key] : null;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public static function exists ($key)
	{
		if (strpos($key, '.'))
		{
			$key = explode('.', $key);
			$taille = sizeof($key);
			$ok = false;
			$value = null;
			
			for ($i = 0; $i < $taille; $i++)
			{
				if ($i == 0 && isset(self::$config[$key[0]]))
				{
					$ok = true;
					$value = self::$config[$key[0]];
				}
				elseif (isset($value[$key[$i]]))
				{
					$ok = true;
					$value = $value[$key[$i]];
				}
				else 
				{
					$ok = false;
				}
			}
			return $ok;
		}
		return isset(self::$config[$key]) ? true : false;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public static function set ($key, $value)
	{
		self::$config[$key] = $value;
	}
}