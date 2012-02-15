<?php

class tutorial_controller extends BaseController {
	function beforeAction ($query) { }

	function gs ($query) {

		if ( isset($query['section']) AND ($query['section'] == 'models') ) {
			$view = 'gs_models';
		} elseif ( isset($query['section']) AND ($query['section'] == 'controllers') ) {
			$view = 'gs_controllers';
		} elseif ( isset($query['section']) AND ($query['section'] == 'views') ) {
			$view = 'gs_views';
		} else {
			$view = 'gs';
		}
		
		$view = new View($this->name(), $view, $query);
		$view->render_headfoot(TRUE);
		$view->render();

	}

	function afterAction ($query) {}
}
