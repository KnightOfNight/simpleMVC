<?php

class welcome_controller extends BaseController {
	function beforeAction ($query) { }

	function hello ($query) {
		$view = new View($this->name(), __FUNCTION__, $query);
		$view->render_headfoot(TRUE);
		$view->render();
	}

	function afterAction ($query) {}
}
