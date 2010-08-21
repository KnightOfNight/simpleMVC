<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version 0.1.0
*
*/


/**
*
* Handle application routes.
*
* @package MVCAPI
*
*/

class Route {
	private function __construct () {}

	/**
	* Validate route and fill in any missing pieces with default values.  Perform any reroutes needed.  Parse it into its component parts and return it as an array.
	*
	* @param string application route
	* @return array broken down application route
	*/
	static function parseRoute ($route) {
		global $CONFIG;

		$query = array ();

		# Parse the URL.  If it's NULL, then go to the default controller/action.
		if ($route === NULL) {
			$controller = $CONFIG->getVal("dispatcher.controller.default");

			$action = $CONFIG->getVal("dispatcher.controller." . $controller . ".default_action");

			if ( (! $controller) OR (! $action) ) {
				Error::fatal ("no route specified via HTTP and no default controller or action found");
			}
		} else {
			# Reroute if the route is an alias.
			$route = self::reRoute($route);

			$route_parts = explode ("/", $route);

			$controller = strtolower ($route_parts[0]);

			# Check the controller name to make sure it is configured and implemented.
			if (! class_exists ($controller_class = ucfirst ($controller) . "Controller")) {
				Error::fatal (sprintf ("route is invalid, '%s' is not a valid controller, class '%s' not found", $controller, $controller_class));
			} elseif (! array_key_exists ($controller, $CONFIG->getVal("dispatcher.controller"))) {
				Error::fatal (sprintf ("route is invalid, controller '%s' not found in configuration", $controller));
			}

			array_shift ($route_parts);

			# Check the action if set.
			if ( (! isset ($route_parts[0])) OR empty ($route_parts[0]) ) {
				# No action specified, get the default action for the specified
				# controller.
				$action = $CONFIG->getVal("dispatcher.controller." . $controller . ".default_action");
			} else {
				$action = strtolower ($route_parts[0]);

				# Check the action to make sure it is configured and implemented.
				$actions = array();
				$actions = array_merge ($actions, $CONFIG->getVal("dispatcher.controller." . $controller . ".external_actions"));
				$actions = array_merge ($actions, $CONFIG->getVal("dispatcher.controller." . $controller . ".internal_actions"));
				if (! method_exists ($controller_class, $action)) {
					Error::fatal (sprintf ("route is invalid, '%s' is not a valid action for controller '%s', method not found", $action, $controller));
				} elseif (! in_array ($action, $actions)) {
					Error::fatal (sprintf ("route is invalid, action '%s' not found in configuration for controller '%s'", $action, $controller));
				}

				array_shift ($route_parts);
			}

			# Get the query, if any.
			foreach ($route_parts as $route_part) {
				$route_part = trim ($route_part);

				if (! empty ($route_part)) {
					array_push ($query, $route_part);
				}
			}
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
	static function makeRoute ($controller, $action, $query) {
		if (! is_null ($controller)) {
			$route = $controller;
		}

		if (! is_null ($action)) {
			$route .= DS . $action;
		}

		if (is_array ($query)) {
			$route .= DS . implode ("/", $query);
		}

		return ($route);
	}


	/**
	* Return the URL for the specified application route.
	*
	* Useful in views when you need the URL to an application route for an
	* HREF.
	*
	* @param string controller
	* @param string action
	* @param string query
	* @param string GET
	* @return string URL to applicaton route
	*/
	static function URL ($controller = NULL, $action = NULL, $query = NULL, $get = NULL) {
		global $CONFIG;

		$url = $CONFIG->getVal("application.base_path");

		$url .= DS . Route::makeRoute($controller, $action, $query);

		if ($get) {
			$url .= "?" . $get;
		}

		return ($url);
	}


	/**
	* Redirect the browser to the URL for the specified application route.

	* @param string controller
	* @param string action
	* @param string query
	* @param string GET
	* @return string URL to applicaton route
	*/
	static function redirect ($controller = NULL, $action = NULL, $query = NULL, $get = NULL) {
		header ("Location: " . Route::URL($controller, $action, $query, $get));
	}


	private static function reRoute ($route) {
		global $CONFIG;

		$routes = $CONFIG->getVal("routes");

		foreach ($routes as $pattern => $new_route) {
			if (preg_match ($pattern, $route)) {
				return (preg_replace ($pattern, $new_route, $route));
			}
		}

		return ($route);
	}
}
