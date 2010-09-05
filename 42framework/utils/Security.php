<?php
namespace Framework\Utils;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class SecurityException extends \Exception { }

class Security
{
	/**
	 * @param \Framework\Session $history
	 */
	public static function checkHistory ($history)
	{
		if ($history !== null && !empty($history))
		{
			$last = end($history);
			
			if ($last === false)
			{
				throw new SecurityException('checkHistory : History not filled.');
			}
			
			$prev = prev($history);
			
			if ($prev === false)
			{
				return true;
			}
			
			if ($last['ipAddress'] !== $prev['ipAddress'] || $last['userAgent'] !== $prev['userAgent'])
			{
				return false;
			}
		}
		return true;
	}
}