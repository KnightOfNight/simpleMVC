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

define ( 'MVC_LIBDIR',		ROOT.'/mvc/lib' );
define ( 'MVC_MODELDIR',	ROOT.'/mvc/models' );
define ( 'MVC_VIEWDIR',		ROOT.'/mvc/views' );
define ( 'MVC_LOGDIR',		ROOT.'/tmp/logs' );
define ( 'MVC_CACHEDIR',	ROOT.'/tmp/cache' );
define ( 'MVC_SESSIONDIR',	ROOT.'/tmp/sessions' );

define ( 'APP_LIBDIR',		ROOT.'/app/lib' );
define ( 'APP_MODELDIR',	ROOT.'/app/models' );
define ( 'APP_VIEWDIR',		ROOT.'/app/views' );
define ( 'APP_CONTDIR',		ROOT.'/app/controllers' );
define ( 'APP_FORMDIR',		ROOT.'/app/forms' );
define ( 'APP_CFGDIR',		ROOT.'/app/cfg' );

define ( 'TMPDIR',			ROOT.'/tmp/tmp' );

define ( 'IMGPATH', 'img' );
define ( 'IMGDIR', ROOT.'/public/'.IMGPATH );


require_once(MVC_LIBDIR.'/__bootstrap.php');

