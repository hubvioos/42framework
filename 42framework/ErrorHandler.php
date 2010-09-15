<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ErrorHandler implements interfaces\iErrorHandler
{	
	protected static $_instance;
	
	private $_error = null;
	
	/**
	 * @var \SplObjectStorage of \Framework\interfaces\iErrorHandlerListener
	 */
	protected $_observers;
	
	protected function __construct ()
	{
		$this->_observers = new \SplObjectStorage();
	}
	
	/**
	 * @return \Framework\ErrorHandler
	 */
	public static function getInstance ()
	{
		if (self::$_instance === null)
		{
			self::$_instance = new ErrorHandler();
		}
		return self::$_instance;
	}
	
	protected function __clone () { }
	
	public function start ($errorReporting, $displayError)
	{
		error_reporting($errorReporting);
		ini_set('display_errors', $displayError);
		set_error_handler(array($this, 'errorHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));
		return $this;
	}
	
	public function exceptionHandler ($e)
	{
		$this->_error = $e;
		$this->notify();
	}
	
	public function errorHandler ($errno, $errstr, $errfile, $errline)
	{
		if (error_reporting() === 0)
		{
			return;
		}
		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
	
	/**
	 * @return \Exception
	 */
	public function getError ()
	{
		if ($this->_error === null)
		{
			return false;
		}
		return $this->_error;
	}
	
	public function attach (interfaces\iErrorHandlerListener $obs)
	{
		$this->_observers->attach($obs);
		return $this;
	}
	
	public function detach (interfaces\iErrorHandlerListener $obs)
	{
		$this->_observers->detach($obs);
		return $this;
	}
	
	public function notify ()
	{
		foreach ($this->_observers as $obs)
		{
			$obs->update($this);
		}
		return $this;
	}
}