<?php

class sample_controller extends BaseController {
	/**
	* This method is called before the requested action is executed.
	*/
	function beforeAction ($query) {
	}


	/**
	* Define actions by writing methods of the same name. All action
	* methods get one argument, the query part of the route, as a hash.
	*/
	function action ($query) {
		$view = new View($this->name(), __FUNCTION__, $query);
		$view->render_headfoot(TRUE);
		$view->render();
	}


	/**
	* This method is called after the requested action is executed.
	*/
	function afterAction ($query) {}
}
