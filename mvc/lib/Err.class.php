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
	* Report a fatal application error to the browser and exit.
	*
	* @param string error message
	*/
	static function fatal ($message) {
		if ( is_null($message) ) {
			$message = 'Unspecified error.';
		}

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

		include("err_fatal.php");

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
