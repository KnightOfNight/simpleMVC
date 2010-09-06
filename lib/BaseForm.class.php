<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
*/


/**
* Handle processing and display of an HTML form.
*
* @package MCS_MVC_API
*/
class BaseForm {
	private $_fields = array();


	/**
	* Create a new BaseForm object.
	*
	* @param string form method, i.e. "get" or "post"
	* @param string the action URL
	* @param hash optional list of field names and their values
	*/
	function __construct ($method, $action, $values = array()) {
		if ( ! method_exists($this, "setup") ) {
			Err::fatal("unable to instantiate form - no setup method found");
		}

		$method = strtolower($method);

		if ( ($method != "post") && ($method != "get") ) {
			Err::fatal("invalid form method '$method'");
		}

		if ( ! is_array($values) ) {
			Err::fatal("optional list of field values should be a hash of field name => field value pairs");
		}

		$this->_method = $method;
		$this->_action = $action;

		$this->setup();

#Dbg::var_dump("values", $values);

		foreach ( $values as $key => $value ) {
			if ( isset($this->_fields[$key]) ) {
				$this->_fields[$key]["value"] = $value;
			}
		}
	}


	/**
	* Output HTML that starts the form.
	*/
	function start () {
?><form class="mvc_form" method="<?php echo $this->_method ?>" action="<?php echo $this->_action ?>"><fieldset class="mvc_form_fieldset"><?php
	}


	/**
	* Output HTML that displays a field label.
	*
	* @param string field name
	*/
	function showlabel ($field) {
		$label = $this->_fields[$field]["label"];

?><label class="mvc_form_label" for="<?php echo $field ?>"><?php echo $label ?></label><?php
	}


	/**
	* Output HTML that displays a field.
	*
	* @param string field name
	*/
	function showfield ($field) {
		$info = $this->_fields[$field];

		$type = $info["label"];
		$value = $info["value"];
		$valid = $info["valid"];
		$error = $info["error"];

		if ( $info["type"] == "text" ) {
			$length = $info["length"];
			$maxlen = $info["maxlen"];

?><input id="<?php echo $field ?>" class="mvc_form_text_field" name="<?php echo $field ?>" value="<?php echo $value ?>" size="<?php echo $length ?>" maxlength="<?php echo $maxlen ?>"></input><?php

		}

		if ( ! $valid ) {
?><ul class="mvc_form_error"><li><?php echo $error ?></li></ul><?php
		}
	}


	/**
	* Output HTML that finishes the form.
	*/
	function finish () {
?></fieldset></form>
<?php
	}


	/**
	* Add a text field to the form.
	*
	* @param string field name
	* @param integer length of the field
	* @param integer maximum input length
	*/
	function textfield ($name, $length, $maxlen, $checks = array()) {
		$info = array(	"type" => "text",
						"label" => $name,
						"length" => $length,
						"maxlen" => $maxlen,
						"checks" => array(),
						"value" => "",
						"valid" => TRUE,
						"error" => "",
		);

		$this->_fields[$name] = $info;
	}


	/**
	* Set the displayed label for a field.
	*
	* @param string field name
	* @param string display label
	*/
	function label ($name, $label) {
		if ( ! isset($this->_fields[$name]) ) {
			Err::fatal("invalid field name '$name' - field not declared in this form");
		}

		$this->_fields[$name]["label"] = $label;
	}


	/**
	* Set the list of check functions for a field.
	*
	* @param string field name
	* @param array optional list of checks to perform on the field
	*/
	function check ($name, $checks = array()) {
		if ( ! isset($this->_fields[$name]) ) {
			Err::fatal("invalid field name '$name' - field not declared in this form");
		}

		if ( ! is_array($checks) ) {
			Err::fatal("list of checks should be an array");
		}

		$this->_fields[$name]["checks"] = $checks;
	}


	/**
	* Check to see if the form passes basic validation.
	*/
	function isvalid () {
		$ret = TRUE;

#Dbg::var_dump("ret", $ret);

		foreach ( $this->_fields as $field => $info ) {
#Dbg::var_dump("field", $field);

			$value = $info["value"];
			$checks = $info["checks"];
#Dbg::var_dump("value", $value);
#Dbg::var_dump("checks", $checks);

			foreach ( $checks as $check ) {
				if ( ! method_exists($this, $ckfunc = "_ck_" . $check) ) {
					Err::fatal("invalid check function '$check' declared in form");
				}

#Dbg::var_dump("ckfunc", $ckfunc);
				$error = $this->$ckfunc($value);

#Dbg::var_dump("error", $error);
				if ( $error === TRUE ) {
					$this->_fields[$field]["valid"] = TRUE;
					$this->_fields[$field]["error"] = "";
				} else {
					$this->_fields[$field]["valid"] = FALSE;
					$this->_fields[$field]["error"] = $error;
					$ret = FALSE;
					break;
				}
			}
		}

#Dbg::var_dump("ret", $ret);

		return($ret);
	}


	private function _ck_required ($value) {
		if ( isset($value) AND (! empty($value)) ) {
#Dbg::var_dump("value", $value);
			return(TRUE);
		} else {
			return("this field is required");
		}
	}


	private function _ck_numeric ($value) {
		if ( empty($value) OR (! preg_match('/[^0-9]/', $value)) ) {
#Dbg::var_dump("value", $value);
			return(TRUE);
		} else {
			return("this field may only contain numbers");
		}
	}


	function __destruct () {}
}
