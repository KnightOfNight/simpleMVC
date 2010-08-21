<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>MCS MVC: Fatal Error</title>

<link rel="stylesheet" type="text/css" media="screen" href="https://www.magrathea.com/~ctg/dvds/css/mcsmvc/error_fatal.css" />

</head>

<body>

<br />
<div class="header">The program has detected an fatal error.</div>

<br />
<div class="errmsg"><?php echo nl2br ($errmsg) ?></div>

<?php if ($trace_info): ?>
<br />
<div class="header">Program trace...</div>
<div class="trace">
<?php foreach ($trace_info as $trace): ?>
<div><?php if (isset ($trace["class"])): ?><?php echo $trace["class"] ?>-&gt;<?php endif ?><?php echo $trace["function"] ?>()<?php if (isset ($trace["file"])): ?> called at <?php echo $trace["file"] ?>:<?php echo $trace["line"] ?><?php endif ?></div>
<?php endforeach ?>
</div>
<?php endif ?>

<br />
<div class="copy"><?php echo $app_version ?> - <?php echo $app_copyright ?></div>

</body>

</html>
