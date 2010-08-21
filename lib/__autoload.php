<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version 0.1.0
*
*/


/**
* Overwrite the builtin auto-loader, handle automatically loading any required
* classes.  Classes can be found in the colon-separated list of paths in
* "LIBDIR"  WTF IS LIBDIR!!!.
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
