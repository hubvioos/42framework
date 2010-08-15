<?php
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
	public static function loadConfig (Array $data = array())
	{
		self::$config = $data;
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
				if ($i == 0 && $ok = isset(self::$config[$key[0]]))
				{
					$value = self::$config[$key[0]];
				}
				elseif ($ok = isset($value[$key[$i]]))
				{
					$value = $value[$key[$i]];
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