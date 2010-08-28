<?php namespace Application\modules\cli\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CliAutoloadException extends \Exception { }

class CliAutoload extends CliGeneric
{
	public function compileAutoload ()
	{
		/*require VENDORS_DIR.DS.'theseer'.DS.'autoload'.DS.'classfinder.php';
		require VENDORS_DIR.DS.'theseer'.DS.'autoload'.DS.'phpfilter.php';
		require VENDORS_DIR.DS.'theseer'.DS.'autoload'.DS.'autoloadbuilder.php';

		require VENDORS_DIR.DS.'theseer'.DS.'scanner'.DS.'directoryscanner.php';
		require VENDORS_DIR.DS.'theseer'.DS.'scanner'.DS.'includeexcludefilter.php';
		require VENDORS_DIR.DS.'theseer'.DS.'scanner'.DS.'filesonlyfilter.php';
		*/
		$scanner = new \TheSeer\Tools\DirectoryScanner;
		$scanner->addInclude('*.php');
		
		$finder = new \TheSeer\Tools\ClassFinder;
		
		$found = array_merge($finder->parseMulti($scanner(VENDORS_DIR)), $finder->parseMulti($scanner(FRAMEWORK_DIR)), $finder->parseMulti($scanner(APPLICATION_DIR)));
		
		$ab = new \TheSeer\Tools\AutoloadBuilder($found);
		$ab->setTemplateFile(FRAMEWORK_DIR.DS.'modules'.DS.'cli'.DS.'views'.DS.'template.php');
		$ab->save(APPLICATION_DIR.DS.'build'.DS.'autoload.php');
	}
	
	public function compileActionsMap ()
	{
		$scanner = new \TheSeer\Tools\DirectoryScanner;
		$scanner->addInclude('*.php');
		
		$finder = new \TheSeer\Tools\ClassFinder;
		
		$found = array_merge($finder->parseMulti($scanner(FRAMEWORK_DIR.DS.'modules')), $finder->parseMulti($scanner(APPLICATION_DIR.DS.'modules')));
		$classNames = array_keys($found);
		foreach ($classNames as $class)
		{
			
		}
		var_dump($found);
	}
}