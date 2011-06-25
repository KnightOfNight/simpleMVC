<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*/


/**
* Singleton class that provides access to filesystem-based cache.
*
* @package MCS_MVC_API
*/
class Cache {
	private function __construct () {}


	/**
	* Get or set a cache entry.
	*
	* @param string name of the cache entry
	* @param mixed value to set, if any
	* @return mixed value of the cache entry
	*/
	static function value ($cache_entry, $cache_value = NULL) {
		$cache_file = CACHEDIR . DS . sha1 ($cache_entry);

		if ( $cache_value === NULL ) {
			if ( ! file_exists($cache_file) ) {
				return(NULL);
			} elseif ( File::ready($cache_file) ) {
				if ( ($cache_value_enc = file_get_contents($cache_file)) === FALSE ) {
					Err::fatal( sprintf("unable to read cache file '%s'", $cache_file) );
				}
	
				$cache_value = unserialize(base64_decode($cache_value_enc));
			} else {
				Err::fatal( sprintf("Cache file '%s' is not readable.", $cache_file) );
			}
		} else {
			$cache_value_enc = base64_encode(serialize($cache_value));

			if ( File::ready(CACHEDIR, "w") ) {
				if ( file_put_contents($cache_file, $cache_value_enc) === FALSE ) {
					Err::fatal( sprintf("Unable to write cache file '%s'.", $cache_file) );
				}
			} else {
				Err::fatal( sprintf("Cache file '%s' is not writable.", $cache_file) );
			}
		}

		return($cache_value);
	}


	/**
	* Get the value of a particular cache entry.
	* @param string unique cache entry name
	* @return mixed value of the entry
	*/
	static function get ($cache_entry) {
		$cache_file = CACHEDIR . DS . sha1 ($cache_entry);

		if (! file_exists ($cache_file)) {
			return (NULL);
		} elseif (File::ready ($cache_file)) {
			if ( ($cache_data = file_get_contents ($cache_file)) === FALSE ) {
				Err::fatal (sprintf ("unable to read cache file '%s'", $cache_file));
			}

			return (unserialize (base64_decode ($cache_data)));
		} else {
			Err::fatal (sprintf ("unable to read cache file '%s'", $cache_file));
		}
	}


	/**
	* Set the value of a cache entry.
	* @param string unique cache entry name
	* @param mixed value of the entry
	*/
	static function set ($cache_entry, $cache_data) {
		$cache_file = CACHEDIR . DS . sha1 ($cache_entry);

		if (File::ready (CACHEDIR, "w")) {
			if (file_put_contents ($cache_file, base64_encode (serialize ($cache_data))) === FALSE) {
				Err::fatal (sprintf ("unable to write cache file '%s'", $cache_file));
			}
		} else {
			Err::fatal (sprintf ("cache directory '%s' is not writable", CACHEDIR));
		}
	}


	function __destruct () {}
}
