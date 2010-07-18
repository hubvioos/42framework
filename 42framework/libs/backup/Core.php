<?php
namespace framework\libs;

class Core
{
    protected static $instance = null;
    protected $module = null;
    protected $action = null;
    protected $params = array();
    protected $filters = array();
    
    protected function __construct($params = array()) {
    	$class = '\app\modules\\'.$params['module'];
    	
    	if(class_exists($class))
    	{
    		$this->module = new $class();
    		
    		if(method_exists($this->module, $params['action']))
    		{
    			$this->action = $params['action'];
    			
    			if(!empty($params['params']))
    			{
    				$methodParams = $params['params'];
    			}
    			else
    			{
    				$methodParams = array();
    			}
    			
    			$this->params = $methodParams;
    		}
    		else
    		{
    			throw new Exception('La méthode n\'existe pas !');
    		}
    	}
    	else
    	{
    		throw new Exception('La classe n\'existe pas !');
    	}
    }
    
    protected function __clone() {}
    
    public static function getInstance($params = array())
    {
    	if(!isset(self::$instance))
        {
        	self::$instance = new self($params);
        }
        
        return self::$instance;
    }
    
    public function addFilter($filter) {
    	$this->filters[] = new $filter();
    }
    
    public function deleteFilter($pos) {
    	array_splice($this->filters, $pos, 1);
    }
    
    public function execute()
    {
        foreach($this->filters as $f)
        {
        	$f->beforeFilter();
        }
        
        $this->module->execute($this->action, $this->params);
        
        foreach(array_reverse($this->filters) as $f)
        {
        	$f->afterFilter();
        }
        
        return $this->module;
    }    
}
?>