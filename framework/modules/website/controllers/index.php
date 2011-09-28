<?php

namespace framework\modules\website\controllers;

class Index extends \framework\core\Controller
{

	public function _before ()
	{
		
	}

	public function processAction ()
	{
		$model = $this->getComponent('gacela')->loadMapper('House')->find(1);
		echo '<pre>';
		\var_dump($model);
	}

	public function _after ()
	{
		
	}

}
