<?php


/**
* The first code to run when the application starts up.  This sets the
* application's root directory and the oft-used "DS" (directory separator
* character for this system), and then loads the bootstrap code.  
*
* Though this file is stored in the "lib" directory, it is executed from the
* "public" directory.
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
*/


define ("DS", DIRECTORY_SEPARATOR);
define ("ROOT", dirname (dirname (__FILE__)));

printf ("root:%s\n", ROOT);

require_once (ROOT.DS."lib".DS."bootstrap.php");

