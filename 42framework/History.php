<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class HistoryException extends \Exception { }

class History
{	
	/**
	 * @var $_history \Framework\Libs\Session
	 */
	protected $_history = null;
	
	protected $_historySize = null;
	
	protected static $_instance = null;

	protected function __clone () { }
	
	/**
	 * @param \Framework\Libs\Session $session
	 * @param integer $historySize
	 */
	protected function __construct (Libs\Session $session, $historySize)
	{
		if ($session->getNamespace() != 'history')
		{
			throw new HistoryException ('Session Namespace is not "history"');
		}
		
		$this->_history = $session;
		
		$this->_historySize = $historySize;
	}
	
	/**
	 * @param Session $session
	 * @param unknown_type $historySize
	 * @return \Framework\History
	 */
	public static function getInstance (Libs\Session $session, $historySize)
	{
		if (self::$_instance === null)
		{
			self::$_instance = new self($session, $historySize);
		}
		return self::$_instance;
	}
	
	public function update (Array $values = array())
	{
		$size = sizeof($this->_history);
		
		foreach ($this->_history as $key => $value)
		{			
			if (!($key == 0 && $size >= $this->_historySize))
			{				
				$this->_history[$size-$key] = $this->_history[$size-$key-1];
			}
		}
		$this->_history[0] = $values;
	}
	
	public function get ()
	{
		return $this->_history;
	}
	
	public function getPrevious ()
	{
		return (isset($this->_history[0])) ? $this->_history[0] : null;
	}
}