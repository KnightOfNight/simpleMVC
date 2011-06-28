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
		global $CONFIG;

		if ( is_null($errmsg) ) {
			$errmsg = "undefined error message";
		}

		if ( isset($CONFIG) ) {
			$app_version = $CONFIG->getVal("framework.version");
			$app_copyright = $CONFIG->getVal("framework.copyright");
		} else {
			$app_version = "MCS MVC";
			$app_copyright = "&copy; MCS 'Net Productions";
		}

		$trace_info = debug_backtrace();

		$ob_level = ob_get_level();
		$ob_status = ob_get_status(TRUE);

#		Dbg::msg("ob_level = '$ob_level'");
#		Dbg::var_dump('ob_status', $ob_status);

		if ( $ob_level > 2 ) {
			ob_end_clean();
		}

		require("err_fatal.php");

		exit (0);
	}


	private function __destruct () {}
}
