<?php
/** 
 * @author Noah Goodrich
 * @date May 7, 2011
 * @package Gacela
 * @brief
 * 
*/

namespace framework\Gacela;

class Gacela extends \framework\core\FrameworkObject
{

	protected static $_instance;

	protected $_cache = null;

	protected $_cacheEnabled = false;

	protected $_namespaces = array();

	protected $_sources = array();

	protected $_cached = array();

    protected function __construct()
    {
        if (\ENV == \framework\core\Core::DEV)
		{
			$loader = new \framework\libs\ClassLoader('Gacela', \FRAMEWORK_DIR.\DIRECTORY_SEPARATOR.'Gacela');
			$loader->register();
		}
		
		$this->init();
    }
	
	public function init ()
	{
		$datasources = $this->getConfig('datasources');
		
		foreach ($datasources->toArray() as $name => $config)
		{
			$this->registerDataSource($name, $config['type'], $config);
		}
	}

	/**
	 * @static
	 * @return Gacela
	 */
	public static function instance()
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new Gacela();
		}

		return self::$_instance;
	}

	/**
	 * @param  $key
	 * @param null $object
	 * @return object|bool
	 */
	public function cache($key, $object = null, $replace = false)
	{
		if(!$this->_cacheEnabled) {
			if(is_null($object)) {
				if(isset($this->_cached[$key])) {
					return $this->_cached[$key];
				}

				return false;
			} else {
				$this->_cached[$key] = $object;

				return true;
			}
		} else {
			if(is_null($object)) {
				return $this->_cache->get($key);
			} else {
				if($replace) {
					return $this->_cache->replace($key, $object);
				} else {
					return $this->_cache->set($key, $object);
				}
			}
		}
	}

	/**
	 * @param  Memcache|array $servers
	 * @return Gacela
	 */
	public function enableCache($servers)
	{
		if($servers instanceof Memcache) {
			$this->_cache = $servers;
		} elseif(is_array($servers)) {
			$this->_cache = new Memcache;

			foreach($servers as $server) {
				$this->_cache->addServer($server[0], $server[1]);
			}
		}

		$this->_cacheEnabled = true;

		return $this;
	}

	/**
	 * @throws Exception
	 * @param  $name
	 * @return Gacela\DataSource\DataSource
	 */
	public function getDataSource($name)
	{
		if(!isset($this->_sources[$name])) {
			throw new Exception("Invalid Data Source {$name} Referenced");
		}

		return $this->_sources[$name];
	}

	public function incrementCache($key)
	{
		if(!$this->cacheEnabled()) {
			$this->_cached[$key]++;
		} else {
			$this->_cache->increment($key);
		}
	}

	/**
	 * @throws Exception
	 * @param  string $name Relative name of the Mapper to load. For example, if the absolute name of the mapper was \App\Mapper\User, you would pass 'user' in as the argument
	 * @return Gacela\Mapper\Mapper
	 */
	public function loadMapper($name)
	{
		$name = ucfirst($name);

		$cached = $this->cache('mapper_'.$name);

		if ($cached === false || is_null($cached)) {
			$class = "\\Gacela\\Mapper\\" . $name;

			$cached = new $class;

			$this->cache('mapper_'.$name, $cached);
		}

		return $cached;
	}

	public function cacheEnabled()
	{
		return $this->_cacheEnabled;
	}

	/**
	 * @param  string $name Name by which the DataSource can later be referenced in Mappers and when directly accessing the registered DataSource.
	 * @param  string $type Type of DataSource (database, *service, *xml ) *coming soon
	 * @param  array $config Configuration arguments required by the DataSource
	 * @return Gacela
	 */
	public function registerDataSource($name, $type, $config)
	{
		$config['name'] = $name;
		$config['type'] = $type;
		
		$class = "\\Gacela\\DataSource\\".ucfirst($type);
		
		$this->_sources[$name] = new $class($config);
		
		return $this;
	}
}
