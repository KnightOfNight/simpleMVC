<?php


/**
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version 0.1.0
*/


/**
* Mange cache contents.
* @package MVCAPI
*/
class Cache {
	/**
	* Get the stored value of a particular cache entry.
	* @param string unique entry name
	* @return mixed value of the entry
	*/
	static function get ($entry_name) {
		$cache_dir = ROOT . DS . "tmp" . DS . "cache";

		$cache_file = $cache_dir . DS . sha1 ($entry_name);

		if (! file_exists ($cache_file)) {
			return (NULL);
		} elseif (File::ready ($cache_file)) {
			if ( ($cache_data = file_get_contents ($cache_file)) === FALSE ) {
				Error::fatal (sprintf ("unable to read cache file '%s'", $cache_file));
			}

			return (unserialize (base64_decode ($cache_data)));
		} else {
			Error::fatal (sprintf ("unable to read cache file '%s'", $cache_file));
		}
	}

	/**
	* Set the stored value of a cache entry.
	* @param string unique entry name
	* @param mixed value of the entry
	*/
	static function set ($entry_name, $cache_data) {
		$cache_dir = ROOT . DS . "tmp" . DS . "cache";

		$cache_file = $cache_dir . DS . sha1 ($entry_name);

		if (File::ready ($cache_dir, "w")) {
			if (file_put_contents ($cache_file, base64_encode (serialize ($cache_data))) === FALSE) {
				Error::fatal (sprintf ("unable to write cache file '%s'", $cache_file));
			}
		} else {
			Error::fatal (sprintf ("cache directory '%s' is not writable", $cache_dir));
		}
	}
}
