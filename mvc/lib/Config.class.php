<?php


/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/


/**
* Provide access to the application's configuration.
*
* @package simpleMVC
*/
class Config {
	private $_values;


	/**
	* Create a new Config object by loading in the application's configuration.
	*
	* @return Config a new Config object
	*/
	function __construct () {
		$cfg_file = APP_CFGDIR.DS."config.json";

		if ( (! File::ready($cfg_file)) OR ( ($cfg_data = file_get_contents($cfg_file)) === FALSE ) ) {
			Err::fatal("Unable to read application configuration file '$cfg_file'.");
		}

		if ( ($this->_values = json_decode($cfg_data, TRUE)) === NULL ) {
			Err::fatal("Unable to parse application configuration, invalid JSON found.");
		}
	}


	/**
	* Get a particular configuration setting.
	*
	* @param string path to configuration variable in the configuration tree
	*
	* @return mixed value of the variable
	*/
	static function get ($path) {
		global $simpleMVC;

        if ( (! isset($simpleMVC['config'])) OR (! ($config = $simpleMVC['config']) instanceof Config) ) {
            Err::fatal("Config::" . __function__ . "() called before configuration loaded.");
        }

		return( $config->___get($path) );
	}


	/**
	* Get a particular configuration setting.
	*
	* @param string path to configuration variable in the configuration tree
	*
	* @return mixed value of the variable
	*/
	function ___get ($path) {
		$levels = explode (".", $path);

		$values = $this->_values;

		foreach ($levels as $level) {
			if (! isset ($values[$level])) {
				Err::set_last("Config->___get() - unable to find requested path '$path', failed on '$level'.");

				return(FALSE);
			}

			$values = $values[$level];
		}

		Err::set_last();

		return ($values);
	}


	function __destruct () {}
}
