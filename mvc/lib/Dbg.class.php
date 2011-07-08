<?php


/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/


/**
* Singleton class that provides acess to debugging tools.
*
* @package simpleMVC
*/
class Dbg {
	private function __construct () {}


	/**
	* Dump the contents and structure of a variable to the screen.
	*
	* @param string variable name
	* @param mixed variable contents
	*/
	static function var_dump ($var_name, $var_contents) {
		printf ("<pre>\n");
		printf ("%s => ", $var_name);
		var_dump ($var_contents);
		printf ("</pre>\n");
	}


	/**
	* Print a message to the screen.
	*
	* @param string message
	*/
	static function msg ($message) {
		printf ("<pre>\n");
		printf ("%s\n", $message);
		printf ("</pre>\n");
	}


	function __destruct () {}
}
