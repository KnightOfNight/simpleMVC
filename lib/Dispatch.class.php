<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
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
	static function go ($passed_route, $external = TRUE) {
		global $DB;
		global $CONFIG;
		global $ERROR;

		$route = Route::parse($passed_route);

		if ( ! is_array($route) ) {
			Err::fatal("Dispatch error: route '$passed_route' could not be parsed.\n\n$route");
		}

		$controller = $route["controller"];
		$action = $route["action"];
		$query = $route["query"];

		# Check to make sure the requested action is allowed if external.
		if ($external) {
			$allowed_actions = $CONFIG->getVal("dispatcher.controller." . $controller . ".external_actions");

			if ( (! is_array($allowed_actions)) OR (! in_array($action, $allowed_actions)) ) {
				$errmsg = "Dispatch error: route '$passed_route' cannot be accessed by an external request.";

				if ( $ERROR ) {
					$errmsg .= "\n\nAdditional Error...\n\n" . $ERROR;
				}

				Err::fatal($errmsg);
			}
		}

		# create a new controller object
		$controller_class = $controller . "_controller";

		$dispatcher = new $controller_class();

		# call the three main parts of the controller
		$dispatcher->beforeAction($query);
		$retval = $dispatcher->$action($query);
		$dispatcher->afterAction($query);

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
		Dispatch::go($route, FALSE);
	}


	/**
	* Dispatch a route that returns data.  Usually called from a controller
	* action, e.g. to reuse code that gets a list of records that are needed by
	* various actions.
	*
	* @param string route
	*/
	static function get ($route) {
		return( Dispatch::go($route, FALSE) );
	}


	function __destruct () {}
}
