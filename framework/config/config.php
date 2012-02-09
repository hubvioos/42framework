<?php 
/**
 * Copyright (C) 2011 - K√©vin O'NEILL, Fran√ßois KLINGLER - <contact@42framework.com>
 * 
 * 42framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * 42framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */

$config = array(
	'environment' => 'dev',
	'errorReporting' => E_ALL|E_STRICT,
	'displayErrors' => 1,
	'defaultModule' => 'website',
	'defaultAction' => 'index',
	'defaultLayout' => false,
	'defaultCharset' => 'utf-8',
	'defaultLanguage' => 'fr',
	'defaultTimezone' => 'Europe/Paris',
	'viewExtension' => '.php',
	'siteUrl' => 'http://localhost/',
	'routes' => array(),
	'historySize' => 2,
	'errorHandlerListeners' => array('framework\\errorHandler\\listeners\\Html'),
	'applicationFilters' => array(),
	'viewFilters' => array(), 
	'dbConnectionParams' => array(
		'driver' => 'pdo_sqlite',
		'path' => APP_DIR.DS.'database'.DS.'db.sqlite'
	),
	'logs' => array(
		'file' => \APP_DIR . \DIRECTORY_SEPARATOR . 'log'
	),
	'defaultCacheSysteml' => 'Apc',
	'cache' => array(
		'Apc' => array(
			'engine' => 'Apc',
			'prefix' => '42f_', //APP.			
			'duration' => 3600,
			'probability' => 100,
		),
		'File' => array(
			'engine' => 'File',
			'path' => '',
			'prefix' => '42f_',
			'lock' => false,
			'serialize' => true,
			'isWindows' => false
		),
		'Memcache' => array(
			'engine' => 'Memcache',
			'prefix' => '42f', //APP_DIR
			'servers' => array('127.0.0.1'),
			'duration' => 3600,
			'compress' => false
		),
		'Xcache' => array(
			'engine' => 'Xcache',
			'prefix' => '42f_', //APP_DIR
			'PHP_AUTH_USER' => 'user',
			'PHP_AUTH_PW' => 'password'
		)
	)
		
		
);
