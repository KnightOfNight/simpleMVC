<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>MCS MVC: Fatal Error</title>

<style type="text/css">
body { font-family: sans-serif; margin: 0px; padding: 0px; }
.header { text-align: center; font-weight: 900; font-size: 1.10em; }
.errmsg { margin-left: auto; margin-right: auto; padding: 5px; width: 700px; color: #FFFF00; font-weight: 900; font-size: .75em; background-color: #A0A0A0; }
.trace { margin-left: auto; margin-right: auto; padding: 5px; width: 700px; color: #FFFF00; font-weight: 900; font-size: .75em; background-color: #A0A0A0; }
.copy { text-align: center; font-weight: 900; font-size: .75em; }
</style>

</head>

<body>

<br />
<div class="header">Application Framework Fatal Error</div>
<div class="errmsg"><?php echo nl2br ($errmsg) ?></div>

<?php if ($trace_info): ?>
<br />
<div class="header">Script Trace</div>
<div class="trace">
<?php foreach ($trace_info as $trace): ?>
<div><?php if (isset ($trace["class"])): ?><?php echo $trace["class"] ?>-&gt;<?php endif ?><?php echo $trace["function"] ?>()<?php if (isset ($trace["file"])): ?> called at <?php echo $trace["file"] ?>:<?php echo $trace["line"] ?><?php endif ?></div>
<?php endforeach ?>
</div>
<?php endif ?>

<br />
<div class="copy"><?php echo $app_version ?> <?php echo $app_copyright ?></div>

</body>

</html>