<?php
namespace framework\libs;

// Classe se chargeant d'analyser la requête de l'utilisateur pour déterminer la page à charger
// Gère les routes afin de pouvoir déterminer des URL "SEO-friendly"
// Gère les redirections avec vérification des routes
class Router
{	
	// analyse la requête de l'utilisateur et détermine le module, l'action et les éventuels paramètres
	public static function routeToApp($url, $saveInRegistry = false)
	{	
		$routed = false;
		$modified = 0;
		$routedRequest = array();
		
		if(strrchr($url, '/') === '/')
		{
			$url = rtrim($url, '/');
			$modified++;
		}
		
		if($url === '')
		{
			$routedRequest['module'] = Registry::get('defaultModule');
			$routedRequest['action'] = Registry::get('defaultAction');
			$routed = true;
		}
		
		if(!$routed)
		{
			if($routedRequest = self::checkRouteToApp($url))
			{
				$routed = true;
			}
			else
			{
				$routedRequest = array();
			}
		}
		
		/*if(!$routed)
		{
			$routes = Registry::get('routes');
			
			if(isset($routes[$url]))
			{
				$routedRequest = $routes[$url];
				$routed = true;
			}
		}
		
		if(!$routed)
		{
			foreach($routes as $k => $v)
			{
				if(strpos($k, ':') != false)
				{
					$k = str_replace(':all', '(.+)', str_replace(':num', '([0-9]+)', $k));
					
					if(preg_match('#^'.$k.'$#', $url, $match))
					{
						$routedRequest = $v;
						
						if(sizeof($match) > 1)
						{
							array_shift($match);
							$routedRequest['params'] = $match;
						}
						
						$routed = true;
						break;
					}
				}
			}
		}*/
		
		if(!$routed)
		{
			$request = explode('/', $url);
			
			if(in_array($request[0], Registry::get('prefixes')))
			{
				$routedRequest['prefix'] = array_shift($request);
			}
			
			switch(sizeof($request))
			{
				case 0:
					$routedRequest['module'] = Registry::get('defaultModule');
					$routedRequest['action'] = Registry::get('defaultAction');
					break;
				
				case 1:
					if($request[0] === Registry::get('defaultAction'))
					{
						$routedRequest['module'] = Registry::get('defaultModule');
						$routedRequest['action'] = $request[0];
						$modified++;
					}
					else
					{
						$routedRequest['module'] = $request[0];
						$routedRequest['action'] = Registry::get('defaultAction');
						
						if($request[0] === Registry::get('defaultModule'))
						{
							$modified++;
						}
					}
					break;
				
				case 2:
				default:
					$routedRequest['module'] = array_shift($request);
					$routedRequest['action'] = array_shift($request);
					
					if(!empty($request))
					{
						$routedRequest['params'] = $request;
					}
					break;
			}
			
			if($k = self::checkAppToRoute($routedRequest))
			{
				Response::getInstance()->status(301)->location(APP_BASE_URL.$k)->send();
			}
			
			/*foreach($routes as $k => $v)
			{
				if(array('module' => $routedRequest['module'], 'action' => $routedRequest['action']) == $v)
				{
					if(!empty($routedRequest['params']))
					{
						$k = str_replace(array_fill(0, sizeof($routedRequest['params']), '$'), $routedRequest['params'], str_replace(':all', '$', str_replace(':num', '$', $k)));
					}
					
					Response::getInstance()->status(301)->location(APP_BASE_URL.$k)->send();
				}
			}*/
		}
		
		if(empty($routedRequest['params']))
		{
			$routedRequest['params'] = array();
		}
		
		if($modified)
		{
			Response::getInstance()->status(301)->location(APP_BASE_URL.self::url($routedRequest))->send();
		}
		
		if(isset($routedRequest['prefix']))
		{
			$routedRequest['action'] = $routedRequest['prefix'].'_'.$routedRequest['action'];
		}
		
		if($saveInRegistry)
		{
			Registry::set('request', $routedRequest);
		}
		
		return $routedRequest;
	}
	
	// renvoie les routes chargées dans l'application
	public static function getRoutes() {
		return Registry::get('routes');
	}
	
	public static function checkRouteToApp($url = null)
	{
		$routes = Registry::get('routes');
		
		if(!empty($routes))
		{	
			if(isset($routes[$url]) && !strpos($url, ':'))
			{
				return $routes[$url];
			}
			
			foreach($routes as $k => $v)
			{
				if(strpos($k, ':') !== false)
				{
					$k = str_replace(':all', '(.+)', str_replace(':num', '([0-9]+)', $k));
					
					if(preg_match('#^'.$k.'$#', $url, $match))
					{
						$routedRequest = $v;
						
						if(sizeof($match) > 1)
						{
							array_shift($match);
							$routedRequest['params'] = $match;
						}
						
						return $routedRequest;
						break;
					}
				}
			}
		}
		
		return false;
	}
	
	public static function checkAppToRoute($url = array())
	{
		$routes = Registry::get('routes');
		
		if(!empty($routes))
		{
			$arr = array('module' => $url['module'], 'action' => $url['action']);
			
			foreach($routes as $k => $v)
			{
				if($arr === $v)
				{
					if(!empty($url['params']))
					{
						$k = str_replace(array_fill(0, sizeof($url['params']), '$'), $url['params'], str_replace(':all', '$', str_replace(':num', '$', $k)));
					}
					
					return $k;
				}
			}
		}
		
		return false;
	}
	
	public static function url($request = array())
	{
		$url = null;
		
		if(isset($request['prefix']) && in_array($request['prefix'], Registry::get('prefixes')))
		{
			$url .= $request['prefix'].'/';
		}
		
		if($request['module'] !== Registry::get('defaultModule'))
		{
			$url .= $request['module'];
		}
		
		$url .= '/'.$request['action'];
		
		if(!empty($request['params']))
		{
			$url .= '/'.implode('/', $request['params']);
		}
		
		return $url;
	}
}
?>