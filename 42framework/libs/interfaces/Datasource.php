<?php
namespace framework\libs\interfaces;

interface Datasource
{
	public function count($field, $from, $where = array());
	
	public function find($fields, $from, $where = array(), $order = null, $limit = null, $offset = null);
}

?>