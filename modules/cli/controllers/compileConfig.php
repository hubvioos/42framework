<?php namespace Application\modules\cli\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CompileConfig extends CliCommand
{
	public function processAction ()
	{
		$config = array();
		require FRAMEWORK_DIR.DS.'config'.DS.'config.php';
		$frameworkConfig = $config;
		
		require APPLICATION_DIR.DS.'config'.DS.'config.php';
		$appConfig = $config;
		
		$config = array_merge($frameworkConfig, $appConfig);
		
		$ab = new \Application\modules\cli\ConfigBuilder($config);
		$ab->setTemplateFile(MODULES_DIR.DS.'cli'.DS.'views'.DS.'configTemplate.php');
		$ab->save(APPLICATION_DIR.DS.'build'.DS.'config.php');
	}
}