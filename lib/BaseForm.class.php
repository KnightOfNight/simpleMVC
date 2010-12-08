<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
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
	function __construct ($method, $action) {
		if ( ! method_exists($this, "setup") ) {
			Err::fatal("unable to instantiate form - no setup method found");
		}

		$method = strtolower($method);

		if ( ($method != "post") && ($method != "get") ) {
			Err::fatal("invalid form method '$method'");
		}

		$this->_method = $method;
		$this->_action = $action;

		$this->setup();
	}


	/**
	* Output HTML that starts the form.
	*/
	function start () {
?><form class="mvc_form" method="<?= $this->_method ?>" action="<?= $this->_action ?>"><fieldset class="mvc_form_fieldset">
<?php
	}


	/**
	* Output HTML that displays a field label.
	*
	* @param string field name
	*/
	function showlabel ($field_name) {
		$label = $this->_fields[$field_name]["label"];

?><label class="mvc_form_label" for="<?= $field_name ?>"><?= $label ?></label>
<?php
	}


	/**
	* Output HTML that displays a field.
	*
	* @param string field name
	*/
	function showfield ($field_name) {
		$info = $this->_fields[$field_name];

		$type = $info["label"];
		$value = htmlentities($info["value"]);
		$valid = $info["valid"];
		$error = $info["error"];

		if ( $info["type"] == "text" ) {
			if ( ! ($size = $info['options']['size']) ) {
				$size = 10;
			}
			if ( ! ($maxlength = $info['options']['maxlength']) ) {
				$maxlength = 20;
			}

?><input id="<?= $field_name ?>" class="mvc_form_text_field" name="<?= $field_name ?>" value="<?= $value ?>" size="<?= $size ?>" maxlength="<?= $maxlength ?>"></input>
<?php

		} elseif ( $info["type"] == "password" ) {
			if ( ! ($size = $info['options']['size']) ) {
				$size = 10;
			}
			if ( ! ($maxlength = $info['options']['maxlength']) ) {
				$maxlength = 20;
			}

?><input id="<?= $field_name ?>" class="mvc_form_password" name="<?= $field_name ?>" value="<?= $value ?>" size="<?= $size ?>" maxlength="<?= $maxlength ?>" type="password"></input>
<?php

		} elseif ( $info["type"] == "checkbox" ) {
?><input id="<?= $field_name ?>" class="mvc_form_checkbox" name="<?= $field_name ?>" value="<?= $value ?>" type="checkbox" />
<?php

		} elseif ( $info["type"] == "hidden" ) {
?><input name="<?= $field_name ?>" value="<?= $value ?>" type="hidden"></input>
<?php

		}

		if ( ! $valid ) {
?><span class="mvc_form_error"><?= $error ?></span>
<?php
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
	* Add a generic field to the form.
	*
	* @param string type of field
	* @param string name of field
	* @param mixed hash of any optional parameters
	*/
	private function _addfield ($field_type, $field_name, $options = array()) {

		$info = array(	"type" => $field_type,
						"label" => $field_name,
						"options" => $options,
						"checks" => array(),
						"value" => "",
						"valid" => TRUE,
						"error" => "",
		);

		$this->_fields[$field_name] = $info;

	}


	/**
	* Add a text field to the form.
	*
	* @param string field name
	* @param integer size of the field
	* @param integer maximum input length
	*/
	function textfield ($field_name, $size, $maxlength) {
		$this->_addfield("text", $field_name, array("size" => $size, "maxlength" => $maxlength));
	}


	/**
	* Add a password field to the form.
	*
	* @param string field name
	* @param integer size of the field
	* @param integer maximum input length
	*/
	function passwordfield ($field_name, $size, $maxlength) {
		$this->_addfield("password", $field_name, array("size" => $size, "maxlength" => $maxlength));
	}


	/**
	* Add a checkbox field to the form.
	*
	* @param string field name
	*/
	function checkboxfield ($field_name) {
		$this->_addfield("checkbox", $field_name);
	}


	/**
	* Add a hidden field to the form.
	*
	* @param string field name
	*/
	function hiddenfield ($field_name) {
		$this->_addfield("hidden", $field_name);
	}


	/**
	* Add a select field to the form.
	*
	* @param string field name
	*/
	function selectfield ($field_name) {
		$this->_addfield('text', $field_name, array('size'=>5, 'maxlength'=>10));
	}


	/**
	* Set the displayed label for a field.
	*
	* @param string field name
	* @param string display label
	*/
	function label ($field_name, $label) {
		if ( ! isset($this->_fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		$this->_fields[$field_name]["label"] = $label;
	}


	/**
	* Set the list of check functions for a field.
	*
	* @param string field name
	* @param array optional list of checks to perform on the field
	*/
	function check ($field_name, $checks = array()) {
		if ( ! isset($this->_fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		if ( ! is_array($checks) ) {
			Err::fatal("list of checks should be an array");
		}

		$this->_fields[$field_name]["checks"] = $checks;
	}


	/**
	* Set the value for a field.
	*
	* @param string field name
	* @param mixed optional field value
	*/
	function value ($field_name, $value = NULL) {
		if ( ! isset($this->_fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		if ( $value === NULL ) {
			$this->_fields[$field_name]["value"] = "";
		} else {
			$this->_fields[$field_name]["value"] = $value;
		}
	}


	/**
	* Mark a field as invalid and set a custom error message.
	*
	* @param string field name
	* @param string custom error message
	*/
	function invalidate ($field_name, $error_message) {
		if ( ! isset($this->_fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		$this->_fields[$field_name]["valid"] = FALSE;
		$this->_fields[$field_name]["error"] = $error_message;
	}


	/**
	* Check to see if the form passes basic validation.
	* @return bool TRUE => form passed basic field validation
	*/
	function validate () {
		$ret = TRUE;

#Dbg::var_dump("ret", $ret);

		foreach ( $this->_fields as $field_name => $info ) {
#Dbg::var_dump("field_name", $field_name);

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
					$this->_fields[$field_name]["valid"] = TRUE;
					$this->_fields[$field_name]["error"] = "";
				} else {
					$this->_fields[$field_name]["valid"] = FALSE;
					$this->_fields[$field_name]["error"] = $error;
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
