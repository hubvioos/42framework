<?php
namespace framework\libs;

class Security
{
	protected $key;
	protected $algo;

	public function __construct($key, $algo = MCRYPT_RIJNDAEL_256){
		$this->key = substr($key, 0, mcrypt_get_key_size($algo, MCRYPT_MODE_ECB));
		$this->algo = $algo;
	}

	public function encrypt($data)
	{
		if(!$data)
		{
			return false;
		}
		
		//Optional Part, only necessary if you use other encryption mode than ECB
		$ivSize = mcrypt_get_iv_size($this->algo, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
		
		$crypt = mcrypt_encrypt($this->algo, $this->key, $data, MCRYPT_MODE_ECB, $iv);
		return trim(base64_encode($crypt));
	}
	
	public function decrypt($data)
	{
		if(!$data)
		{
			return false;
		}
		
		$crypt = base64_decode($data);
		
		//Optional Part, only necessary if you use other encryption mode than ECB
		$ivSize = mcrypt_get_iv_size($this->algo, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
		
		$decrypt = mcrypt_decrypt($this->algo, $this->key, $crypt, MCRYPT_MODE_ECB, $iv);
		return trim($decrypt);
	
	}
}



/*class Security
{	
	
	
	protected static $instance = null;
	
	private function __construct() {
		$GLOBALS = $this->sanitizeArray($GLOBALS);
		$_GET = $this->sanitizeArray($_GET);
		$_POST = $this->sanitizeArray($_POST);
		$_FILES = $this->sanitizeArray($_FILES);
		$_SESSION = $this->sanitizeArray($_SESSION);
		$_COOKIE = $this->sanitizeArray($_COOKIE);
		$_SERVER = $this->sanitizeArray($_SERVER);
		$_ENV = $this->sanitizeArray($_ENV);
		$_REQUEST = $this->sanitizeArray($_REQUEST);
	}
	
	private function __clone() {}
	
	public static function getInstance() {
		if(empty(self::$instance))
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function sanitizeArray($arr = array()) {
		$r = array();
		
		foreach($arr as $k)
		{
			if(is_array($k))
			{
				
			}
			else
			{
				
			}
		}
		
		return $r;
	}
}*/
?>