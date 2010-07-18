<?php
namespace framework\libs\cache;
use framework\libs as F;

class Memcached extends CacheEngine
{
	protected $memcache = null;
	
	public function __construct()
	{
		$this->memcache = new \Memcached();
		$this->serversConf();
	}
	
	public function serversConf()
	{
		$servers = F\Registry::get('memcache.servers');
		if(sizeof($servers) > 1)
		{
			$this->memcache->addServers($servers);
		}
		else
		{
			$this->memcache->addServer($servers['host'], $servers['port']);
		}
	}
	
	public function end($key, $ttl = 0)
	{
		$content = ob_get_contents();
		ob_end_clean();
		$this->create($this->getKey($key), $content, $ttl);
	}
	
	public function getKey($key)
	{
		return md5($key);
	}
	
	public function get($key)
	{
        return $this->memcache->get($this->getKey($key));
	}
	
	public function create($key, $content, $ttl = 0)
	{
        return $this->memcache->add($this->getKey($key), $content, $ttl);
	}
	
	public function delete($key)
	{
		return $this->memcache->delete($this->getKey($key));
	}
}
?>