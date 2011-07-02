<?php

/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*/

/**
* Singleton class that provides access to the authorization system.
*
* @package MCS_MVC_API
*/
class Auth {

	static function login ($username, $password) {
		if ( $username == 'ctg' AND $password == 'smeg' ) {
			$_SESSION['username'] = $username;
			return(TRUE);
		} else {
			return(FALSE);
		}
	}

	static function logout ($username, $password) {
		Session::stop();
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
