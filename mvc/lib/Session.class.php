<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*
*/


/**
*
* Singleton class that contains various methods for handling a browsing session.
*
* @package simpleMVC
*
*/
class Session {
	private function __construct () {}


	/**
	* Start a new session.
	*/
	static function start () {
		ini_set("session.gc_probability", 1);
		ini_set("session.gc_divisor", 1000);
		ini_set("session.gc_maxlifetime", 43200);

		session_save_path(MVC_SESSIONDIR);

		session_name("session");

		session_set_cookie_params(0, Config::get("application.base_path"), Config::get("application.domain"), TRUE, TRUE);

		session_start();

		# session_start() doesn't resend the session cookie if the session has
		# already been started, so if the session name is in the cookie, then
		# we need to resend it.

		if ( isset($_COOKIE[session_name()]) ) {
			if ( Auth::remembered() ) {
				$timeout = time() + 31536000;
			} else {
				$timeout = 0;
			}

			setcookie( session_name(), session_id(), $timeout, Config::get("application.base_path"), Config::get("application.domain"), TRUE, TRUE);
		}
	}


	/**
	* Get a session variable.
	*
	* @param string variable name
	* @return mixed variable value
	*/
	static function get ($name) {
		if ( isset($_SESSION[$name]) ) {
			return($_SESSION[$name]);
		} else {
			return(NULL);
		}
	}


	/**
	* Set a session variable.
	*
	* @param string variable name
	* @param mixed variable value
	*/
	static function set ($name, $value) {
		return( $_SESSION[$name] = $value );
	}


	/**
	* Stop the current session.
	*/
	static function stop () {
		$_SESSION = array();

		setcookie( session_name(), "", time() - 86400,  Config::get("application.base_path"), Config::get("application.domain"),  TRUE, TRUE);

		session_destroy();
	}
}
