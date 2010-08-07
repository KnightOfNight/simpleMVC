<?PHP


$start_time = microtime (TRUE);


require_once (ROOT.DS."cfg".DS."inflection.php");


# Setup autoloader
#
function __autoload ($class) {
	foreach (explode (":", LIBDIR) as $dir) {
		$file = $dir.DS.$class.".class.php";

		if (file_exists ($file) AND is_readable ($file)) {
			require_once ($file);
			return (TRUE);
		}
	}

	return (FALSE);

	Error::fatal (sprintf ("unable to find class definition for '%s'", $class));
}


# Dispatch an URL
#
function dispatch ($route, $external = TRUE) {
	global $db;
	global $config;

# my_var_dump ("dispatch::route (before parseRoute)", $route);

	$route = Route::parseRoute($route);

# my_var_dump ("dispatch::route (after parseRoute)", $route);

	$controller = $route["controller"];
	$action = $route["action"];
	$query = $route["query"];

	# check to make sure the requested action is allowed for the given mode
	if ($external) {
		$allowed_actions = $config->getVal("dispatcher.controller." . $controller . ".external_actions");
	} else {
		$allowed_actions = $config->getVal("dispatcher.controller." . $controller . ".internal_actions");
	}

	if (! in_array ($action, $allowed_actions)) {
		Error::fatal (sprintf ("action '%s' is not valid when called from an %s source", $action, $external ? "external" : "internal"));
	}

	# create a new controller object
	$controller_class = ucfirst ($controller) . "Controller";
	$dispatcher = new $controller_class ($db);

	# call the three main parts of the controller
	if ($external) {
		$dispatcher->beforeAction($query);
	}

	$retval = $dispatcher->$action($query);

	if ($external) {
		$dispatcher->afterAction($query);
	}

	return ($retval);
}


# Include the output of an action within another action
#
function include_action_output ($controller, $action, $query) {
	dispatch (Route::makeRoute($controller, $action, $query), FALSE);
}


# Return the results of an action
#
function return_action_results ($controller, $action, $query) {
	return (dispatch (Route::makeRoute($controller, $action, $query), FALSE));
}


# Dump a variable with pre tags and the name of the variable displayed
#
function my_var_dump ($name, $value) {
	printf ("<pre>\n");
	printf ("name: %s\n", $name);
	var_dump ($value);
	printf ("</pre>\n");
}


# Script execution begins here


# Turn on output buffering, using gzip to compress output if supported by
# browser
#
ob_start ("ob_gzhandler");


# Load the program's configuration
#
$config = new Config;


# Turn on logging
#
$Log = new Log ((int) $config->getVal("framework.loglevel"));


# Setup error reporting
#
error_reporting (E_ALL | E_STRICT);

if ($config->getVal("application.development")) {
	ini_set ("display_errors", "ON");
} else {
	ini_set ("display_errors", "OFF");
	ini_set ("log_errors", "ON");
	ini_set ("error_log", ROOT . DS . "tmp" . DS . "logs" . DS . "error.log");
}


# Connect to the database
#
$db_cfg = $config->getVal("database");
$db = new Database;
$db->connect ($db_cfg["host"], $db_cfg["port"], $db_cfg["name"], $db_cfg["user"], $db_cfg["pass"]);


# Get the URL
#
$route = isset ($_GET["route"]) ? $_GET["route"] : NULL;

# my_var_dump ("dispatch::route (before dispatch)", $route);

# Dispatch the route.
#
dispatch ($route);
