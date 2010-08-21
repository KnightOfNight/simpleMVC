<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
*/


/**
* Singleton class for help with debugging.
*
* @package MCS_MVC_API
*/
class Debug {
	private function __construct () {}


	/**
	* Dump the contents and structure of a variable to the screen inside pre
	* tags.
	* @param string variable name
	* @param mixed variable contents
	*/
	static function var_dump ($var_name, $var_contents) {
		printf ("<pre>\n");
		printf ("variable '%s'\n", $var_name);
		var_dump ($var_contents);
		printf ("</pre>\n");
	}
}
