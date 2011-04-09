<?php
namespace framework\filters\appFilters;

defined('FRAMEWORK_DIR') or die('Invalid script access');

class DoctrineFilter extends \framework\filters\Filter
{
	public function _after(&$request, &$response)
	{
		if($this->getContainer()->getAccessCounter('entityManager') >= 1)
		{
			$this->getComponent('entityManager')->flush();
		}
	}
}