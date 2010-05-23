<?php
namespace framework\libs\datasources;

class MongoDatasource
{
	protected $db = null;
	
	public function __construct()
	{
		$this->db = DbProvider::getConnexion('default');
	}
}
?>