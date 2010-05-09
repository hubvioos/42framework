<?php
namespace framework\utils;

// La classe Benchmark permet d'effectuer des contrôles de performance.
// Elle gère le temps d'exécution total de l'application ainsi que sa consommation de mémoire.
// Elle permet également de "poser" des marques, et de connaître le temps d'exécution ou la consommation de mémoire entre 2 marques distinctes.
class Benchmark {

	// ces 2 array() contiennent l'ensemble des marques "posées" durant l'exécution
	protected static $timeMarker = array();
	protected static $memoryMarker = array();

	// init permet d'enregistrer le temps et la mémoire de départ dans les attributs de la classe
	protected static function init()
	{
		if(!isset(self::$timeMarker['appStartTime']) && !isset(self::$memoryMarker['appStartMemoryUsage']))
		{
			self::$timeMarker['appStartTime'] = APP_START_MICROTIME;
			self::$memoryMarker['appStartMemoryUsage'] = APP_START_MEMORY_USAGE;
		}
	}
	
	// timeMark permet de "poser" une marque de temps
	public static function timeMark($name)
	{
		self::$timeMarker[$name] = microtime();
	}
	
	// memoryMark permet de "poser" une marque de mémoire
	public static function memoryMark($name)
	{
		self::$memoryMarker[$name] = memory_get_usage();
	}

	// elapsedTime calcule le temps écoulé entre 2 marques, passées en paramètres
	// si on ne passe aucun paramètre à la méthode, elle renverra le temps entre le début de l'exécution et le moment de son appel
	public static function elapsedTime($point1 = '', $point2 = '', $decimals = 4)
	{
		self::init();
		
		if ($point1 == '')
		{
			$point1 = 'appStartTime';
		}

		if (!isset(self::$timeMarker[$point1]))
		{
			return '';
		}

		if (!isset(self::$timeMarker[$point2]))
		{
			self::$timeMarker[$point2] = microtime();
		}
	
		list($sm, $ss) = explode(' ', self::$timeMarker[$point1]);
		list($em, $es) = explode(' ', self::$timeMarker[$point2]);

		return number_format(($em + $es) - ($sm + $ss), $decimals);
	}
 	
	// memoryUsage renvoie la consommation de mémoire, convertie avec la méthode convert
	// si on ne passe pas de paramètre à la méthode, elle renvoie la mémoire actuelle
	public static function memoryUsage($name = '')
	{
		self::init();
		
		if($name == '')
		{
			return self::convert(memory_get_usage());
		}
		else
		{
			if(isset(self::$memoryMarker[$name]))
			{
				return self::convert(self::$memoryMarker[$name]);
			}
		}
		
		return null;
	}
	
	// convert convertie une marque de mémoire en chaîne de caractères avec une unité appropriée
	protected static function convert($size)
 	{
    	$unit=array('b','kb','mb','gb','tb','pb');
    	return round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 	}
} // fin de Benchmark
?>