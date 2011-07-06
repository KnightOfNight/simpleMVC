<?php


/**
* Bootstrap the application framework.  Setup the database connection, start
* the session, setup logging, etc., and finally, engage the controller for the
* requested route.
*
* This code is loaded by index.php from the public folder.
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*/


# Stop the default output buffer and enable compression.
ob_end_clean();
ob_start( 'ob_gzhandler' );


/**
* Script start time.
* @global float $START_TIME
*/
$START_TIME = microtime(TRUE);


$LIBDIR = array(	ROOT.DS.'lib',
					ROOT.DS.'lib/mvc/controllers',
					ROOT.DS.'lib/mvc/models',
					ROOT.DS.'lib/mvc/views',
					ROOT.DS.'app/controllers',
					ROOT.DS.'app/models',
					VIEWDIR,
					FORMDIR,
					ROOT.DS.'app/lib',
);
/**
* Application library path.
* @global array $LIBDIR
*/
$LIBDIR = implode(':', $LIBDIR);


set_include_path($LIBDIR);


# Setup the autoloader
require_once('__autoload.php');
require_once('__class_exists.php');


# Load any libraries or configuration files that the autoloader won't catch
require_once(CFGDIR.DS.'inflection.php');


/**
* Current application configuration.
* @global object $simpleMVC_CONFIG
*/
$simpleMVC_CONFIG = new Config;


/**
* Database connection.
* @global Database $DB
*/
$DB = new Database;
if ( Config::get('database') === FALSE ) {
	Err::fatal("Unable to read database configuration.\n\n" . $ERROR);
}
$DB->connect( Config::get('database') );


Session::start();


/**
* Log file handler.
* @global object $simpleMVC_LOG
*/
$simpleMVC_LOG = new Log( (int) Config::get('framework.loglevel') );


# Setup error reporting
#
error_reporting(E_ALL | E_STRICT);
ini_set('log_errors', 'ON');
ini_set('error_log', LOGDIR.DS.'error.log');

if ( Config::get('application.development') !== FALSE ) {
	ini_set('display_errors', 'ON');
} else {
	ini_set('display_errors', 'OFF');
}


# Get the route
#
/**
* Initially requested application route.
* @global string $simpleMVC_ROUTE
*/
$simpleMVC_ROUTE = isset($_GET['route']) ? $_GET['route'] : NULL;
Log::msg(Log::INFO, "initial route = '$simpleMVC_ROUTE'");


# Dispatch the route.
#
Dispatch::go($simpleMVC_ROUTE);


# Close up.
#
$simpleMVC_LOG->close();
unset($DB);
