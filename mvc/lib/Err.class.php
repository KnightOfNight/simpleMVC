<?php


/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/


/**
* Singleton class for handling application errors.
*
* @package simpleMVC
*/
class Err {
	private function __construct () {}


	/**
	* Report a fatal application error to the browser and the application log
	* and exit.
	*
	* @param string error message
	*/
	static function fatal ($message = "Unspecified error") {

		Log::msg(Log::ERROR, $message);

		self::___printexit("err_fatal.php", $message);
	}


	/**
	* Report a critical application error that is so bad it can't even be
	* logged, usually called very early and/or very low in the framework.
	*
	* @param string error message
	*/
	static function critical ($message = "Unspecified error") {

		self::___printexit("err_fatal.php", $message);
	}


	/**
	* Print the passed error message and exit.  Only used internally by the
	* class.
	*
	* @param string view file
	* @param string message
	*/
	static function ___printexit ($view, $message) {
		if ( ($app_version = Config::get('framework.version')) === FALSE ) {
			$app_version = "simpleMVC";
		}
		if ( ($app_copyright = Config::get('framework.copyright')) === FALSE ) {
			$app_copyright = "&copy; MCS 'Net Productions";
		}

		$trace_info = debug_backtrace();

		while ( ($ob_level = ob_get_level()) > 1 ) {
			ob_end_clean();
		}

		include($view);

		exit(-1);
	}


	/**
	* Set the last application error message.
	*
	* @param string error message
	*/
	static function set_last ($message = NULL) {
		global $simpleMVC;

		$simpleMVC['last_error'] = $message;
	}


	/**
	* Get the last application error.
	* 
	* @return string error message
	*/
	static function last () {
		global $simpleMVC;

		return($simpleMVC['last_error']);
	}


	private function __destruct () {}
}
