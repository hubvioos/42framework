<?php
namespace framework\libs;

class Core
{
    protected static $instance = null;
    protected $request = null;
    protected $filters = array();
    
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
    
    public function execute()
    {
        foreach($this->filters as $f)
        {
        	$f->beforeFilter();
        }
        
        $this->request->module->execute($this->request->action, $this->request->params);
        
        foreach(array_reverse($this->filters) as $f)
        {
        	$f->afterFilter();
        }
        
        return $this->request->module;
    }    
}
?>