<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*
*/


/**
*
* Singleton class that handles application routes.
*
* @package MCS_MVC_API
*
*/

class Route {
	private function __construct () {}


	/**
	* Validate route and fill in any missing pieces with default values.
	* Perform any reroutes needed.  Parse it into its component parts and
	* return it as an array.
	*
	* @param string application route
	* @return mixed array verified application route OR string error message
	*/
	static function parse ($route) {
		global $CONFIG;
		global $ERROR;

#		Dbg::var_dump("route", $route);

		# Trim any leading slashes.
		$route = preg_replace('/^\/+/', '', $route);
#		Dbg::var_dump("route", $route);
		$route = self::reroute($route);

#		Dbg::var_dump("route", $route);

		$controller = '';
		$action = '';
		$query = array ();

		if (! empty($route)) {

			$route_parts = explode ("/", $route);

			$controller = $route_parts[0];

			array_shift ($route_parts);

			if ( isset($route_parts[0]) AND (! empty($route_parts[0])) ) {
				$action = $route_parts[0];
			}

			# Get the query, if any.
			array_shift ($route_parts);

			foreach ($route_parts as $route_part) {
				$route_part = trim ($route_part);

				if (! empty ($route_part)) {
					array_push ($query, $route_part);
				}
			}
		}


		# Check the controller.
		if ( (! $controller) AND (($controller = $CONFIG->getVal("dispatcher.controller.default")) === FALSE) ) {
			return("Invalid application route: no route specified via URL and no default controller found.\n\n$ERROR");
		}

		if ( (($controllers = $CONFIG->getVal("dispatcher.controller")) === FALSE) OR (! array_key_exists($controller, $controllers)) ) {
			$error = "Invalid application route: controller '$controller' not found in configuration.";

			if ( $ERROR ) {
				$error .= "\n\nAdditional Error\n" . $ERROR;
			}

			return($error);
		}

		if ( ! __class_exists($class = BaseController::toclass($controller)) ) {
			return("Invalid application route: controller not configured (class '$class' not found).");
		}


		# Check the action.
		if ( (! $action) AND (($action = $CONFIG->getVal("dispatcher.controller." . $controller . ".default_action")) === FALSE) ) {
			return("Invalid application route: no action specified via URL and no default action found for controller '$controller'.\n\n$ERROR");
		}

		if (! method_exists ($class, $action)) {
			return("Invalid application route: action '$action' not configured for controller '$controller' (method '$action' not found in class '$class').");
		}


		return (array ("controller" => $controller, "action" => $action, "query" => $query));

	}


	/**
	* Make an application route from its component parts.
	*
	* @param string controller
	* @param string action
	* @param string query
	* @return string application route
	*/
#	static function makeRoute ($controller, $action, $query) {
#		if (! is_null ($controller)) {
#			$route = $controller;
#		}
#
#		if (! is_null ($action)) {
#			$route .= DS . $action;
#		}
#
#		if (is_array ($query)) {
#			$route .= DS . implode ("/", $query);
#		}
#
#		return ($route);
#	}


	/**
	* Return the URL for the specified application route.
	*
	* Useful in views when you need the URL to an application route for an
	* HREF.
	*
	* @param string destination route
	* @param string GET parameters
	*
	* @return string URL to applicaton route
	*/
	static function toURL ($route, $get = NULL) {
		global $CONFIG;
		$url = $CONFIG->getVal("application.base_path");

		if ($route === NULL) {
			$route = "";
		}

		$route = preg_replace('/^\/+/', "", $route);

		$url .= DS . $route;

		if ($get) {
			$url .= "?" . $get;
		}

		return ($url);
	}


	/**
	* Immediately redirect the browser to the URL for the specified application route.
	*
	* @param string destination route
	* @param string GET parameters
	*/
	static function redirect ($route = NULL, $get = NULL) {
		header("Location: " . Route::toURL($route, $get));
	}


	/**
	* Change a route by applying any matching reroute rules from the application configuration.
	*/
	private static function reroute ($route) {
		global $CONFIG;

		$routes = $CONFIG->getVal("routes");

		foreach ($routes as $pattern => $new_route) {
			if ( preg_match($pattern, $route) ) {
				$match = preg_replace($pattern, $new_route, $route);
				$route = preg_replace('/^\/+/', '', $match);
				break;
			}
		}

		return ($route);
	}


	private function __destruct () {}
}
