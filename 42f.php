#!/usr/bin/php -q
<?php

$params = array();

if ($_SERVER['argc'] > 1)
{
	\array_shift($_SERVER['argv']);
	$params = $_SERVER['argv'];
}


echo \exec("php framework/skel/webroot/index.php " . \implode(" ", $params));
echo "\n";