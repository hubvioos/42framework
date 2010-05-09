<?php
namespace framework\libs;

/*
	Classe Core (singleton)
		
	Le coeur du framework, sa méthode execute s'occupe de tout charger : c'est l'étape principale de l'exécution des pages.
	Les filtres gérés avec addFilter et deleteFilter sont appelés dans execute() avant et après l'exécution principale du code. Ils permettent par exemple une gestion de l'authentification.
*/
class Core
{
	protected static $instance = null;
	protected $request = null;
	protected $filters = array();
	
	/*
		A la création de l'instance de Core, on stocke l'instance de Request dans l'attribut request.
	*/
	protected function __construct() {
		$this->request = Request::getInstance();
	}
	
	protected function __clone() {}
	
	public static function getInstance()
	{
		if(!isset(self::$instance))
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function addFilter($name, $class, $params = array()) {
		$this->filters[$name] = new $class($params);
	}
	
	public function deleteFilter($name) {
		if(isset($this->filters[$name]))
		{
			unset($this->filters[$name]);
		}
	}
	
	/*
		Méthode principale de l'éxecution de la page
	*/
	public function execute()
	{
		foreach($this->filters as $f)
		{
			$f->beforeFilter();
		}
		
		/*
			Exécute l'action du module définis par l'instance de Request
		*/
		$this->request->module->execute($this->request->action, $this->request->params);
		
		foreach(array_reverse($this->filters) as $f)
		{
			$f->afterFilter();
		}
		
		return $this->request->module;
	}	
}
?>