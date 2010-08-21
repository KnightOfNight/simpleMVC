<?php


/**
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version 0.1.0
*/


/**
* Singleton class for handling application errors.
*
* @package MVCAPI
*/
class Error {
	private function __construct () {}


	/**
	* Report a fatal application error to the browser and the log file,
	* display a stack trace, and exit.
	* @param string error message to report
	*/
	static function fatal ($errmsg) {
		global $CONFIG;

		if (is_null ($errmsg)) {
			$errmsg = "undefined error message";
		}

		if (isset ($CONFIG)) {
			$app_version = $CONFIG->getVal("framework.version");
			$app_copyright = $CONFIG->getVal("framework.copyright");
		} else {
			$app_version = "MCS MVC";
			$app_copyright = "&copy; MCS 'Net Productions";
		}

		$trace_info = debug_backtrace ();

		array_shift ($trace_info);
		array_shift ($trace_info);

		require (ROOT.DS."public".DS."views".DS."error_fatal.php");

		exit (0);
	}
}
