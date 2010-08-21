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
* @version 0.1.0
*/


/**
* Global variable: script start time in decimal seconds.
* @global float $START_TIME
*/
$START_TIME = microtime (TRUE);


define ( "LIBDIR",
		# General application framework.
		ROOT.DS."lib" . ":" . 

		# Application-specific controllers.
		ROOT.DS."app/controllers" . ":" . 

		# Application-specific models.
		ROOT.DS."app/models" . ":" . 

		# Application-specific misc. classes, addon libraries, etc.
		ROOT.DS."app/lib" );

define ( "VIEWDIR", ROOT.DS."app/views" );


# Setup the autoloader
require_once (ROOT.DS."lib".DS."__autoload.php");


# Load any libraries or configuration files othat the autoloader won't catch
# require_once (ROOT.DS."lib".DS."debug.php");
require_once (ROOT.DS."cfg".DS."inflection.php");


# Turn on output buffering, using gzip to compress output if supported by
# browser
#
ob_start ("ob_gzhandler");


/**
* Global variable: current application configuration
* @global array $CONFIG
*/
$CONFIG = new Config;


Session::start();


/**
* Global variable: current session ID
* @global string $SESSION_ID
*/
$SESSION_ID = session_id ();


/**
* Global variable: log file handler
* @global Log $LOG
*/
$LOG = new Log ((int) $CONFIG->getVal("framework.loglevel"));


# Setup error reporting
#
error_reporting (E_ALL | E_STRICT);

if ($CONFIG->getVal("application.development")) {
	ini_set ("display_errors", "ON");
} else {
	ini_set ("display_errors", "OFF");
	ini_set ("log_errors", "ON");
	ini_set ("error_log", ROOT.DS."tmp".DS."logs".DS."error.log");
}


/**
* Global variable: database connection
* @global Database $DB
*/
$DB = new Database;
$cfg = $CONFIG->getVal("database");
$DB->connect ($cfg["host"], $cfg["port"], $cfg["name"], $cfg["user"], $cfg["pass"]);


# Get the route
#
$route = isset ($_GET["route"]) ? $_GET["route"] : NULL;
$LOG->msg(Log::INFO, "initial route = '" . $route . "'");


# Dispatch the route.
#
Dispatch::go ($route);


# Close up.
#
unset ($DB);
unset ($LOG);
