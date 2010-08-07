<?PHP

class Config {
	private $_values;


	function __construct () {
		$cfg_file = ROOT.DS."cfg".DS."config.json";

		if ( (! File::ready ($cfg_file)) OR ( ($cfg_data = file_get_contents ($cfg_file)) === FALSE ) ) {
			Error::fatal (sprintf ("unable to read configuration file '%s'", $cfg_file));
		}

		if ( ($this->_values = json_decode ($cfg_data, TRUE)) === NULL ) {
			Error::fatal ("unable to parse configuration information, invalid json found");
		}
	}


	function getVal ($path = NULL) {
		if (is_null ($path)) {
			return ($this->_values);
		}

		$levels = explode (".", $path);

		$values = $this->_values;

		foreach ($levels as $level) {
			if (! isset ($values[$level])) {
				Error::fatal (sprintf ("attempted to traverse too far into configuration tree, path = '%s', failed on '%s'", $path, $level));
			}

			$values = $values[$level];
		}

		return ($values);
	}


	function __destruct () {}
}
