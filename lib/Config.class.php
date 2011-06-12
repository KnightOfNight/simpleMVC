<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*/


/**
* Provide access to the application's configuration.
*
* @package MCS_MVC_API
*/
class Config {
	private $_values;


	/**
	* Create a new Config object by loading in the application's configuration.
	* @return Config a new Config object
	*/
	function __construct () {
		$cfg_file = ROOT.DS."app/cfg".DS."config.json";

		if ( (! File::ready ($cfg_file)) OR ( ($cfg_data = file_get_contents ($cfg_file)) === FALSE ) ) {
			Err::fatal (sprintf ("unable to read configuration file '%s'", $cfg_file));
		}

		if ( ($this->_values = json_decode ($cfg_data, TRUE)) === NULL ) {
			Err::fatal ("unable to parse configuration information, invalid json found");
		}
	}


	/**
	* Get a particular configuration setting.
	* @param string path to configuration variable in the configuration tree
	* @return mixed value of the variable
	*/
	function getVal ($path) {
		$levels = explode (".", $path);

		$values = $this->_values;

		foreach ($levels as $level) {
			if (! isset ($values[$level])) {
				Err::fatal (sprintf ("attempted to traverse too far into configuration tree, path = '%s', failed on '%s'", $path, $level));
			}

			$values = $values[$level];
		}

		return ($values);
	}


	function __destruct () {}
}
