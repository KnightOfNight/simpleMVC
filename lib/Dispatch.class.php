<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
*/


/**
* Singleton class that handles dispatching requests to the appropriate
* controller.
*
* @package MCS_MVC_API
*/
class Dispatch {
	private function __construct () {}


	/**
	* Dispatch a route.  If the route comes from a web browser, it is an
	* external route, otherwise it is an internal route.
	*
	* @param string route
	* @param bool TRUE => external route
	* @return array optional data returned from the route processing
	*/
	static function go ($route, $external = TRUE) {
		global $DB;
		global $CONFIG;

		$route = Route::parseRoute($route);

		$controller = $route["controller"];
		$action = $route["action"];
		$query = $route["query"];

		# check to make sure the requested action is allowed for the given mode
		if ($external) {
			$allowed_actions = $CONFIG->getVal("dispatcher.controller." . $controller . ".external_actions");
		} else {
			$allowed_actions = $CONFIG->getVal("dispatcher.controller." . $controller . ".internal_actions");
		}

		if (! in_array ($action, $allowed_actions)) {
			Error::fatal (sprintf ("action '%s' is not valid when called from an %s source", $action, $external ? "external" : "internal"));
		}

		# create a new controller object
		$controller_class = ucfirst ($controller) . "Controller";

		$dispatcher = new $controller_class ();

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


	/**
	* Dispatch an internal route that produces output.  Usually called from
	* within a view, e.g. to allow reuse of code that gets a list of records,
	* said code could be called from multiple pages.
	*
	* @param string route
	*/
	static function show ($route) {
		Dispatch::go ($route, FALSE);
	}


	# Return the results of an action
	#
#	function return_action_results ($controller, $action, $query) {
#		return (dispatch (Route::makeRoute($controller, $action, $query), FALSE));
#	}


	function __destruct () {}
}
