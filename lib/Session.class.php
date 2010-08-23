<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
*
*/


/**
*
* Singleton class that contains various methods for handling a browsing session.
*
* @package MCS_MVC_API
*
*/
class Session {
	private function __construct () {}


	/**
	* Start a new session.
	*/
	static function start () {
		global $CONFIG;

		ini_set ("session.gc_probability", 1);
		ini_set ("session.gc_divisor", 1000);
		ini_set ("session.gc_maxlifetime", 43200);

		session_save_path (ROOT.DS."tmp".DS."sessions");

		session_name ("SESSION_ID");

		session_set_cookie_params (0, $CONFIG->getVal("application.base_path"),
			$CONFIG->getVal("application.domain"), TRUE, TRUE);

		session_start ();

		# session_start doesn't resend the session cookie if the session has
		# already started.

		if ( isset ($_COOKIE[session_name ()]) ) {
			if (Login::remembered()) {
				$timeout = time() + 31536000;
			} else {
				$timeout = 0;
			}

			setcookie (session_name (), session_id (), $timeout, $CONFIG->getVal("application.base_path"),
				$CONFIG->getVal("application.domain"), TRUE, TRUE);

#Debug::var_dump ("application.base_path", $CONFIG->getVal("application.base_path"));
#Debug::var_dump ("application.domain", $CONFIG->getVal("application.domain"));
		}
	}


	/**
	* Stop the current session.
	*/
	static function stop () {
		global $CONFIG;

		$_SESSION = array();

		setcookie (session_name (), "", time() - 86400,  $CONFIG->getVal("application.base_path"),
			$CONFIG->getVal("application.domain"),  TRUE, TRUE);

		session_destroy ();
	}
}
