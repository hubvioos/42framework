<?php
namespace framework\libs;

// Classe se chargeant d'analyser la requête de l'utilisateur pour déterminer la page à charger
// Gère les routes afin de pouvoir déterminer des URL "SEO-friendly"
// Gère les redirections avec vérification des routes
class Router2 {

	// contient une instance de la classe (Singleton)
	private static $instance;
	
	// contient la requête de l'utilisateur
	protected static $request = array(
		'request' => null,
		'treatedRequest' => array()
	);
	
	// compte le nombre de fois où la requête a été modifiée
	protected static $modified = 0;
	
	protected static $modif = null;
	
	// contient les routes définies dans le fichier /config/config.php
	protected static $routes = array();
	
	// regex permettant de repérer les préfixes
	protected static $prefixesRegex = null;
	
	// mise en place du Singleton
	private function __construct() {
		self::$request['request'] = $_GET['url'];
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
		$j = 0;
		
		foreach($routes as $k => $v)
		{
			//on enregistre la route originale et le redirect
			self::$routes[$j]['route'] = $k;
			self::$routes[$j]['redirect'] = $v;
			
			// on détermine la regex de la route et du redirect
			$routeRegex = $k;
			
			if(isset($v['prefix']))
			{
				$redirectRegex = $v['prefix'].'/';
			}
			else
			{
				$redirectRegex = null;
			}
			
			$redirectRegex .= $v['module'].'/'.$v['action'];
			
			if(isset($v['params']))
			{
				foreach($v['params'] as $name => $regex)
				{
					$routeRegex = str_replace(':'.$name, '('.$regex.')', $routeRegex);
					$redirectRegex .= '/('.$regex.')';
				}
			}
			
			// et on les enregistre
			self::$routes[$j]['regexedRoute'] = $routeRegex;
			self::$routes[$j]['regexedRedirect'] = $redirectRegex;
			
			$j++;
		}
		
		self::$prefixesRegex = implode('|', Registry::get('prefixes'));
		
		return $this;
	}
	
	// analyse la requête de l'utilisateur et détermine le module, l'action et les éventuels paramètres
	public function analyse()
	{	
		if(self::$request['request'] == '')
		{
			self::$request['request'] = Registry::get('defaultModule').'/'.Registry::get('defaultAction');
		}
		
		if(strrchr(self::$request['request'], '/') == '/')
		{
			self::$modified++;
			self::$modif = '/';
			self::$request['request'] = rtrim(self::$request['request'], '/');
		}
		
		$test = self::checkUrl(self::$request['request'], 'redirect');
		
		if(self::$modif == 'route')
		{
			self::redirect($test, 301);
		}
		
		self::$request['request'] = self::checkUrl(self::$request['request'], 'route');
		
		if(self::$modif == '/')
		{
			self::redirect(rtrim(self::$request['request'], '/'), 301);
		}
		
		self::checkPrefixes();
		
		if(empty(self::$request['treatedRequest']['params']))
		{
			self::$request['treatedRequest']['params'] = array();
		}
		
		$request = explode('/', self::$request['request']);
		$taille = sizeof($request);
		if($taille > 0)
		{
			switch($taille)
			{	
				case 1:
					if($request[0] == Registry::get('defaultAction') || $request[0] == Registry::get('defaultModule'))
					{
						self::redirect('/', 301);
					}
					else
					{
						self::$request['treatedRequest']['module'] = array_shift($request);
						self::$request['treatedRequest']['action'] = Registry::get('defaultAction');
					}
					self::redirect(self::$request['treatedRequest'], 301);
					break;
					
				default:
					self::$request['treatedRequest']['module'] = array_shift($request);
					self::$request['treatedRequest']['action'] = array_shift($request);
					
					while(sizeof($request))
					{
						self::$request['treatedRequest']['params'][] = array_shift($request);
					}
					break;
			}
		}
		
		Registry::set('request', self::$request['treatedRequest']);
		
		return $this;
	}
	
	// renvoie les routes chargées dans l'application
	public static function getRoutes() {
		$params = array();
		
		foreach(self::$routes as $param)
		{
			$params[] = $param;
		}
		return $params;
	}
	
