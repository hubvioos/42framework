<?php
namespace framework\libs;

class ClassLoader
{
	protected $_namespace = null;
	
	protected $_folder = null;
	
	protected $_extension = '.php';
	
	protected $_separator = '\\';
	
	public function __construct($namespace, $folder, $separator = null, $extension = null)
	{
		$this->_namespace = $namespace;
		$this->_folder = $folder;
		
		if ($separator !== null)
		{
			$this->_separator = $separator;
		}
		if ($extension !== null)
		{
			$this->_extension = $extension;
		}
	}
	
	public function setSeparator($separator)
	{
		$this->_separator = $separator;
	}
	
	public function getSeparator()
	{
		return $this->_separator;
	}
	
	public function setNamespace($namespace)
	{
		$this->_namespace = $namespace;
	}
	
	public function getNamespace()
	{
		return $this->_namespace;
	}
	
	public function setFolder($folder)
	{
		$this->_folder = $folder;
	}
	
	public function getFolder()
	{
		return $this->_folder;
	}
	
	public function setExtension($extension)
	{
		$this->_extension = $extension;
	}
	
	public function getExtension()
	{
		return $this->_extension;
	}
	
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	}
	
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	}
	
	public function loadClass($className)
	{
		if (!$this->canLoadClass($className))
		{
			return false;
		}
		require $this->getClassPath($className);
		return true;
	}
	
	public function canLoadClass($className)
	{
		if ($this->_namespace !== null && strpos ($className, $this->_namespace.$this->_separator) !== 0)
		{
			return false;
		}
		return file_exists($this->getClassPath($className));
	}
	
	public function getClassPath($className)
	{
		$length = strlen($this->_namespace.$this->_separator);
		$classFile = str_replace($this->_separator, DIRECTORY_SEPARATOR, substr($className, $length)).$this->_extension;
		return $this->_folder.DIRECTORY_SEPARATOR.$classFile;
	}
}