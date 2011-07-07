<?php


/**
* The first code to run when the application starts up.  This sets the
* application's root directory and the oft-used "DS" (directory separator
* character for this system), and then loads the bootstrap code.  
*
* This file is stored in the "lib" directory but it is executed from a copy in
* the "public" directory.
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*/


define ('DS', DIRECTORY_SEPARATOR);
define ('ROOT', dirname (dirname (__FILE__)));

define ( 'VIEWDIR', ROOT.'/app/views' );
define ( 'FORMDIR', ROOT.'/app/forms' );
define ( 'CFGDIR', ROOT.'/app/cfg' );

define ( 'TMPDIR', ROOT.'/tmp/tmp' );

define ( 'LOGDIR', TMPDIR.'/logs' );
define ( 'CACHEDIR', TMPDIR.'/cache' );
define ( 'SESSIONDIR', TMPDIR.'/sessions' );

define ( 'IMGPATH', 'img' );
define ( 'IMGDIR', ROOT.'/public/'.IMGPATH );

require_once(ROOT.'/lib/__bootstrap.php');


