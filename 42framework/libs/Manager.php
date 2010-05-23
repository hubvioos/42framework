<?php
namespace framework\libs;

class Manager
{
	protected $db = null;
	
	public function __construct()
	{
		$this->db = DbProvider::getConnexion('default');
	}
}
?>