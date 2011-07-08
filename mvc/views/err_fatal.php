<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>simpleMVC Fatal Error</title>

<style type="text/css">
body { font-family: sans-serif; margin: 10px; padding: 0; font-size: 10pt; line-height: 125%; }
table { border-collapse: collapse; }
table,tr,td { margin: 0; padding: 0; border-width: 0; }

div.header { font-weight: 900; font-size: 1.10em; }

div.block  { padding: 15px; color: #FF0; background-color: #333; width: 75%; }

div.errmsg { }

div.trace { }

.copy { font-weight: 900; font-size: .75em; }

</style>

</head>

<body>

<div class="header">Framework Fatal Error</div>
<div class="block errmsg">
<?php echo nl2br ($message) ?>
</div>


<?php if ($trace_info): ?><!-- if there is trace info -->

<br />

<div class="header">Script Trace</div>

<div class="block trace"><!-- trace output -->

<?php $trace = array_shift($trace_info); ?>
<?php $line_count = -1; ?>

<div>
<?php if (isset ($trace["file"])): ?>Script TERMINATED AT <?php echo $trace["file"] ?>:<?php echo $trace["line"] ?>
<?php if (isset ($trace_info[$line_count+1])): ?><?php $next_trace = $trace_info[$line_count+1]; ?> IN <?php if (isset ($next_trace["class"])): ?><?php echo $next_trace["class"] ?>-&gt;<?php endif ?><?php echo $next_trace["function"] ?>()<?php endif ?>
<?php endif ?>
</div>

<br />

<?php for ( $line_count = 0; isset($trace_info[$line_count]); $line_count++ ): ?>

<?php $trace = $trace_info[$line_count]; ?>

<div>
<?php if (isset ($trace["class"])): ?><?php echo $trace["class"] ?>-&gt;<?php endif ?><?php echo $trace["function"] ?>()
<?php if (isset ($trace["file"])): ?> CALLED AT <?php echo $trace["file"] ?>:<?php echo $trace["line"] ?>
<?php if (isset ($trace_info[$line_count+1])): ?><?php $next_trace = $trace_info[$line_count+1]; ?> IN <?php if (isset ($next_trace["class"])): ?><?php echo $next_trace["class"] ?>-&gt;<?php endif ?><?php echo $next_trace["function"] ?>()<?php endif ?>
<?php endif ?>
</div>

<br />

<?php endfor ?>

</div><!-- end trace output -->

<?php endif ?><!-- if there is trace info -->

<div class="copy"><br /><?php echo $app_version ?> <?php echo $app_copyright ?></div>

</body> </html>


