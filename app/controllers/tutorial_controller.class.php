<?php

class tutorial_controller extends BaseController {
	function beforeAction ($query) { }

	function gs ($query) {

		if ( isset($query['section']) AND ($query['section'] == 'models') ) {
			$view = 'gs_models';
		} else {
			$view = 'gs';
		}
		
		$view = new View($this->name(), $view, $query);
		$view->render_headfoot(TRUE);
		$view->render();

	}

	function afterAction ($query) {}
}
