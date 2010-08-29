<?php namespace Application\modules\cli\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CliAutoloadException extends \Exception { }

class CliAutoload extends CliGeneric
{
	public function compileAutoload ()
	{
		$scanner = new \TheSeer\Tools\DirectoryScanner;
		$scanner->addInclude('*.php');
		
		$finder = new \TheSeer\Tools\ClassFinder;
		
		$found = array_merge($finder->parseMulti($scanner(VENDORS_DIR)), 
							 $finder->parseMulti($scanner(FRAMEWORK_DIR)), 
							 $finder->parseMulti($scanner(APPLICATION_DIR)));
		
		$ab = new \TheSeer\Tools\AutoloadBuilder($found);
		$ab->setTemplateFile(FRAMEWORK_DIR.DS.'modules'.DS.'cli'.DS.'views'.DS.'autoloadTemplate.php');
		$ab->save(APPLICATION_DIR.DS.'build'.DS.'autoload.php');
	}
	
	public function compileActionsMap ()
	{
		$scanner = new \TheSeer\Tools\DirectoryScanner;
		$scanner->addInclude('*.php');
		
		$finder = new \TheSeer\Tools\ClassFinder;
		
		$found = array_merge($finder->parseMulti($scanner(FRAMEWORK_DIR.DS.'modules')), 
							 $finder->parseMulti($scanner(APPLICATION_DIR.DS.'modules')));
		
		$actionsMap = array();
		foreach (array_keys($found) as $class)
		{
			$ref = new \ReflectionClass(stripslashes($class));
			
			$methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
			$namespace = $ref->getNamespaceName();
			list(,,$module) = explode('\\', $namespace);
			foreach ($methods as $method)
			{
				if (strpos($method->getName(), '__') === false && !$ref->isSubclassOf('\Exception'))
				{
					$actionsMap[$module][$method->getName()] = $class;
				}
			}
		}
		$ab = new \Application\Modules\Cli\ActionsMapBuilder($actionsMap);
		$ab->setTemplateFile(FRAMEWORK_DIR.DS.'modules'.DS.'cli'.DS.'views'.DS.'actionsMapTemplate.php');
		$ab->save(APPLICATION_DIR.DS.'build'.DS.'actionsMap.php');
	}
}