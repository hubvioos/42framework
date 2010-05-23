<?php
namespace framework\libs\interfaces;

interface DbDatasource
{
	public function exec($query);
	
	public function query($query);
	
	public function describeTable();
}

?>