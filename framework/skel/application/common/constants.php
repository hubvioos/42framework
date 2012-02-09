<?php
/**
 * Defines the execution mode (dev, stage, prod or custom)
 */
\define('ENV', 'dev');

/**
 * Defines the path to the application folder
 */
\define('APP_DIR', \dirname(__DIR__));

/**
 * Defines the path to the framework folder
 */
//\define('FRAMEWORK_DIR', \dirname(\APP_DIR) . \DIRECTORY_SEPARATOR . 'framework');
\define('FRAMEWORK_DIR', \dirname(\dirname(\APP_DIR)));

/**
 * Defines the path to the modules folder
 */
\define('MODULES_DIR', \APP_DIR . \DIRECTORY_SEPARATOR . 'modules');

/**
 * Definess the path to the vendors folder
 */
\define('VENDORS_DIR', \APP_DIR . \DIRECTORY_SEPARATOR . 'vendors');

/**
 * Defines the path to the folder containing log files
 */
\define('LOGS_DIR', \APP_DIR . \DIRECTORY_SEPARATOR . 'log');

/**
 * Definess the path to the area folder
 */
\define('AREA_DIR', \APP_DIR . \DIRECTORY_SEPARATOR . 'areas' . \DIRECTORY_SEPARATOR . \AREA_NAME);

/**
 * Defines the path to the folder containing build files
 */
\define('BUILD_DIR', \AREA_DIR . \DIRECTORY_SEPARATOR . 'build');

\define('DS', \DIRECTORY_SEPARATOR);