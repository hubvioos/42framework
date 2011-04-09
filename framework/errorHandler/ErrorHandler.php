<?php 
/**
 * Copyright (C) 2011 - K√©vin O'NEILL, Fran√ßois KLINGLER - <contact@42framework.com>
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

namespace framework\errorHandler;

class ErrorHandler
{	
	/**
	 * @var \Exception
	 */
	protected $_error = null;
	
	/**
	 * @var \SplObjectStorage
	 */
	protected $_observers;
	
	public function __construct ()
	{
		$this->_observers = new \SplObjectStorage();
	}
	
	/**
	 * @param integer $errorReporting
	 * @param integer $displayError
	 * @return \framework\errorHandler\ErrorHandler
	 */
	public function init ($errorReporting, $displayError)
	{
		error_reporting($errorReporting);
		ini_set('display_errors', $displayError);
		set_error_handler(array($this, 'errorHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));
		return $this;
	}
	
	/**
	 * @param \Exception $e
	 */
	public function exceptionHandler ($e)
	{
		$this->_error = $e;
		$this->notify();
	}
	
	/**
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 * @throws \ErrorException
	 */
	public function errorHandler ($errno, $errstr, $errfile, $errline)
	{
		if (error_reporting() === 0)
		{
			return true;
		}
		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
	
	/**
	 * @return \Exception
	 */
	public function getLastError ()
	{
		if ($this->_error === null)
		{
			return false;
		}
		return $this->_error;
	}
	
	/**
	 * @param \framework\errorHandler\interfaces\iErrorHandlerListener $obs
	 * @return \framework\errorHandler\ErrorHandler
	 */
	public function attach (\framework\errorHandler\interfaces\iErrorHandlerListener $obs)
	{
		$this->_observers->attach($obs);
		return $this;
	}
	
	/**
	 * @param \framework\errorHandler\interfaces\iErrorHandlerListener $obs
	 * @return \framework\errorHandler\ErrorHandler
	 */
	public function detach (\framework\errorHandler\interfaces\iErrorHandlerListener $obs)
	{
		$this->_observers->detach($obs);
		return $this;
	}
	
	/**
	 * @return \framework\errorHandler\ErrorHandler
	 */
	public function notify ()
	{
		foreach ($this->_observers as $obs)
		{
			/* @var $obs interfaces\iErrorHandlerListener */
			$obs->update($this);
		}
		return $this;
	}
}