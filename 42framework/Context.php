<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ContextException extends \Exception { }

class Context ()
{    
	public static $url = null;
	
    public static $ipAddress = '0.0.0.0';
	
	public static $userAgent = null;
	
	public static $acceptCharset = null;
	
	public static $acceptLanguage = null;
	
	public static $acceptEncoding = null;
	
	public static $isSecure = false;
	
	public static $isAjax = false;
	
	public static $protocol = 'http';
	
	public static $isCli = false;

	protected static $instance = null;
	
	protected function __construct ()
	{
	    
	}
	
	protected function __clone () { }

	public static function getInstance ()
	{    
	    if (Context::$instance === null)
		{
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    		{
    			Context::$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    		}
    		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
    		{
    			Context::$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    		}
    		elseif (isset($_SERVER['REMOTE_ADDR']))
    		{
    			Context::$ipAddress = $_SERVER['REMOTE_ADDR'];
    		}
		
    		if (!filter_var(Context::$ipAddress, FILTER_VALIDATE_IP))
    		{
    			Context::$ipAddress = '0.0.0.0';
    		}
		
    		Context::$userAgent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? null : $_SERVER['HTTP_USER_AGENT'];
		
    		Context::$acceptCharset = (!isset($_SERVER['HTTP_ACCEPT_CHARSET'])) ? Config::$config['defaultCharset'] : Context::$instance->extractValue(
    			$_SERVER['HTTP_ACCEPT_CHARSET']);
		
    		Context::$acceptEncoding = (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? null : Context::$instance->extractValue($_SERVER['HTTP_ACCEPT_ENCODING']);
		
    		Context::$acceptLanguage = (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? Config::$config['defaultLanguage'] : Context::$instance->extractValue(
    			$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		
    		Context::$isSecure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN));
		
    		Context::$isAjax = (isset($_SERVER['HTTP_X_ContextED_WITH']) AND strtolower($_SERVER['HTTP_X_ContextED_WITH']) === 'xmlhttpContext');
    	}
    	return Context::$instance;
    }
}