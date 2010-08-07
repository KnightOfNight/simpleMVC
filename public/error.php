<?PHP

	global $config;

	if (isset ($config)) {
		$version_info = $config->getVal("framework");
		$version_str = $version_info["name"] . " ver. " . $version_info["version"] . " - " . $version_info["copyright"];
	} else {
		$version_str = "&copy; MCS 'Net Productions 2010";
	}

	if (! isset ($errmsg)) {
		$errmsg = "an unknown error has occured";
	}

	$trace_info = debug_backtrace ();
	array_shift ($trace_info);
	array_shift ($trace_info);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Fatal Error</title>
<style type="text/css">
body { font-family: arial, sans-serif; }
body,div,span,form,input,table,tr,td,h1,h2,h3,h4,h5,br { margin: 0; padding: 0; } 
.header { margin-left: auto; margin-right: auto; padding: 0 5px 0 5px; width: 600px; font-weight: 900; font-size: 1em; background-color: #E0E0E0; }
.errmsg { margin-left: auto; margin-right: auto; padding: 5px; width: 600px; color: #FF0000; font-weight: 900; font-size: .75em; background-color: #D0D0D0; }
.copy { text-align: center; font-weight: 900; font-size: .75em; }
</style>
</head>

<body>

<br />

<div class="header">Error...</div>

<div class="errmsg"><?PHP echo nl2br ($errmsg) ?></div>

<?PHP if ($trace_info): ?>
<br />
<div class="header">Back trace...</div>
<div class="errmsg">

<?PHP foreach ($trace_info as $trace): ?>
<div><?PHP if (isset ($trace["class"])): ?><?PHP echo $trace["class"] ?>-&gt;<?PHP endif ?><?PHP echo $trace["function"] ?>()<?PHP if (isset ($trace["file"])): ?> called at <?PHP echo $trace["file"] ?>:<?PHP echo $trace["line"] ?><?PHP endif ?></div>
<?PHP endforeach ?>
</div>
<?PHP endif ?>


<br />

<div class="copy"><?PHP echo $version_str ?></div>

</body>

</html>
