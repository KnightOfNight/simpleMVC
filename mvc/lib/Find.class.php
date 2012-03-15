<?php

/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/

/**
* Singleton class that helps find data in various sources, including POST, GET,
* and the framework's query array.
*
* @package simpleMVC
*/
class Find {
	private function __construct () {}

	/**
	* Find the specified key in the POST values.
	*/
	static function post ($key) {
		return( Find::any($_POST, $key ) );
	}

	/**
	* Find the specified key in the GET values.
	*/
	static function get ($key) {
		return( Find::any($_GET, $key ) );
	}

	/**
	* Find the specified key in the specified array.
	*/
	static function any ($array, $key) {
		if ( isset($array[$key]) ) {
			return($array[$key]);
		} else {
			return(NULL);
		}
	}

	function __destruct () {}
}

