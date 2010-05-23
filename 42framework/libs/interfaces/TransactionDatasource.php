<?php
namespace framework\libs\interfaces;

interface TransactionDatasource
{
	public function beginTransaction();
	
	public function commit();
	
	public function rollBack();
}

?>