<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version v0.0.0
*
*/


/**
*
* Singleton class for browser redirects.
*
* @package MVCAPI
*
*/
class Browser {
	private function __construct() {}

	/**
	* Redirect the browser to the specified application path.
	*
	* @param string controller
	* @param string action
	* @param string query
	* @param string GET
	*/
	static function redirect ($controller = NULL, $action = NULL, $query = NULL, $get = NULL) {
		header ("Location: " . Link::to($controller, $action, $query, $get));
	}
}
