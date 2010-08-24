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
	private $_name;
	private $_view = NULL;


	/**
	* Create a new BaseControler object.
	*
	* @return BaseController a new BaseController object
	*/
	function __construct () {
		$this->_name = BaseController::tocontroller( get_class($this) );
	}


	/**
	* Return the instantiated controller name.
	*
	* @return string controller name
	*/
	function name () {
		return ($this->_name);
	}


	/**
	* Get or set the current view.
	*
	* @param string action name
	* @param string query string
	* @return View the current view, if any
	*/
	function view ($action = NULL, $query = NULL) {
		if ($action) {
			$this->_view = new View($this->_name, $action, $query);
		}

		return ($this->_view);
	}


	/**
	* NOTE: when this object is destroyed, it then renders the prepared view if
	* there is one.
	*/
	function __destruct () {
		if ($this->_view instanceof View) {
			$this->_view->render();
		}
	}


	/**
	* Convert a controller name into the corresponding class name.
	*
	* @param string controller name
	* @return string class name
	*/
	static function toclass ($controller) {
		return( $controller . "_controller" );
	}


	/**
	* Convert a class name into the corresponding controller name.
	*
	* @param string class name
	* @return string controller name
	*/
	static function tocontroller ($class) {
		return( str_replace("_controller", "", $class) );
	}
}
