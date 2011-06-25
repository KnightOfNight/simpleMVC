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

define ( 'VIEWDIR', ROOT.DS.'app'.DS.'views' );
define ( 'FORMDIR', ROOT.DS.'app'.DS.'forms' );
define ( 'CFGDIR', ROOT.DS.'app'.DS.'cfg' );

define ( 'LOGDIR', ROOT.DS.'tmp'.DS.'logs' );
define ( 'CACHEDIR', ROOT.DS.'tmp'.DS.'cache' );
define ( 'SESSIONDIR', ROOT.DS.'tmp'.DS.'sessions' );

define ( 'IMGPATH', 'img' );
define ( 'IMGDIR', ROOT.DS.'public'.DS.IMGPATH );

require_once(ROOT.DS.'lib'.DS.'__bootstrap.php');


