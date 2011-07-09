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
define ('ROOTDIR', dirname( dirname(__FILE__) ) );

define ( 'MVC_LIBDIR',		ROOTDIR.'/mvc/lib' );
define ( 'MVC_MODELDIR',	ROOTDIR.'/mvc/models' );
define ( 'MVC_VIEWDIR',		ROOTDIR.'/mvc/views' );
define ( 'MVC_LOGDIR',		ROOTDIR.'/tmp/logs' );
define ( 'MVC_CACHEDIR',	ROOTDIR.'/tmp/cache' );
define ( 'MVC_SESSIONDIR',	ROOTDIR.'/tmp/sessions' );

define ( 'APP_LIBDIR',		ROOTDIR.'/app/lib' );
define ( 'APP_MODELDIR',	ROOTDIR.'/app/models' );
define ( 'APP_VIEWDIR',		ROOTDIR.'/app/views' );
define ( 'APP_CONTDIR',		ROOTDIR.'/app/controllers' );
define ( 'APP_FORMDIR',		ROOTDIR.'/app/forms' );
define ( 'APP_CFGDIR',		ROOTDIR.'/app/cfg' );

define ( 'TMPDIR',			ROOTDIR.'/tmp/tmp' );

define ( 'IMGPATH', 'img' );
define ( 'IMGDIR', ROOTDIR.'/public/'.IMGPATH );


require_once(MVC_LIBDIR.'/__bootstrap.php');

