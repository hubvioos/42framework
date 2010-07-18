<?php
namespace framework\libs;

// Classe se chargeant d'analyser la requête de l'utilisateur pour déterminer la page à charger
// Gère les routes afin de pouvoir déterminer des URL "SEO-friendly"
// Gère les redirections avec vérification des routes
class Router {

	// contient une instance de la classe (Singleton)
	private static $instance;
	
	// contient la requête de l'utilisateur
	protected $request = null;
	
	// contient les routes définies dans le fichier /config/config.php
	protected static $routes = array();
	
	// regex permettant de repérer les préfixes
	protected static $prefixesRegex = null;
	
	// paramètres de la requête analysée
	protected static $params = array(
		'module' => null,
		'action' => null,
		'suffix' => null
	);
	
	// mise en place du Singleton
	private function __construct() {
		$this->request = $_GET['url'];
	}
	
	private function __clone() {}
	
	// fournie une instance de la classe
	public static function getInstance ()
    {
        if (!isset (self::$instance))
        {
        	self::$instance = new self();
        }
            
        return self::$instance;
    }
	
	// charge les routes définies dans /config/config.php
	public function loadConfig($routes = array()) {		
		if($routeConfig = Cache::getCache(Cache::nameCacheFile(array('name' => 'routes', 'type' => 'data'))))
		{
			self::$routes = $routeConfig;
		}
		else
		{
			$j = 0;
		
			foreach($routes as $k => $v)
			{
				//on enregistre la route originale et le redirect
				self::$routes[$j]['route'] = $k;
				self::$routes[$j]['redirect'] = $v;
			
				// on détermine la regex de la route et du redirect
				$route = explode('/', $k);
			
				$routeRegex = null;
				$redirectRegex = $v['module'].'/'.$v['action'];
			
				for($i=0;$i<sizeof($route);$i++)
				{
					if(!preg_match('#^\:([a-zA-Z0-9_-]+)?#', $route[$i], $match[$i]))
					{
						if($routeRegex == null)
						{
							$routeRegex = $route[$i];
						}
						else
						{
							$routeRegex .= '/'.$route[$i];
						}
					}
					else
					{
						if($match[$i][1] != 'suffix')
						{
							$redirectRegex .= '/('.$v[$match[$i][1]].')';
						}
						
						if($routeRegex == null)
						{
							if($match[$i][1] != 'suffix')
							{
								$routeRegex = '('.$v[$match[$i][1]].')';
							}
							else
							{
								$routeRegex = '[('.$v[$match[$i][1]].')]?';
							}
						}
						else
						{
							if($match[$i][1] != 'suffix')
							{
								$routeRegex .= '/('.$v[$match[$i][1]].')';
							}
							else
							{
								$routeRegex .= '([/'.$v[$match[$i][1]].']?)';
							}
						}
					}
				}
			
				// et on les enregistre
				self::$routes[$j]['regexedRoute'] = $routeRegex;
				self::$routes[$j]['regexedRedirect'] = $redirectRegex;
			
				$j++;
			}
			
			Cache::createCache(Cache::nameCacheFile(array('name' => 'routes', 'type' => 'data')), self::$routes);
		}
		
		if($config = Cache::getCache(Cache::nameCacheFile(array('name' => 'prefixesRegex', 'type' => 'data'))))
		{
			self::$prefixesRegex = $config;
		}
		else
		{
			self::$prefixesRegex = implode('|', Registry::get('prefixes'));
			Cache::createCache(Cache::nameCacheFile(array('name' => 'prefixesRegex', 'type' => 'data')), self::$prefixesRegex);
		}
		
		return $this;
	}
	
