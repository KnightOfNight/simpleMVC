<?php

/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/

/**
* Singleton class that helps with AJAX.
*
* @package simpleMVC
*/
class AJAX {
	private function __construct () {}

	/**
	* Check to see if an incoming request was made via an AJAX call.
	* Produce a fatal error if it is not.
	*/
	static function check ($controller, $action) {
		if ( (! isset($_SERVER['HTTP_X_REQUESTED_WITH'])) OR ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') ) {
			Err::fatal("Unable to dispatch route '/$controller/$action'.\n\nExpecting AJAX request with header 'HTTP_X_REQUESTED_WITH'='XMLHttpRequest'.");
		}
	}

	function __destruct () {}
}
