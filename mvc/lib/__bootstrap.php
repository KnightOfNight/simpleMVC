<?php


/**
* Bootstrap the application framework.  Setup the database connection, start
* the session, setup logging, etc.  Finally, engage the controller for the
* requested route.
*
* This code is loaded by index.php from the public folder.
*
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/


# Stop the default output buffer and enable compression.
#
ob_end_clean();
ob_start( 'ob_gzhandler' );


/**
* Application framework global variable.
* @global array $simpleMVC
*/
$simpleMVC = array();
$simpleMVC['last_error'] = NULL;
$simpleMVC['start_time'] = microtime(TRUE);


# Setup the include path.
#
$LIBDIR = array(	MVC_LIBDIR,
					APP_LIBDIR,

					APP_MODELDIR,
					MVC_MODELDIR,

					APP_VIEWDIR,
					MVC_VIEWDIR,

					APP_CONTDIR,

					APP_FORMDIR,
);

$LIBDIR = implode(':', $LIBDIR);

set_include_path($LIBDIR);

$simpleMVC['libdir'] = $LIBDIR;

unset($LIBDIR);


# Setup the autoloader
#
require_once('__autoload.php');
require_once('__class_exists.php');


# Load any libraries or configuration files that the autoloader won't catch
#
require_once(CFGDIR.'/inflection.php');


# Load the framework and application configuration.
#
$simpleMVC['config'] = new Config;


# Setup the database connection.
#
$simpleMVC['database'] = new Database;
if ( Config::get('database') === FALSE ) {
	Err::fatal("Unable to read database configuration.\n\n" . Err::last());
}
$simpleMVC['database']->connect( Config::get('database') );


Session::start();

# Setup the logging.
#
$simpleMVC['log'] = new Log( (int) Config::get('framework.loglevel') );


# Setup error reporting.
#
error_reporting(E_ALL | E_STRICT);
ini_set('log_errors', 'ON');
ini_set('error_log', LOGDIR.'/error.log');

if ( Config::get('application.development') !== FALSE ) {
	ini_set('display_errors', 'ON');
} else {
	ini_set('display_errors', 'OFF');
}


# Get the route
#
$simpleMVC['requested_route'] = isset($_GET['route']) ? $_GET['route'] : NULL;
Log::msg(Log::INFO, "requested route = '" . $simpleMVC['requested_route'] . "'");


# Dispatch the route.
#
Dispatch::go($simpleMVC['requested_route']);


# Close up.
#
$simpleMVC['log']->close();
unset($simpleMVC['database']);