	// analyse la requête de l'utilisateur et détermine le module, l'action et les éventuels paramètres
	public function analyse()
	{	
		$prefixes = Registry::get('prefixes');
		
		if(!empty($prefixes))
		{
			foreach($prefixes as $z)
			{
				if($this->request == $z)
				{
					self::redirect($this->request.'/', 301);
				}
			}
		}
		
		$prefix = null;
		
		// vérification du préfixe
		$explodeRequest = explode('/', $this->request);
		$texplodeRequest = count($explodeRequest);
		
		if(in_array($explodeRequest[0], Registry::get('prefixes')))
		{
			$prefix = $explodeRequest[0].'_';
			$this->request = null;
			for($i=1;$i<$texplodeRequest;$i++)
			{
				if($i != 1)
				{
					$this->request .= '/';
				}
				
				$this->request .= $explodeRequest[$i];
			}
		}
		
		if(strrchr($this->request, '/') == '/')
		{
			$this->request = substr($this->request, 0, strlen($this->request)-1);
			self::redirect($prefix.$this->request, 301);
		}
		
		if($this->request == Registry::get('defaultModule').'/'.Registry::get('defaultAction'))
		{
			if(!isset($prefix))
			{
				self::redirect('/', 301);
			}
			else
			{
				$explodePrefix = explode('_', $prefix);
				self::redirect($explodePrefix[0].'/', 301);
			}
		}
		
		// on vérifie les routes si il y en a
		if(!empty(self::$routes))
		{
			foreach(self::$routes as $r)
			{
				// si la requête correspond au redirect, on redirige vers la route correspondante (pour éviter le duplicate content)
				if(preg_match('#^'.$r['regexedRedirect'].'?#', $this->request))
				{
					// la requête correspond à un redirect, on la redirige donc pour éviter le duplicate content
					// (self::redirect() se charge de la modification de la requête pour avoir la bonne url)
					if(isset($prefix))
					{
						$explodePrefix = explode('_', $prefix);
						self::redirect($explodePrefix[0].'/'.$this->request, 301);
					}
					else
					{
						self::redirect($this->request, 301);
					}
				}
				else
				{
					// si la requête correspond à la route, on la traite en conséquence 
					if(preg_match('#^'.$r['regexedRoute'].'?#', $this->request, $params))
					{
						$route = explode('/', $r['route']);
						$troute = count($route);
						$redirect = $r['redirect'];
						$redirect['action'] = $prefix.$redirect['action'];
						
						for($i=0, $p=1;$i<$troute && $p<sizeof($params);$i++)
						{
							if(preg_match('#^\:([a-zA-Z0-9_-]+)?#', $route[$i], $param))
							{
								if($param[1] == 'suffix')
								{
									$suffix = explode('/', $params[$p]);
									$redirect['suffix'] = $suffix[1];
								}
								else
								{
									$q = $p-1;
									$redirect['params'][$q] = $params[$p];
								}
								
								$p++;
							}
						}
					
						$request = array();
						$request = $redirect;
						break;
					}
				}
			}
		}
		
		// on traite la requête pour déterminer le module et l'action à appeler
		if(!isset($request))
		{
			if($this->request != null)
			{
				$part = explode('/', $this->request);
				$tpart = count($part);
			
				$request = array();
			
				switch($tpart)
				{
					case 1:
						if($part[0] == Registry::get('defaultAction'))
						{
							$request['module'] = Registry::get('defaultModule');
							$request['action'] = $prefix.Registry::get('defaultAction');
							self::redirect('/', 301);
						}
						else
						{
							$request['module'] = $part[0];
							$request['action'] = $prefix.Registry::get('defaultAction');
						}
						break;
					default:
						$request['module'] = $part[0];
						if($part[1] == '')
						{
							$request['action'] = $prefix.Registry::get('defaultAction');
						}
						else
						{
							$request['action'] = $prefix.$part[1];
						}
						
						for($i=2, $j=0;$i<$tpart;$i++, $j++)
						{
							$request['params'][$j] = $part[$i];
						}
						break;
				}
			}
			else
			{
				$request = array();
				$request['module'] = Registry::get('defaultModule');
				$request['action'] = $prefix.Registry::get('defaultAction');
			}
		}
		
		if(empty($request['params']))
		{
			$request['params'] = array();
		}
		
		Registry::set('request', $request);
		
		return $this;
	}
	
	// renvoie les routes chargées dans l'application
	public static function getRoutes() {
		return self::$routes;
	}
	
