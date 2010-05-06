<?php
namespace framework\libs;

class Registry
{
    public function __construct($data = array()){
    	if(!isset($_SESSION['registry']) || $data['envMode'] == 'dev')
    	{
			$_SESSION['registry'] = $data;
			$_SESSION['registry']['prefixesRegex'] = implode('|', $data['prefixes']);
		}
    }

    public static function getRegistry()
    {
    	return $_SESSION['registry'];
    }
    
    public static function get($key)
    {
        if(strpos($key, '.'))
        {
        	$key = explode('.', $key);
       		$taille = sizeof($key);
       		$value = null;
       		
       		for($i=0;$i<$taille;$i++)
       		{
       			if($i == 0)
       			{
       				$value = $_SESSION['registry'][$key[0]];
       			}
       			else
       			{
       				$value = $value[$key[$i]];
       			}
       		}
       		
       		return $value;
       	}
       	
        return isset($_SESSION['registry'][$key]) ? $_SESSION['registry'][$key] : null;
    }

    public static function set($key, $value)
    {
        $_SESSION['registry'][$key] = $value;
    }
}
?>