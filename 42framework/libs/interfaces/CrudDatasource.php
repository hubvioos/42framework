<?php
namespace framework\libs\interfaces;

interface CrudDatasource
{
	public function create();
	
	public function read();
	
	public function update();
	
	public function delete();
}

?>