<?php
namespace framework\libs\interfaces;

interface Datasource
{
	public function count($object, $criteria = array());
	
	public function find($object, $criteria = array(), $order = null, $limit = null, $offset = null);
}

?>