<?php namespace Application\modules\cli\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CompileAutoload extends CliCommand
{
	public function processAction ()
	{
		$scanner = new \Application\modules\cli\DirectoryScanner;
		$scanner->addInclude('*.php');
		
		$finder = new \Application\modules\cli\ClassFinder;
		
		$found = array_merge($finder->parseMulti($scanner(VENDORS_DIR)), 
							 $finder->parseMulti($scanner(FRAMEWORK_DIR)), 
							 $finder->parseMulti($scanner(MODULES_DIR)),
							 $finder->parseMulti($scanner(APPLICATION_DIR)));
		
		$ab = new \Application\modules\cli\AutoloadBuilder($found);
		$ab->setTemplateFile(MODULES_DIR.DS.'cli'.DS.'views'.DS.'autoloadTemplate.php');
		$ab->save(APPLICATION_DIR.DS.'build'.DS.'autoload.php');
	}
}
