<?PHP


$start_time = microtime (TRUE);
$START_TIME = microtime (TRUE);


# setup autoloader
require_once (ROOT.DS."lib".DS."autoload.php");


# load any libraries or configuration files othat the autoloader won't catch
require_once (ROOT.DS."lib".DS."debug.php");
require_once (ROOT.DS."cfg".DS."inflection.php");


# Turn on output buffering, using gzip to compress output if supported by
# browser
#
ob_start ("ob_gzhandler");


# Load the program's configuration
#
$CONFIG = new Config;


# Start a session
#
Session::start();
$SESSION_ID = session_id ();


# Turn on logging
#
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


# Connect to the database
#
$db_cfg = $CONFIG->getVal("database");
$DB = new Database;
$DB->connect ($db_cfg["host"], $db_cfg["port"], $db_cfg["name"], $db_cfg["user"], $db_cfg["pass"]);


# Get the route
#
$route = isset ($_GET["route"]) ? $_GET["route"] : NULL;
$LOG->msg(Log::INFO, "initial route = '" . $route . "'");


# Dispatch the route.
#
Dispatch::go ($route);


# Close up.
unset ($DB);
unset ($LOG);
