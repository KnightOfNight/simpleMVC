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
		# Trim any leading and trailing slashes.
		$route = self::trim($route);
		$route = self::reroute($route);

		$controller = '';
		$action = '';
		$query = array();

		if (! empty($route)) {

			$route_parts = explode ('/', $route);

#			Dbg::var_dump('route_parts', $route_parts);

			$controller = $route_parts[0];
			array_shift ($route_parts);
#			Dbg::var_dump('route_parts', $route_parts);

			if ( isset($route_parts[0]) AND (! empty($route_parts[0])) ) {
				$action = $route_parts[0];
			}
			array_shift ($route_parts);

#			Dbg::var_dump('route_parts', $route_parts);

			$route = implode('/', $route_parts);
			$route_parts = explode ('|', $route);

#			Dbg::var_dump('route', $route);
#			Dbg::var_dump('route_parts', $route_parts);

			# Build the $query array.
			foreach ( $route_parts as $unsplit_pair ) {
				$key_value = explode('=', $unsplit_pair);

#				Dbg::var_dump('key_value', $key_value);

				if ( count($key_value) == 2 ) {
					$key = $key_value[0];
					$value = $key_value[1];

					$query[$key] = $value;

#				} elseif ( count($key_value) == 1 ) {
#					$key = $key_value[0];
#					$query[$key] = TRUE;
				}
			}

#			Dbg::var_dump('query', $query);

#			exit;

		}


		# Check the controller.
		if ( (! $controller) AND (($controller = Config::get("dispatcher.controller.default")) === FALSE) ) {
			$error = "Invalid application route: no route specified via URL and no default controller found.";
			if ( Err::last() ) { $error .= "\n\nAdditional Error...\n\n" . Err::last(); }
			return($error);
		}

		if ( (($controllers = Config::get("dispatcher.controller")) === FALSE) OR (! array_key_exists($controller, $controllers)) ) {
			$error = "Invalid application route: controller '$controller' not found in configuration.";
			if ( Err::last() ) { $error .= "\n\nAdditional Error...\n\n" . Err::last(); }
			return($error);
		}

		if ( ! __class_exists($class = BaseController::toclass($controller)) ) {
			return("Invalid application route: controller not configured (class '$class' not found).");
		}


		# Check the action.
		if ( (! $action) AND (($action = Config::get("dispatcher.controller." . $controller . ".default_action")) === FALSE) ) {
			$error = "Invalid application route: no action specified via URL and no default action found for controller '$controller'.";
			if ( Err::last() ) { $error .= "\n\nAdditional Error...\n\n" . Err::last(); }
			return($error);
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
		$url = Config::get("application.base_path");

		if ($route === NULL) {
			$route = "";
		}

		$route = preg_replace('/^\/+/', "", $route);

		$url .= DS . $route;

		return ( htmlentities($url) );
	}


	/**
	* Immediately redirect the browser to the URL for the specified application route.
	*
	* @param string destination route
	* @param string GET parameters
	*/
	static function redirect ($route = NULL, $get = NULL) {
		header("Location: " . Route::toURL($route));
	}


	/**
	* Trim all leading and trailing slashes from a route.
	*
	* @param string route
	*/
	static function trim ($route = NULL) {
		if ( ! $route ) {
			return($route);
		}

		$route = preg_replace('/^\/+/', '', $route);
		$route = preg_replace('/\/+$/', '', $route);

		return($route);
	}


	/**
	* Change a route by applying any matching reroute rules from the application configuration.
	*/
	private static function reroute ($route) {
		$routes = Config::get("routes");

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
