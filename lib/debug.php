<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version 0.1.0
*
*/


/**
*
* Dump the contents of a variable nicely formatted so it can be read easily.
*
* @param string name of the variable to dump
* @param mixed variable to dump
*
*/
function my_var_dump ($name, $variable) {
	printf ("<pre>\n");
	printf ("name: %s\n", $name);
	var_dump ($variable);
	printf ("</pre>\n");
}
