<?php namespace Application\modules\errors\controllers;
use Framework;

defined('FRAMEWORK_DIR') or die('Invalid script access');

class error404 extends \Application\modules\errors\generic
{
	public function processAction ($request = null)
	{
		$this->_response
			->status(404)
			->setBody(\Framework\View::factory('errors', 'error404', array('requestedUrl' => \Framework\Context::getInstance()->getUrl(true))));
		
		return true;
	}
}
