<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*
*/


/**
* Check if a class exists as <class>.class.php and is loadable.
*
* @param string class name
*/
function __class_exists ($class) {
	global $simpleMVC;
	$libdir = $simpleMVC['libdir'];

	$class_file = $class.".class.php";

	foreach (explode (":", $libdir) as $dir) {
		$file = $dir.DS.$class_file;

		if ( file_exists($file) AND is_readable($file) ) {
			return(TRUE);
		}
	}

	return(FALSE);
}