	// vérifie que l'URL passée en argument ne correspond pas à une route, et le cas échéant, la modifie en conséquence
	public static function checkRoutes($url = null) {
		if(!empty(self::$routes)) {
			foreach(self::$routes as $r)
			{
				if(preg_match('#^'.$r['regexedRedirect'].'([/[a-zA-Z0-9_-]+]?)?#', $url, $params))
				{
					$route = explode('/', $r['route']);
					$troute = count($route);
					
					$requestString = null;
					
					for($i=0, $p=1;$i<$troute && $p<sizeof($params);$i++)
					{
						if($i != 0)
						{
							$requestString .= '/';
						}
						
						if(preg_match('#^\:([a-zA-Z0-9_-]+)?#', $route[$i], $param))
						{
							if($param[1] == 'suffix')
							{
								$explodeSuffix = explode('/', $params[$p]);
								$requestString .= $explodeSuffix[1];
								$p++;
							}
							else
							{
								$requestString .= $params[$p];
								$p++;
							}
						}
						else
						{
							$requestString .= $route[$i];
						}
					}
					
					$explode = explode('/', $requestString);
					$texplode = count($explode);
					$requestString = null;
					for($i=0;$i<$texplode;$i++)
					{
						if(!empty($explode[$i]))
						{
							if($i != 0)
							{
								$requestString .= '/';
							}
							
							$requestString .= $explode[$i];
						}
					}
					
					$url = $requestString;
					break;
				}
			}
		}
		
		return $url;
	}
	
	// renvoie une URL sous forme de chaîne à partir d'un tableau en vérifiant les routes au passage
	public static function url($params = array()) {
		if(isset($params['prefix']))
		{
			$prefix = $params['prefix'].'/';
		}
		elseif(preg_match('#^('.self::$prefixesRegex.')_([a-zA-Z0-9_-]+)?#', $params['action'], $param))
		{
			$prefix = $param[1].'/';
			$params['action'] = $param[2];
		}
		else
		{
			$prefix = null;
		}
		
		if(isset($params['suffix']))
		{
			$suffix = '/'.$params['suffix'];
		}
		else
		{
			$suffix = null;
		}
		
		$url = $params['module'].'/'.$params['action'];
		
		$taille = count($params['params']);
		
		if($taille > 1)
		{
			for($i=0;$i<$taille;$i++)
			{
				$url .= '/'.$params['params'][$i];
			}
		}
		
		$url = self::checkRoutes($url);
		
		return $prefix.$url.$suffix;
	}
	
	// renvoie une URL sous forme de chaîne à partir d'une autre chaîne en vérifiant les routes et les préfixes
	public static function checkUrl($url = null) {
		$explodeUrl = explode('/', $url);
		$texplodeUrl = count($explodeUrl);
		
		if(in_array($explodeUrl[0], Registry::get('prefixes')))
		{
			$prefix = $explodeUrl[0].'/';
			$url = null;
			for($i=1;$i<$texplodeUrl;$i++)
			{
				if($i != 1)
				{
					$url .= '/';
				}
				
				$url .= $explodeUrl[$i];
			}
		}
		else
		{
			$prefix = null;
		}
		
		$url = self::checkRoutes($url);
		
		if(strrchr($url, '/') == '/')
		{
			$url = substr($url, 0, strlen($url)-1);
		}
		
		return $prefix.$url;
	}
	
	// redirige vers l'URL donnée avec le code passé en second argument (301 et 302 pris en charge)
	public static function redirect($url, $errorCode = null) {
		if(is_array($url))
		{
			$url = self::url($url);
		}
		else
		{
			if(!preg_match('#^https?\://?#', $url))
			{
				if($url != '/')
				{
					$url = APP_BASE_URL.self::checkUrl($url);
				}
				else
				{
					$url = APP_BASE_URL;
				}
			}
		}
		
		if($errorCode == null)
		{
			header('Location: '.$url);
		}
		else
		{
			switch($errorCode)
			{
				case 301:
					header('Location: '.$url, true, 301);
					break;
				case 302:
					header('Location: '.$url, true, 302);
					break;
				default:
					header('Location: '.$url);
					break;
			}
		}
	}
}
?>