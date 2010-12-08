<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*
*/


/**
* Overwrite the builtin auto-loader, handle automatically loading any required
* classes.
*
* @param string name of the class to load
*/
function __autoload ($class) {
	$file = $class.".class.php";

	require_once($file);
#
#	global $LIBDIR;
#
#	foreach (explode (":", $LIBDIR) as $dir) {
#		$file = $dir.DS.$class.".class.php";
#
#		if (file_exists ($file) AND is_readable ($file)) {
#			require_once ($file);
#			return (TRUE);
#		}
#	}
#
#	return (FALSE);
}
