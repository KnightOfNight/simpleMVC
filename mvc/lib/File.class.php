<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*
*/


/**
*
* Singleton class to help with filesystem I/O.
*
* @package simpleMVC
*
*/
class File {
	private function __construct () {}


	/**
	* Verify the accessibility of a file.
	*
	* @param string file name
	* @param string access mode: "r" or "w"
	* @return bool file is accessible
	*/
	static function ready ($file, $mode = "r") {
		if ($mode === "r") {
			return( file_exists ($file) AND is_readable ($file) );
		} elseif ($mode === "w") {
			return( file_exists ($file) AND is_writable ($file) );
		} else {
			Err::critical(sprintf ("invalid mode '%s'", $mode));
		}
	}


	private function __destruct () {}
}
