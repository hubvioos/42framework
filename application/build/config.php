<?php // this is an autogenerated file - do not edit (created Mon, 01 Nov 2010 13:20:42 +0100) - to regenerate, use compileConfig command in cli
defined('FRAMEWORK_DIR') or die('Invalid script access');

$config = array(
         'errorReporting' => '32767',
         'displayErrors' => '1',
         'defaultModule' => 'website',
         'defaultAction' => 'index',
         'defaultLayout' => false,
         'defaultCharset' => 'utf-8',
         'defaultLanguage' => 'fr',
         'defaultTimezone' => 'Europe/Paris',
         'viewExtension' => '.php',
         'siteUrl' => 'http://localhost/42framework/',
         'routes' => array('testroute' => array('module' => 'test','action' => 'qwerty','params' => array())),
         'historySize' => '2',
         'errorHandlerListeners' => array('0' => 'framework\errorHandler\listeners\Html'),
         'applicationFilters' => array('0' => 'framework\filters\appFilters\ApplicationFilter','1' => 'framework\filters\appFilters\SecurityFilter','2' => 'framework\filters\appFilters\ExecFilter'),
         'viewFilters' => array('0' => 'framework\filters\viewFilters\RenderFilter'),
         'events' => array()
      );
