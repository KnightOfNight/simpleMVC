<?PHP

	global $config;

?>
<?PHP if ($username = Login::authorized()): ?>
<div>Welcome <?PHP echo $username ?></div>
<div><a href="<?PHP echo Link::to("welcome", "logout") ?>">Logout</a></div>
<?PHP else: ?>
<form id="form_login" method="post" action="<?PHP echo Link::to("welcome") ?>">
Username: <input id="form_login_username" name="username" type="text" value="<?PHP echo $passed_username ?>"/><br />
Password: <input id="form_login_password" name="password" type="password" /><br />
<input id="form_login_remember" name="remember" type="checkbox" <?PHP echo $passed_remember ?> /> stay signed in<br />
<input type="submit" value="Login" />
</form>
<?PHP endif ?>
