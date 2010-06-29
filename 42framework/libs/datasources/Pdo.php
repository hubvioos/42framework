<?php
namespace framework\libs\datasources;

class Pdo extends \PDO
{
	final public function __construct($dsn, $username = '', $password = '', $driverOptions = array())
	{
		parent::__construct($dsn, $username, $password, $driverOptions);        
		$this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
		$this->setAttribute(self::ATTR_STATEMENT_CLASS, array('Statement'));
		Statement::setPDOInstance($this);
	}
}
?>