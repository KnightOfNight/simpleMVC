<?php

class gs_controller extends BaseController {
	function beforeAction ($query) { }

	function index ($query) {
		$view = new View($this->name(), __FUNCTION__, $query);
		$view->render_headfoot(TRUE);
		$view->render();
	}

	function models ($query) {
		$view = new View($this->name(), __FUNCTION__, $query);
		$view->render_headfoot(TRUE);
		$view->render();
	}

	function views ($query) {
		$view = new View($this->name(), __FUNCTION__, $query);
		$view->render_headfoot(TRUE);
		$view->render();
	}

	function controllers ($query) {
		$view = new View($this->name(), __FUNCTION__, $query);
		$view->render_headfoot(TRUE);
		$view->render();
	}

	function routes ($query) {
		$view = new View($this->name(), __FUNCTION__, $query);
		$view->render_headfoot(TRUE);
		$view->render();
	}

	function afterAction ($query) {}
}
