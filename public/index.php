<?PHP

define ("DS",			DIRECTORY_SEPARATOR);
define ("ROOT",			dirname (dirname (__FILE__)));
define ("LIBDIR",		ROOT.DS."lib" . ":" . ROOT.DS."app/controllers" . ":" . ROOT.DS."app/models" . ":" . ROOT.DS."app/lib");
define ("VIEWDIR",		ROOT.DS."app/views");

require_once (ROOT.DS."lib".DS."bootstrap.php");
