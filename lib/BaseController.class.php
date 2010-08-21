<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version 0.1.0
*
*/


/**
*
* Base Controller class - handle the lowest level functions of a controller
* object.
*
* @package MVCAPI
*
*/
class BaseController {
	protected $controller;
	protected $view;

	function __construct () {
		$this->controller = strtolower (str_replace ("Controller", "", get_class ($this)));
	}

	function __destruct () {
		if ($this->view instanceof View) {
			$this->view->render ();
		}
	}
}


