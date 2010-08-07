<?PHP

class File {
	static function ready ($file = NULL, $mode = "r") {
		if ($file === NULL) {
			Error::fatal ("no file name specified");
		}

		if ($mode === "r") {
			return ( file_exists ($file) AND is_readable ($file) );
		} elseif ($mode === "w") {
			return ( file_exists ($file) AND is_writable ($file) );
		} else {
			Error::fatal (sprintf ("invalid mode '%s'", $mode));
		}
	}
}
