<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*
*/


/**
* Check if a class exists as <class>.class.php and is loadable.
*
* @param string class name
*/
function __class_exists ($class) {

	global $LIBDIR;

	$class_file = $class.".class.php";

	foreach (explode (":", $LIBDIR) as $dir) {
		$file = $dir.DS.$class_file;

		if ( file_exists($file) AND is_readable($file) ) {
			return(TRUE);
		}
	}

	return(FALSE);

}


