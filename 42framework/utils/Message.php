<?php
namespace Framework\Utils;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class MessageException extends \Exception { }

class Message
{	
	public static function add (Session $session, $category, $value)
	{
		if ($session->getNamespace() != 'message')
		{
			throw new MessageException ('Session Namespace is not "message"');
		}
		
		$session[$category] = $value;
	}
	
	public static function get (Session $session, $category)
	{
		if ($session->getNamespace() != 'message')
		{
			throw new MessageException ('Session Namespace is not "message"');
		}
		
		return $session[$category];
	}
	
	public static function clearCategory (Session $session, $category)
	{
		if ($session->getNamespace() != 'message')
		{
			throw new MessageException ('Session Namespace is not "message"');
		}
		
		unset($session[$category]);
	}
	
	public static function clearAll (Session $session)
	{
		if ($session->getNamespace() != 'message')
		{
			throw new MessageException ('hSession Namespace is not "message"');
		}
		
		$session->destroy();
	}
}