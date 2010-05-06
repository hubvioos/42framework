<?php
namespace framework\libs\cache;

abstract class CacheEngine
{
	protected $cacheKey = null;
	protected $name = null;
	
	public function __construct($cacheKey) {
		$this->name = trim($cacheKey, '/');
		$this->cacheKey = $this->getCacheKey($cacheKey);
		
	}
	
	public function getCacheKey($cacheKey)
	{
		if(!$cacheKey)
		{
			throw new Exception('L\'identifiant de cache n\'est pas une chaîne de caractères !');
		}
		
		return crc32($cacheKey);
	}
	
	public function start()
	{
		ob_start();	
	}
	
	public function end()
	{
		$content = ob_get_contents();
		ob_end_clean();
		$this->create($content);
	}
	
	/*abstract public function get();
	abstract public function delete();
	abstract public function create($content);
	abstract public function exists();*/
}
?>