<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
*/


/**
* Base Controller class - handle the lowest level functions of a controller
* object.
*
* @package MCS_MVC_API
*/
class BaseController {
	/**
	* @var string name of the instantiated controller
	*/
	protected $name;


	/**
	* @var string name of the controller
	*/
	protected $controller;


	/**
	* @var string name of the vuew
	*/
	protected $view;


	/**
	* Create a new BaseControler object.
	*
	* @return BaseController a new BaseController object
	*/
	function __construct () {
		$this->controller = strtolower (str_replace ("Controller", "", get_class ($this)));
	}


	/**
	* NOTE: when this object is destroyed, it then renders the prepared view,
	* if any.
	*/
	function __destruct () {
		if ($this->view instanceof View) {
			$this->view->render ();
		}
	}
}
