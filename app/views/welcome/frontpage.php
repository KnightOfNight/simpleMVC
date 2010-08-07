<?PHP

$this->slot("page_title", "Welcome!");

?>
<div id="login_form"><?PHP include_action_output ("welcome", "_loginform", $QUERY) ?></div>
