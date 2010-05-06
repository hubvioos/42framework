<?php
namespace framework\libs;

class Cache
{
	public function getEngine($engine = null, $cacheKey = null, $ttl = false)
	{
		if($engine == null)
		{
			$engine = Registry::get('cacheEngine');
		}
		
		if($engine == 'file')
		{
			return new cache\FileCache($cacheKey, $ttl);
		}
		
		if($engine == 'memcache')
		{
			return new cache\Memcached();
		}
	}
}
?>