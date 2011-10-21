<?php

namespace framework\helpers;
class TestEvent {
	
	 public function __construct($arg1 = 'component container')
	 {
		echo $arg1;
	 }

	 //TEMP ONLY FOR TEST
	public static function OnMessage($arg1 = "valeur par default")
	{
		
		echo $arg1;	
	}
}
?>
