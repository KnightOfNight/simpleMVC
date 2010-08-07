<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version v0.0.0
*
*/


/**
* Link::to - return the URL to a particularSome static functions that help create URL
*/
class Link {

	private function __construct () {}

	/**
	* Redirect the browser to the specified application route.
	*
	* @param string controller
	* @param string action
	* @param string query
	* @param string GET
	*/
	static function redirect ($controller = NULL, $action = NULL, $query = NULL, $get = NULL) {
		header ("Location: " . Link::to($controller, $action, $query, $get));
	}
	
	/**
	* Return the URL to the specified application route.
	*
	* @param string controller
	* @param string action
	* @param string query
	* @param string GET
	*/
	static function to ($controller = NULL, $action = NULL, $query = NULL, $get = NULL) {
		global $CONFIG;

		$url = $CONFIG->getVal("application.base_path");

		$url .= DS . Route::makeRoute($controller, $action, $query);

		if ($get) {
			$url .= "?" . $get;
		}

		return ($url);
	}
}
