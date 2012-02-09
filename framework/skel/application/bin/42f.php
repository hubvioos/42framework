#!/usr/bin/php -q
<?php
\define('AREA_NAME', 'web');
require \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'common' 
		. \DIRECTORY_SEPARATOR . 'constants.php';

$params = array();

if ($_SERVER['argc'] > 1)
{
	\array_shift($_SERVER['argv']);
	$params = $_SERVER['argv'];
}

$command = 'php ' . \AREA_DIR . \DIRECTORY_SEPARATOR . 'webroot' 
		. \DIRECTORY_SEPARATOR . 'index.php ' . \implode(' ', $params);

echo \exec($command);
echo "\n";