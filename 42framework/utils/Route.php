<?php
namespace Framework\Utils;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class RouteException extends \Exception { }

class Route
{
	protected static $routes = array();
	
	const INT_PARAM         = '\d+';
	const ALPHANUM_PARAM    = '\w+';
	const WORD_PARAM        = '[a-zA-Z]+';
	
	public static function init ($routes)
	{
		self::$routes = $routes;
	}
	
	public static function pathToParams($path)
	{
		$params = array();
		
		$exploded_path = explode('/', $path);
		
		list($params['module'], $params['action']) = $exploded_path;
		
		$params['params'] = array_slice($exploded_path, 2);
		
		return $params;
	}
	
	public static function paramsToPath($params)
	{
		$path = null;

		if($params['module'] !== Config::$config['defaultModule'])
		{
			$path .= $params['module'];
		}

		$path .= '/'.$params['action'];

		if(!empty($params['params']))
		{
			$path .= '/'.implode('/', $params['params']);
		}

		return $path;
	}
	
	public static function urlToPath($url) 
	{    
	    $path = $url;
	    
	    $routed = false;
	    $redirect = false;
	    
	    $defaultModule = Config::$config['defaultModule'];
	    $defaultAction = Config::$config['defaultAction'];
	    $defaultPath = $defaultModule . '/' . $defaultAction;
	    
	    // Clean the "/" at the end of the url
		if(strrchr($path, '/') === '/')
		{
			$path = rtrim($path, '/');
			$redirect = true;
		}
		
		/*
		 * Redirect to the default path in some cases
		 *
		 */
		
		if ($path === '')
		{
		    $path = $defaultPath;
			return $path;
		}
		
		$explodedUrl = explode('/', $path);
		
		if (sizeof($explodedUrl) == 1)
		{
		    if ($explodedUrl[0] === Config::$config['defaultAction'])
		    {
		        $path = $defaultPath;
		        
    		    $redirect = true;
		    }
		    else
		    {
                $path = $explodedUrl[0] . '/' . $defaultAction;
                
                if ($explodedUrl[0] === $defaultModule)
                {
        		    $redirect = true;
                }
            }		    
		}
		
		/*
		 * Change URL into path
		 *
		 */
		
		foreach(self::$routes as $routeUrl => $routeParams)
		{
		    // Check if route is dynamic
		    if (strpos($routeUrl, '<') !== false)
		    {
			    $regex = $routeUrl;
    			foreach($routeParams['params'] as $routeParam => $routeRegex)
    			{
        			$regex = str_replace('<'.$routeParam.'>', '('.$routeRegex.')', $regex);
    			}
			
    			// Default regex
    			preg_replace('/<(\w+)>/', '(.*)', $regex);
			
    			if(preg_match_all('#^'.$regex.'$#', $url, $match, PREG_SET_ORDER))
    			{
    			    $path = $routeParams['module'] . '/' . $routeParams['action'];
			    
    			    array_shift($match[0]);
    			    foreach($match[0] as $value)
    			    {
    			        $path .= '/' . $value;
    			    }
			    
    			    break;
    			}
		    }
		    else
		    {
		        if ($routeUrl == $url)
		        {
		            $path = $routeParams['module'] . '/' . $routeParams['action'];
		            
		            if (!empty($routeParams['params']))
		            {
		                $path .= '/' . implode('/', $routeParams['params']);
		            }
		            
		            break;
		        }
		    }
		}
				
		
		// Redirect if we need to.
		if ($redirect)
		{
		    \Framework\Response::getInstance()->redirect(Config::$config['siteUrl'] . $path, 301, true);
		}
			
		return $path;
	}
	
	public function pathToUrl($path)
	{	
	    $url = $path;
	    
	    $found = null;
	    
		if(strrchr($url, '/') === '/')
		{
			$url = rtrim($url, '/');
		}
		
        $pathParams = self::pathToParams($url);
		
		foreach($this->routes as $routeUrl => $routeParams)
		{
		    $regex = $routeUrl['module'] . '/' . $routeUrl['action'];
		    
            // On sort chaque argument de la route
    		if (preg_match_all('/<(\w+)>/', $routeUrl, $args, PREG_SET_ORDER))
			{
    			array_shift($args[0]);
    			
    			foreach($args[0] as $value) // Pour chaque argument on check si ça correspond
    			{
    			    $found = false;
    			    foreach($routeParams['params'] as $routeParam => $routeRegex)
        			{
        			    if ($value == $routeParam)
        			    {
        			        $regex .= '/' . '(' . $routeRegex . ')';
        			        $found = true;
        			        break;
        			    }
        			}
    			    if (!$found)
    			    {
    			        $regex .= '/' . '(.*)';
    			    }
    			}

    			// We check the path with the newly created regex.
    			if (preg_match_all($regex, $url, $match, PREG_SET_ORDER))
    			{
    			    array_shift($match[0]);
    			    
    			    // And we replace each arg by its value.
    			    foreach($args[0] as $key => $value)
    			    {
    			        $url = str_replace('<'.$value.'>', $match[0][$key], $url);
    			    }
    			    
    			    return $url;
    			}
			}
			else // If the route is static
			{
			    if (!empty($routeParams['params']))
			    {
			        $regex .= '/' . implode($routeParams['params']);
			    }
			    
			    if ($regex == $path)
		        {
		            return $routeUrl;
		        }
			}
		}
		
		return $url;
	}	
	
	public static function paramsToUrl($params) 
	{
		return self::pathToUrl(self::paramsToPath($params));
	}
	
	public static function urlToParams($url)
	{
		return self::pathToParams(self::urlToPath($url));
	}
}