<?php 
/**
 * Copyright (C) 2011 - K√©vin O'NEILL, Fran√ßois KLINGLER - <contact@42framework.com>
 * 
 * 42framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * 42framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */

namespace framework\libs;

class Route 
{
	protected static $_routes = array();
	protected static $_config = null;
	
	const INT_PARAM         = '\d+';
	const ALPHANUM_PARAM    = '\w+';
	const WORD_PARAM        = '[a-zA-Z]+';
	
	public function __construct (\ArrayAccess $config)
	{
		$routes = array();
		
		if (isset($config['routes']))
		{
			$routes = $config['routes'];
		}
		self::$_routes = $routes;
		self::$_config = $config;
	}
	
	public function getRoutes()
	{
		return self::$_routes;
	}
	
	public function pathToParams($path)
	{
		$params = array();
		
		$exploded_path = explode('/', $path);
		
		list($params['module'], $params['action']) = $exploded_path;
		
		$params['params'] = array_slice($exploded_path, 2);
		
		return $params;
	}
	
	public function paramsToPath($params)
	{
		$path .= $params['module'] .'/'.$params['action'];

		if(!empty($params['params']))
		{
			$path .= '/' . implode('/', $params['params']);
		}

		return $path;
	}
	
	// public function urlToPath($url, $defaultModule, $defaultAction) 
	public function urlToPath($url) 
	{
	    if (!is_string($url))
		{
			throw new \InvalidArgumentException(__METHOD__ . ' : Invalid params');
		}
		
	    $path = rtrim($url, '/');
	    
	    $routed = false;
	    $redirect = false;
	    
	    // $defaultPath = $defaultModule . '/' . $defaultAction;
	    $defaultPath = self::$_config['defaultModule'].'/'.self::$_config['defaultAction'];
		
		/*
		 * Redirect to the default path if empty
		 *
		 */
		if ($path === '')
		{
		    return $defaultPath;
		}
		
		/*
		 * Change URL into path
		 *
		 */
		foreach(self::$_routes as $routeUrl => $routeParams)
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
    			$regex = preg_replace('#<\w*>#', '(.*)', $regex);
				
    			if(preg_match_all('#^'.$regex.'$#', $path, $match, PREG_SET_ORDER))
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
		        if ($routeUrl == $path)
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
		
		$explodedUrl = explode('/', $path);
		
		// Redirections if missing arguments
		if (count($explodedUrl) == 1)
		{
			$incompletePath = $explodedUrl[0];
			
			if($incompletePath === self::$_config['defaultAction'])
			{
				$path = $defaultPath;
			}
			else
			{
				// if the incomplete path is the name of a module
				// and if this module has a default action configured
				if(isset(self::$_config['modules'][$incompletePath]))
				{
					// check if a default action if configured or go to the generic defaultAction
					if(isset(self::$_config['modules'][$incompletePath]['defaultAction']))
					{
						$path = $incompletePath . '/' . self::$_config['modules'][$incompletePath]['defaultAction'];
					}
					else
					{
						$path = $incompletePath . '/' . self::$_config['defaultAction'];
					}
				}
				else
				{
					$path = $defaultPath;
				}
			}
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
		
        $pathParams = $this->pathToParams($url);

		foreach(self::$_routes as $routeUrl => $routeParams)
		{
		    $regex = $routeParams['module'] . '/' . $routeParams['action'];
		    
            // On sort chaque argument de la route
    		if (preg_match_all('#<(\w+)>#', $routeUrl, $args, PREG_SET_ORDER))
			{
    			array_shift($args[0]);
    			
    			foreach($args[0] as $value) // Pour chaque argument on check si √ßa correspond
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
    			if (preg_match_all('#^'.$regex.'$#', $url, $match, PREG_SET_ORDER))
    			{
    			    array_shift($match[0]);

					$url = \stripslashes($routeUrl);

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
			    //if (!empty($routeParams['params']))
			    if (count($routeParams['params']) > 0)
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
	
	public function paramsToUrl($params) 
	{
		return $this->pathToUrl($this->paramsToPath($params));
	}
	
	public function urlToParams($url)
	{
		return $this->pathToParams($this->urlToPath($url));
	}
}
