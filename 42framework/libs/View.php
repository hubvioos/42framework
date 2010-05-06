<?php
namespace framework\libs;

class View
{
    // adresse du fichier de vue à inclure
    private $file;
    
    // variables supplèmentaires
    private $vars = array();

    // on définit l'adresse du fichier de vue à inclure et on récupère les variables supplémentaires
    public function __construct($file, $vars = false)
    {
        $this->file = APP.DS.'views'.DS.ltrim($file, '/').'.php';
        
        if(!file_exists($this->file))
        {
            throw new Exception("La vue '{$this->file}' n'a pas été trouvée !");
        }
        
        if($vars !== false)
        {
            $this->vars = $vars;
        }
    }
    
    public function loadFragment($file, $vars = array(), $cacheFragment = false, $cacheTtl = false)
    {
    	if($cacheFragment)
    	{
    		$cache = new FileCache('fragments-'.trim($file, '/'), $cacheTtl);
    		
    		if($cache->exists())
    		{
    			return $cache->get();
    		}
    		else
    		{
    			$cache->start();
    		}
    	}
    	
    	$path = APP.DS.'views'.DS.'fragments'.DS.trim($file, '/').'.php';
    	extract($vars, EXTR_SKIP);
    	include $path;
    	
    	if($cacheFragment)
    	{
    		$cache->end();
    		return $cache->get();
    	}
    }

    // assigne une variable supplémentaire au tableau vars
    public function assign($name, $value = null)
    {
        if(is_array($name))
        {
        	array_merge($this->vars, $name);
        }
        else
        {
        	$this->vars[$name] = $value;
        }
    }
	
	public function beforeRender()
    {
    	
    }
    
    public function afterRender()
    {
        
    }
	
    // effectue le rendu de la vue
    public function render()
    {
        ob_start();
        
        $this->beforeRender();
        
        extract($this->vars, EXTR_SKIP);
        include $this->file;
        
        $this->afterRender();
        
        return ob_get_clean();
    }

    // affiche le rendu de la vue
    public function display() {
    	echo $this->render();
    }

    // effectue le rendu de la vue et le retourne
    public function __toString() {
    	return $this->render();
    }
}
?>