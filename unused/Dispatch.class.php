<?PHP

class Dispatch {
	# Dispatch a route to the appropriate controller
	#
	static function go (Database $db, $url, $external = TRUE) {
		global $config;

		$route = Route::parseURL($url);

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
			Error::fatal (sprintf ("action '%s' is only valid when called from an %s source", $action, $external ? "external" : "internal"));
		}

		# create a new controller object
		$controller_class = ucfirst ($controller) . "Controller";
		$dispatch = new $controller_class ($db);

		# call the three main parts of the controller
		if ($external) {
			$dispatch->beforeAction ($query);
		}

		$retval = $dispatch->$action ($query);

		if ($external) {
			$dispatch->afterAction ($query);
		}

		return ($retval);
	}
}
