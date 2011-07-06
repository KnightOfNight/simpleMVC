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
		$cfg_file = CFGDIR.DS."config.json";

		if ( (! File::ready($cfg_file)) OR ( ($cfg_data = file_get_contents($cfg_file)) === FALSE ) ) {
			Err::fatal("Unable to read application configuration file '$cfg_file'.");
		}

		if ( ($this->_values = json_decode($cfg_data, TRUE)) === NULL ) {
			Err::fatal("Unable to parse application configuration, invalid JSON found.");
		}
	}


	/**
	* Get a configuration value by path.
	*
	* @param string path to the variable in the configuration tree
	* @return mixed value
	*/
	static function get ($path) {
		global $simpleMVC_CONFIG;

		if ( ! isset($simpleMVC_CONFIG) ) {
			return(FALSE);
		}

		return( $simpleMVC_CONFIG->get_value($path) );
	}


	/**
	* Get a particular configuration setting.
	*
	* @param string path to configuration variable in the configuration tree
	* @return mixed value of the variable
	*/
	function get_value ($path) {
		$levels = explode (".", $path);

		$values = $this->_values;

		global $ERROR;

		foreach ($levels as $level) {
			if (! isset ($values[$level])) {
				$ERROR = "Config->get_value() - unable to find requested path '$path', failed on '$level'.";
				return(FALSE);
			}

			$values = $values[$level];
		}

		$ERROR = '';

		return ($values);
	}


	function __destruct () {}
}
