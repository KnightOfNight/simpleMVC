<?php

/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/

/**
* Singleton class that provides access to the authorization system.
*
* @package simpleMVC
*/
class Auth {

	static function login ($username, $password) {
		if ( ($username == 'ctg' AND $password == 'smeg') OR ($username == 'ras' AND $password == 'ugh4269') ) {
			$_SESSION['username'] = $username;
			return(TRUE);

		} else {
			return('Invalid credentials specified.');
		}
	}

	static function logout () {
		$_SESSION['username'] = NULL;
		$_SESSION['remember'] = FALSE;
		# Session::stop();
	}

	static function remember () {
		$_SESSION['remember'] = TRUE;
	}

	static function remembered () {
		return( isset($_SESSION['remember']) );
	}

	static function authorized () {
		if (isset ($_SESSION['username'])) {
			return ($_SESSION['username']);
		} else {
			return (FALSE);
		}
	}

#	static function authorize ($username = NULL) {
#		if (is_null ($username)) {
#			Err::fatal ("no username specified");
#		}
#
#		$_SESSION["username"] = $username;
#	}

}