	// vérifie que l'URL passée en argument ne correspond pas à une route, et le cas échéant, la modifie en conséquence
	public static function checkRoutes($requestString = null, $sens = 'redirect') {
		switch($sens)
		{
			case 'route':
				$match = 'regexedRoute';
				$string = 'regexedRedirect';
				break;
				
			case 'redirect':
				$match = 'regexedRedirect';
				$string = 'route';
				break;
				
			default:
				throw new Exception('Sens de vérification des routes inconnu !');
				break;
		}
		
		if(!empty(self::$routes))
		{
			foreach(self::$routes as $r)
			{
				if(preg_match('#^'.$r[$match].'?#', $requestString, $params))
				{
					$requestString = $r[$string];
					
					if(!empty($params))
					{
						$i = 1;
						
						switch($sens)
						{
							case 'route':
								foreach($r['redirect']['params'] as $name => $regex)
								{
									$requestString = str_replace($regex, $params[$i], $requestString, $count);
							
									if($count > 0)
									{
										$i++;
									}
							
									if($i >= sizeof($params))
									{
										break;
									}
								}
								break;
								
							case 'redirect':
								foreach($r['redirect']['params'] as $name => $regex)
								{
									$requestString = str_replace(':'.$name, $params[$i], $requestString, $count);
							
									if($count > 0)
									{
										$i++;
									}
							
									if($i >= sizeof($params))
									{
										break;
									}
								}
								break;
						}
					}
					
					if($sens == 'redirect')
					{
						self::$modified++;
						self::$modif = 'route';
					}
					
					break;
				}
			}
		}
		
		return $requestString;
	}
	
	// vérifie les préfixes
	public static function checkPrefixes($array = array()) {
		if(!empty($array))
		{
			if(!empty($array['prefix']))
			{
				$prefix = $array['prefix'];
			}
			elseif(preg_match('#^('.self::$prefixesRegex.')_([a-zA-Z0-9_-]+)?#', $array['action'], $param))
			{
				$prefix = $param[1];
				$array = self::addAction($param[2], $array);
				self::$modified++;
			}
			else
			{
				$prefix = null;
			}
			
			$array = self::addPrefix($prefix, $array);
			
			return $array;
		}
		else
		{
			if(!empty(self::$request['treatedRequest']))
			{
				if(!empty(self::$request['treatedRequest']['prefix']))
				{
					$prefix = self::$request['treatedRequest']['prefix'];
				}
				elseif(preg_match('#^('.self::$prefixesRegex.')_([a-zA-Z0-9_-]+)?#', self::$request['treatedRequest']['action'], $param))
				{
					self::$request['treatedRequest']['prefix'] = $prefix = $param[1];
					self::$request['treatedRequest']['action'] = $param[2];
				}
				else
				{
					self::$request['treatedRequest']['prefix'] = $prefix = null;
				}
			}
			else
			{
				$request = explode('/', self::$request['request']);
				
				if(in_array($request[0], Registry::get('prefixes')))
				{
					self::$request['treatedRequest']['prefix'] = $prefix = array_shift($request);
					self::$request['request'] = $request;
				}
				else
				{
					self::$request['treatedRequest']['prefix'] = $prefix = null;
				}
			}
		}
		
		return $prefix;
	}
	
	public static function addPrefix($prefix, $array = array()) {
		$array['prefix'] = $prefix;
		
		return $array;
	}
	
	public static function addModule($module, $array = array()) {
		$array['module'] = $module;
		
		return $array;
	}
	
	public static function addAction($action, $array = array()) {
		$array['action'] = $action;
		
		return $array;
	}
	
	public static function addParam($param, $array = array()) {
		$array['params'][] = $param;
		
		return $array;
	}
	
	// renvoie une URL sous forme de chaîne à partir d'un tableau en vérifiant les routes au passage
	public static function url($params = array(), $routeSens = 'redirect') {
		$params = self::checkPrefixes($params);
		
		$prefix = $params['prefix'].'/';
		
		$base_url = $params['module'].'/'.$params['action'];
		
		$requestParams = implode('/', $params['params']);
		
		$url = self::checkRoutes(implode('/', array($base_url, $requestParams)), $routeSens);
		
		return ltrim($prefix.$url, '/');
	}
	
	// renvoie une URL sous forme de chaîne à partir d'une autre chaîne en vérifiant les routes et les préfixes
	public static function checkUrl($url = null, $sensRoute = 'redirect') {
		$explodeUrl = explode('/', $url);
		
		if(in_array($explodeUrl[0], Registry::get('prefixes')))
		{
			$prefix = array_shift($explodeUrl).'/';
			$url = implode('/', $explodeUrl);
		}
		else
		{
			$prefix = null;
		}
		
		$url = self::checkRoutes($url, $sensRoute);
		
		return ltrim($prefix.$url, '/');
	}
	
	// redirige vers l'URL donnée avec le code passé en second argument (301 et 302 pris en charge)
	public static function redirect($url, $errorCode = null) {
		if(is_array($url))
		{
			$url = self::url($url, 'redirect');
		}
		else
		{
			if(!preg_match('#^https?\://?#', $url))
			{
				if($url != '/')
				{
					$url = APP_BASE_URL.self::checkUrl($url, 'redirect');
				}
				else
				{
					$url = APP_BASE_URL;
				}
			}
		}
		
		self::$modified = 0;
		self::$modif = null;
		
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