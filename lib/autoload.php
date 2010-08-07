<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version v0.0.0
*
*/


/**
* Setup an __autoload function to handle automatically loading all required
* classes.
* @param string name of the class to load
*/
function __autoload ($class) {
	foreach (explode (":", LIBDIR) as $dir) {
		$file = $dir.DS.$class.".class.php";

		if (file_exists ($file) AND is_readable ($file)) {
			require_once ($file);
			return (TRUE);
		}
	}

	return (FALSE);
}
