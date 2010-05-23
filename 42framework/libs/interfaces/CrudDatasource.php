<?php
namespace framework\libs\interfaces;

interface CrudDatasource
{
	public function create($object);
	
	public function read($object);
	
	public function update($object);
	
	public function delete($object);
}

?>