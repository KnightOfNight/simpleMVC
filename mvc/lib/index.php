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
* @package simpleMVC
* @version 0.5.0
*/


define ('DS', DIRECTORY_SEPARATOR);
define ('ROOT', dirname (dirname (__FILE__)));

define ( 'VIEWDIR', ROOT.'/app/views' );
define ( 'FORMDIR', ROOT.'/app/forms' );
define ( 'CFGDIR', ROOT.'/app/cfg' );

define ( 'TEMPDIR', ROOT.'/tmp' );

define ( 'TMPDIR', TEMPDIR.'/tmp' );
define ( 'LOGDIR', TEMPDIR.'/logs' );
define ( 'CACHEDIR', TEMPDIR.'/cache' );
define ( 'SESSIONDIR', TEMPDIR.'/sessions' );

define ( 'IMGPATH', 'img' );
define ( 'IMGDIR', ROOT.'/public/'.IMGPATH );

require_once(ROOT.'/lib/__bootstrap.php');


