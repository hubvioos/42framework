<?php
namespace framework\libs\cache;

class FileCache extends CacheEngine
{
	protected $path = null;
	protected $ttl = false;
	
	public function __construct($cacheKey, $ttl = false)
	{
		parent::__construct($cacheKey);
		$this->path = $this->getFilePath();
		$this->ttl = $ttl;
	}
	
	public function getFilePath($level = 3)
	{
		$decrement = 0;
		$path = null;
 
		for($i=0;$i<$level;$i++)
		{
			$decrement = $decrement - 2;
			$path .= sprintf("%02d".DS, substr('000000'.$this->cacheKey, $decrement, 2));
		}
		
		$path = APP.DS.'cache'.DS.$path;
		
		if(!file_exists($path))
		{
			mkdir($path, 0777, true);
		}
 
		return $path;
	}
	
	public function get()
	{
		if($this->exists())
        {
        	include($this->path.$this->name.'.php');
        	return $cache;
        }
        
        return false;
	}
	
	public function create($content)
	{
		$content = base64_encode(serialize($content));

        $content = str_replace(array('', '\'', "0"), array('\\', '\'', '0'), $content);

        $content = '<?php $cache = unserialize(base64_decode(\''.$content.'\')); ?>';
        
        $cacheFile = fopen($this->path.$this->name.'.php', 'w');
        $result = fwrite($cacheFile, $content);
        fclose($cacheFile);
        
        return $result;
	}
	
	public function delete()
	{
		return @unlink($this->path.$this->name.'.php');
	}
	
	public function exists()
	{
		if(is_file($this->path.$this->name.'.php'))
        {
        	if($this->ttl && (filemtime($this->path.$this->name.'.php') + $this->ttl) < time())
        	{
        		$this->delete();
        		return false;
        	}
        	
        	return true;
        }
        
        return false;
	}
}
?>