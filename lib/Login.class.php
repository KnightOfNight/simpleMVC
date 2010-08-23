<?PHP

class Login {
	static function authorize ($username = NULL) {
		if (is_null ($username)) {
			Err::fatal ("no username specified");
		}

		$_SESSION["username"] = $username;
	}


	static function authorized () {
		if (isset ($_SESSION["username"])) {
			return ($_SESSION["username"]);
		} else {
			return (FALSE);
		}
	}


	static function remember () {
		$_SESSION["remember"] = 1;
	}


	static function remembered () {
		return (isset ($_SESSION["remember"]));
	}


}
