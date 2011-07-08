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

	if ( __class_exists($class) ) {
		$file = $class.".class.php";
		require_once($file);
	} else {
		Err::fatal("Autoload error: unable to load class '$class'.");
	}

}


