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
	public static function init (Array $config = array(), $configPath)
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