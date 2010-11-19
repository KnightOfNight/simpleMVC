<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.2.0
*/


/**
* Base Controller class - handle the lowest level functions of a controller
* object.
*
* @package MCS_MVC_API
*/
class BaseController {
	private $_name;


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


	function __destruct () { }
}
