<?php
namespace framework\libs;

class Controller
{
    protected $layout = 'default';
    protected $view = false;
    protected $execView = true;
    protected $layout_vars = array();
    
    public function __construct()
    {
    	
    }
    
    public function checkParams($action, $params)
    {
    	
    }
    
    public function showError404($params = array())
    {
    	$this->setExecView(false);
    	$this->setLayout(false);
    	$module = '\app\modules\\'.Registry::get('defaultModule');
    	$module = new $module();
    	$module->execute('error404', array($params));
    	$module->display(false);
    }
    
    public function execute($action, $params)
    {
        $this->beforeExec();
        
        call_user_func_array(array($this, $action), $params);
        
        $this->afterExec();
        
        if($this->view == false)
        {
        	$this->view = Registry::get('request.module').DS.Registry::get('request.action');
        }
    }
    
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }
    
    public function setView($view)
    {
        $this->view = $view;
    }
    
    public function setExecView($bool)
    {
        $this->execView = $bool;
    }
    
    public function beforeExec()
    {
    	
    }
    
    public function afterExec()
    {
        
    }
    
    public function set($var, $value = false)
    {
        if(is_array($var))
        {
            array_merge($this->layout_vars, $var);
        }
        else
        {
            $this->layout_vars[$var] = $value;
        }
    }
    
    public function render()
    {
        if($this->layout)
        {
            if($this->execView)
        	{
            	$this->layout_vars['content_for_layout'] = new View($this->view, $this->layout_vars);
            }
            else
            {
            	$this->layout_vars['content_for_layout'] = null;
            }
            
            if(!isset($this->layout_vars['pageTitle']))
            {
            	$this->layout_vars['pageTitle'] = Registry::get('defaultPageTitle');
            }
            
            return new View('layouts/'.$this->layout, $this->layout_vars);
        }
        
        if($this->execView)
        {
        	return new View($this->view, $this->layout_vars);
        }
        
        return;
    }
    
    public function display($exit = true)
    {
        echo $this->render();
        
        if($exit)
        {
        	exit;
        }
    }  
}
?>