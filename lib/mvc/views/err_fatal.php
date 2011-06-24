<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>MCS MVC: Fatal Error</title>

<style type="text/css">
body { font-family: sans-serif; margin: 0px; padding: 10px; }
.header { font-weight: 900; font-size: 1.10em; width: 75%; }
.errmsg { padding: 5px; color: #FF0; font-weight: 500; font-size: .9em; background-color: #333; width: 75%; }
div.trace { padding: 5px; color: #FF0; font-weight: 500; font-size: .9em; background-color: #333; line-height: 150%; width: 75%; }
td.trace { color: #FF0; font-size: .8em; padding: 0 10px 0 0; }
.copy { font-weight: 900; font-size: .75em; }
</style>

</head>

<body>

<div class="header">Framework Fatal Error</div>
<div class="errmsg"><?php echo nl2br ($errmsg) ?></div>


<?php if ($trace_info): ?>
<div class="header"><br />Script Trace</div>

<div class="trace">

<table>

<?php for ( $line_count = 0; isset($trace_info[$line_count]); $line_count++ ): ?>
<?php $trace = $trace_info[$line_count]; ?>

<tr>

<td class="trace">
<?php if (isset ($trace["class"])): ?><?php echo $trace["class"] ?>-&gt;<?php endif ?><?php echo $trace["function"] ?>()
</td>

<td class="trace">
<?php if (isset ($trace["file"])): ?>called at&nbsp;&nbsp; <?php echo $trace["file"] ?>:<?php echo $trace["line"] ?><?php endif ?>

<?php if (isset ($trace_info[$line_count+1])): ?>
<?php $next_trace = $trace_info[$line_count+1]; ?>
&nbsp;&nbsp;[<?php if (isset ($next_trace["class"])): ?><?php echo $next_trace["class"] ?>-&gt;<?php endif ?><?php echo $next_trace["function"] ?>]
<?php endif ?>

</td>

</tr>

<?php endfor ?>

</table>

</div>

<?php endif ?>

<div class="copy"><br /><?php echo $app_version ?> <?php echo $app_copyright ?></div>

</body>

</html>
