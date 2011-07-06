<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*/


/**
* Singleton class for handling application errors.
*
* @package MCS_MVC_API
*/
class Err {
	private function __construct () {}


	/**
	* Report a fatal application error to the browser and exit.
	*
	* @param string error message
	*/
	static function fatal ($errmsg) {
		if ( is_null($errmsg) ) {
			$errmsg = 'Unspecified error.';
		}

		if ( ($app_version = Config::get('framework.version')) === FALSE ) {
			$app_version = "MCS MVC";
		}
		if ( ($app_copyright = Config::get('framework.copyright')) === FALSE ) {
			$app_copyright = "&copy; MCS 'Net Productions";
		}

		$trace_info = debug_backtrace();

		while ( ($ob_level = ob_get_level()) > 1 ) {
			ob_end_clean();
		}

		require("err_fatal.php");

		exit(-1);
	}


	private function __destruct () {}
}
