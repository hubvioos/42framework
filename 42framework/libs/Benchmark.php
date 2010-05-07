<?php
namespace framework\libs;

class Benchmark {

	protected $timeMarker = array();
	protected $memoryMarker = array();

	public function __construct($time, $memory)
	{
		$this->timeMarker['appStartTime'] = $time;
		$this->memoryMarker['appStartMemoryUsage'] = $memory;
	}
	
	public function timeMark($name)
	{
		$this->timeMarker[$name] = microtime();
	}
	
	public function memoryMark($name)
	{
		$this->memoryMarker[$name] = memory_get_usage();
	}

	public function elapsedTime($point1 = '', $point2 = '', $decimals = 4)
	{
		if ($point1 == '')
		{
			$point1 = 'appStartTime';
		}

		if (!isset($this->timeMarker[$point1]))
		{
			return '';
		}

		if (!isset($this->timeMarker[$point2]))
		{
			$this->timeMarker[$point2] = microtime();
		}
	
		list($sm, $ss) = explode(' ', $this->timeMarker[$point1]);
		list($em, $es) = explode(' ', $this->timeMarker[$point2]);

		return number_format(($em + $es) - ($sm + $ss), $decimals);
	}
 	
	public function memoryUsage($name = '')
	{
		if($name == '')
		{
			return $this->convert(memory_get_usage());
		}
		else
		{
			if(isset($this->memoryMarker[$name]))
			{
				return $this->convert($this->memoryMarker[$name]);
			}
		}
		
		return null;
	}
	
	protected function convert($size)
 	{
    	$unit=array('b','kb','mb','gb','tb','pb');
    	return round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 	}
}
?>