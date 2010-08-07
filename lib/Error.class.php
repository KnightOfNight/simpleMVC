<?PHP

class Error {
	static function fatal ($errmsg) {
		if (is_null ($errmsg)) {
			$errmsg = "unknown error";
		}

		require (ROOT.DS."public".DS."error.php");

		exit (0);
	}
}
