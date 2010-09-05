<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class HistoryException extends \Exception { }

class History
{	
	protected $_history = null;
	
	protected $_historySize = null;
	
	protected static $_instance = null;

	protected function __clone () { }
	
	protected function __construct (Session $session, $historySize)
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
	public static function getInstance (Session $session, $historySize)
	{
		if (History::$_instance === null)
		{
			History::$_instance = new History($session);
		}
		return History::$_instance;
	}
	
	public function update ($values = array())
	{
		$this->_history[] = $values;
				
		if (sizeof($this->_history) > $this->_historySize)
		{
			array_shift($this->_history);
		}
	}
	
	public function get ()
	{
		return $this->_history;
	}
	
	public function getPrevious ()
	{
		end($this->_history);
		
		return prev($this->_history);
	}
}