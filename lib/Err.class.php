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
		$trace_details = '';

		if ( $trace_info ) {

			$trace = array_shift($trace_info);
			$file = $trace['file'];
			$line = $trace['line'];

			$trace = array_shift($trace_info);
			$class = ( isset($trace['class']) ) ? $trace['class'] . '-&gt;' : '';
			$function = $trace['function'];

			$trace_details .= "$class$function() terminated at $file:$line\n\n";

			$file = $trace['file'];
			$line = $trace['line'];

			$trace_details .= "$class$function() called at $file:$line\n";

			foreach ( $trace_info as $trace ) {
				$file = $trace['file'];
				$line = $trace['line'];
				$class = ( isset($trace['class']) ) ? $trace['class'] . '-&gt;' : '';
				$function = $trace['function'];

				$trace_details .= "$class$function() called at $file:$line\n";
			}
		}

		require("err_fatal.php");

		exit (0);
	}


	private function __destruct () {}
}
