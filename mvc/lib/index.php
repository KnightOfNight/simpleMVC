<?php


/**
* This is the first code to run when the application starts up.
*
* This file is stored in the "lib" directory but it is executed from a copy in
* the "public" directory.
*
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/


/**
*
*/
define ('DS', DIRECTORY_SEPARATOR);
define ('ROOT', dirname( dirname(__FILE__) ) );


define ( 'MVC_LIBDIR', ROOT.'/mvc/lib' );
define ( 'MVC_MODELDIR', ROOT.'/mvc/models' );
define ( 'MVC_VIEWDIR', ROOT.'/mvc/views' );

define ( 'APP_LIBDIR', ROOT.'/app/lib' );
define ( 'APP_MODELDIR', ROOT.'/app/models' );
define ( 'APP_VIEWDIR', ROOT.'/app/views' );

define ( 'APP_CONTDIR', ROOT.'/app/controllers' );

define ( 'APP_FORMDIR', ROOT.'/app/forms' );


define ( 'CFGDIR', ROOT.'/app/cfg' );

define ( 'TEMPDIR', ROOT.'/tmp' );

define ( 'TMPDIR', TEMPDIR.'/tmp' );
define ( 'LOGDIR', TEMPDIR.'/logs' );
define ( 'CACHEDIR', TEMPDIR.'/cache' );
define ( 'SESSIONDIR', TEMPDIR.'/sessions' );

define ( 'IMGPATH', 'img' );
define ( 'IMGDIR', ROOT.'/public/'.IMGPATH );


require_once(MVC_LIBDIR.'/__bootstrap.php');

