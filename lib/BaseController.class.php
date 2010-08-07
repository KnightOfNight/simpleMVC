<?php

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
