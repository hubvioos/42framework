<?php
namespace framework\libs;

class Model
{
	protected $db = null;
	
	public function __construct()
	{
		$this->db = DbProvider::getInstance();
	}
}
?>