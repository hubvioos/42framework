<?php
namespace framework\libs\datasources;
use framework\libs as F;

class MongoDatasource
{
	protected $db = null;
	
	public function __construct()
	{
		$this->db = F\DbProvider::getConnexion('default');
	}
}
?>