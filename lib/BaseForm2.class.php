<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*/


/**
* Handle all basic aspects of an HTML form.
*
* Internal Structure of Fields Hash
*
* Key: string, field name
*
* Value: hash, field information - keys follow
*
* type: string, required
*
* label: string, required
*
* value: string, optional, default ''
*
* valid: string, optional, default TRUE
*
* error: string, optional, default ''
*
* options: hash ('option name' => 'value'), optional, default is all default options set
*
* option enabled: string, default 'yes'
*
* option required: string, default 'yes'
*
* option hidden: string, default 'yes'
*
* checks: hash ('check function' => 'custom error message'), optional, default is none
*
* @package MCS_MVC_API
*/
class BaseForm2 {

	protected $form_name;

	protected $fields;
	protected $options;

	private $_method;
	private $_HTML_form_name;

	private $_field_types = array( 'text', 'password', 'dropdown', 'checkbox', 'bitmask' );
	private $_fields_with_choices = array( 'dropdown', 'bitmask' );

	/**
	* Create a new BaseForm object.
	*
	* @param string form name
	* @param string form name suffix
	*/
	function __construct ($form_name, $suffix = NULL) {

		$this->form_name = $form_name;

		$this->_HTML_form_name = 'f_' . $form_name;

		if ( $suffix ) {
			$this->_HTML_form_name .= '_' . $suffix;
		}

		if ( $cache = Cache::value($cache_key = "form:" . $this->form_name) ) {
#			$this->fields = $cache['fields'];
#			$this->options = $cache['options'];
#			return;
		}

		$cfg_file = ROOT.DS."app/forms/json".DS.$this->form_name.".json";

		if ( (! File::ready ($cfg_file)) OR ( ($cfg_data = file_get_contents ($cfg_file)) === FALSE ) ) {
			Err::fatal (sprintf ("unable to read configuration file '%s'", $cfg_file));
		}

		if ( ($config = json_decode ($cfg_data, TRUE)) === NULL ) {
            Err::fatal ("unable to parse form configuration, invalid JSON found");
		}


		if ( ! isset($config['fields']) ) {
            Err::fatal ("invalid form config: no fields configured");
		} elseif ( (! is_array($config['fields'])) OR empty($config['fields']) ) {
            Err::fatal ("invalid form config: 'fields' must be an array of one or more");
		} else {
			$this->fields = $config["fields"];
		}


		if ( ! isset($config['options']) ) {
			$this->options = array();
		} elseif ( ! is_array($config['options']) ) {
           	Err::fatal ("invalid form config: 'options' must be an array");
		} else {
			$this->options = $config['options'];
		}


		$this->_check_field_config();


		$cache = array();
		$cache['fields'] = $this->fields;
		$cache['options'] = $this->options;
		Cache::value($cache_key, $cache);


		if ( method_exists($this, 'setup') ) {
			$this->setup();
		}

	}




	/**
	* Perform a detailed check of the field configuration.  Die if invalid in any way.
	*/
	private function _check_field_config () {
		foreach ( $this->fields as $field_name => $field_info ) {

			# type
			if ( ! isset($this->fields[$field_name]['type']) ) {
				Err::fatal("invalid form config: field '$field_name' - no field type specified");
			}

			if ( ! in_array($this->fields[$field_name]['type'], $this->_field_types) ) {
				Err::fatal("invalid form config: field '$field_name' - invalid field type '$this->fields[$field_name]['type']'");
			}


			# label
			if ( ! isset($this->fields[$field_name]['label']) ) {
				Err::fatal("invalid form config: field '$field_name' - field label is required");
			}


			# value
			if ( ! isset($this->fields[$field_name]['value']) ) {
				$this->fields[$field_name]['value'] = '';
			}

			# valid
			$this->fields[$field_name]['valid'] = TRUE;

			# error
			$this->fields[$field_name]['error'] = 'Field is invalid.';

			# options and checks
			foreach ( array('options', 'checks') as $item) {
				if ( ! isset($this->fields[$field_name][$item]) ) {
					$this->fields[$field_name][$item] = array();
				} elseif ( ! is_array($this->fields[$field_name][$item]) ) {
					Err::fatal("invalid form config: field '$field_name' - '$item' must be an array");
				}
			}

			# option: enabled
			if ( ! isset($this->fields[$field_name]['options']['enabled']) ) {
				$this->fields[$field_name]['options']['enabled'] = 'yes';
			}

			# option: required
			if ( ! isset($this->fields[$field_name]['options']['required']) ) {
				$this->fields[$field_name]['options']['required'] = 'yes';
			}

			# option: hidden
			if ( ! isset($this->fields[$field_name]['options']['hidden']) ) {
				$this->fields[$field_name]['options']['hidden'] = 'no';
			}

			# option: choices
			if ( in_array($this->fields[$field_name]['type'], $this->_fields_with_choices) ) {
				if ( ! isset($this->fields[$field_name]['options']['choices']) ) {
					Err::fatal("invalid form config: field '$field_name' is type '$this->fields[$field_name]['type']' but no 'choices' found in 'options'");
				}

				if ( ! is_array($this->fields[$field_name]['options']['choices']) ) {

					$parts = explode(".", $this->fields[$field_name]['options']['choices']);

					if ( count($parts) != 2 ) {
						Err::fatal("invalid form config: field '$field_name' - 'choices' is invalid, should be array or 'class.method'");
					}

					$class = $parts[0];
					$method = $parts[1];

					if ( ! method_exists($class, $method) ) {
						Err::fatal("invalid form config: field '$field_name' - 'choices' is invalid, method not found");
					}

					$this->fields[$field_name]['options']['choices'] = call_user_func( array($class, $method) );

					if ( empty($this->fields[$field_name]['options']['choices']) ) {
						Err::fatal("invalid form config: field '$field_name' - 'choices' is invalid, method returned empty list");
					}

				}

			}
		
			# checks
			foreach ( $this->fields[$field_name]['checks'] as $check_name => $check_errmsg ) {
				if ( ! method_exists($this, $ckfunc = '_ck_' . $check_name) ) {
					Err::fatal("invalid form config: field '$field_name' - check '$check_name' is invalid, method '$ckfunc' not found");
				}
			}
		}

		return(TRUE);
	}


	/**
	* Output HTML that starts the form.
	*
	* @param string form method, i.e. 'get' or 'post'
	* @param string the action URL
	*/
	function HTMLstart ($method, $action) {
		if ( ($method != 'post') AND ($method != 'get') ) {
			Err::fatal("invalid form method '$method'");
		}

		$this->_method = $method;

		$id = $this->_HTML_form_name;

?><form id="<?= $id ?>" class="mvc_form" method="<?= $method ?>" action="<?= $action ?>">
<div>
<input class="mvc_form_internal" name="form_name" value="<?= $this->form_name ?>" type="hidden"></input>
<?php
	}


	/**
	* Output HTML that finishes the form.
	*/
	function HTMLfinish () {
?></div></form>
<?php
	}


	/**
	* Output HTML that displays a field label.
	*
	* @param string field name
	*/
	function HTMLlabel ($field_name) {
		if ( ! isset($this->fields[$field_name]) ) {
			Err::fatal("invalid field name '$field_name'");
		}

		$field_info = $this->fields[$field_name];

		$id = $this->_HTML_form_name . '_l_' . $field_name;
		$for = $this->_HTML_form_name . '_' . $field_name;
		$label = $field_info['label'];

?><label id="<?= $id ?>" class="mvc_form_label" for="<?= $for ?>"><?= $label ?></label>
<?php
	}


	/**
	* Output HTML that displays a field.
	*
	* @param string field name
	*/
	function HTMLfield ($field_name) {
		if ( ! isset($this->fields[$field_name]) ) {
			Err::fatal("invalid field name '$field_name'");
		}

		$field_info = $this->fields[$field_name];

		$type = $field_info['type'];

		$func = "_HTML_" . $type;

		$this->$func($field_name);
	}


	/**
	* Output the HTML for a field type 'text'.
	*
	* @param string field name
	*/
	private function _HTML_text ($field_name) {

		# common
		$id = $name = $this->_HTML_form_name . '_' . $field_name;
		$value = $this->fields[$field_name]['value'];
		$options = $this->fields[$field_name]['options'];

		$disabled = ($options['enabled'] == 'no') ? ' disabled="disabled"' : '';
		$hidden = ($options['hidden'] == 'yes') ? ' type="hidden"' : '';

		$valid = $this->fields[$field_name]['valid'];
		$error = $this->fields[$field_name]['error'];

		# specific
		$inputclass = 'mvc_form_text_field';

		$size = ( isset($options['size']) AND ($options['size'] > 0) ) ?  $options['size'] : 10;

		$maxlength = ( isset($options['maxlength']) AND ($options['maxlength'] > 0) ) ?  $options['maxlength'] : 80;

		$password = ( isset($options['password']) AND ($options['password'] == 'yes') ) ? ' type="password"' : '';

		$password = ( $hidden AND $password ) ? '' : $password;

		# HTML
?><input id="<?= $id ?>" class="<?= $inputclass ?>" name="<?= $name ?>" value="<?= $value ?>" size="<?= $size ?>" maxlength="<?= $maxlength ?>"<?= $disabled ?><?= $password ?><?= $hidden ?>></input>
<?php

		$this->_HTML_field_error($field_name);

	}

	/**
	* Output the HTML for a field type 'dropdown'.
	*
	* @param string field name
	*/
	private function _HTML_dropdown ($field_name) {

		# common
		$id = $name = $this->_HTML_form_name . '_' . $field_name;
		$value = $this->fields[$field_name]['value'];
		$options = $this->fields[$field_name]['options'];
		$disabled = ($options['enabled'] == 'no') ? ' disabled="disabled"' : '';
		$hidden = ($options['hidden'] == 'yes') ? ' type="hidden"' : '';
		$valid = $this->fields[$field_name]['valid'];
		$error = $this->fields[$field_name]['error'];

		# specific
		$inputclass = 'mvc_form_dropdown_field';
		$choices = $options['choices'];
		$forcechoice = ( isset($options['forcechoice']) AND ($options['forcechoice'] == 'yes') ) ? TRUE : FALSE;

		# HTML
?><select id="<?= $id ?>" class="<?= $inputclass ?>" name="<?= $name ?>"<?= $disabled ?><?= $hidden ?>>
<?php

		if ( (! $value) AND $forcechoice ) {
?><option value="">Select One</option>
<?php
		}

		foreach ( $options['choices'] as $choice => $choice_value ) {
			$selected = ( $value == $choice_value ) ? ' selected="selected"' : '';

?><option value="<?= $choice_value ?>"<?= $selected ?>><?= $choice ?></option>
<?php
		}

?></select>
<?php

		$this->_HTML_field_error($field_name);

	}


	/**
	* Output the HTML for a field type 'bitmask'.
	*
	* @param string field name
	*/
	private function _HTML_bitmask ($field_name) {

		# common
		$id = $name = $this->_HTML_form_name . '_' . $field_name;
		$value = $this->fields[$field_name]['value'];
		$options = $this->fields[$field_name]['options'];
		$disabled = ($options['enabled'] == 'no') ? ' disabled="disabled"' : '';
		$hidden = ($options['hidden'] == 'yes') ? ' type="hidden"' : '';
		$valid = $this->fields[$field_name]['valid'];
		$error = $this->fields[$field_name]['error'];

		# specific
		$tableclass = 'mvc_form_bitmask';
		$inputclass = 'mvc_form_bitmask_field';
		$choices = $options['choices'];
		$columns = ( isset($options['columns']) AND ($options['columns'] > 0)  AND ($options['columns'] < count($choices)) ) ?  $options['columns'] : 3;

		# HTML

?>
<table id="<?= $id ?>" class="<?= $tableclass ?>">
<tr> <td>
<?php

		$count = 0;
		$per_column = count($choices) / $columns;

		foreach ( $choices as $choice => $choice_value ) {

			$input_id = $fld_name = $id . '_' . md5($choice);
			$checked = ( $value & $choice_value ) ? ' checked="checked"' : '';

			$label_id = $this->_HTML_form_name . '_l_' . $field_name . '_' . md5($choice);
			$for = $input_id;
			$label = $choice;

			if ( $count >= $per_column ) {
				$count = 0;
?>
</td> <td>
<?php
			}

?><input id="<?= $input_id ?>" class="<?= $inputclass ?>" name="<?= $fld_name ?>" value="checked" type="checkbox"<?= $checked ?><?= $disabled ?><?= $hidden ?>></input>
<label id="<?= $label_id ?>" class="mvc_form_label" for="<?= $for ?>"><?= $label ?></label>
<br />
<?php

			$count++;

		}

?>
</td> </tr>
</table>
<?php

		$this->_HTML_field_error($field_name);

	}


	/**
	* Output the HTML for a field error.
	*
	* @param string field name
	*/
	private function _HTML_field_error ($field_name) {
		$valid = $this->fields[$field_name]['valid'];
		$error = $this->fields[$field_name]['error'];

		if ( ! $valid ) {
?><span class="mvc_form_error"><?= $error ?></span>
<?php
		}
	}


	/**
	* Get/Set the displayed label for a field.
	*
	* @param string field name
	* @param string optional display label
	*/
	function label ($field_name, $label = NULL) {
		if ( ! isset($this->fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		if ( $label === NULL ) {
			return($this->fields[$field_name]['label']);
		} else {
			$this->fields[$field_name]['label'] = $label;
		}

	}


	/**
	* Get/Set the value for a field.
	*
	* @param string field name
	* @param mixed optional field value
	*/
	function value ($field_name, $value = NULL) {
		if ( ! isset($this->fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		if ( $value === NULL ) {
			return($this->fields[$field_name]['value']);
		} else {
			$this->fields[$field_name]['value'] = $value;
		}
	}


	/**
	* Get/Set an option for a field.
	*
	* @param string field name
	* @param string option name
	* @param string optional option value
	*/
	function option ($field_name, $option_name, $value = NULL) {
		if ( ! isset($this->fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		if ( $value === NULL ) {
			return($this->fields[$field_name]['options'][$option_name]);
		} else {
			$this->fields[$field_name]['options'][$option_name] = $value;
		}

	}


	/**
	* Check to see if the form has been submitted, populate field values.
	* Will not check input.
	*
	* @param string form method, i.e. 'get' or 'post'
	*/
	function submitted ($method) {
		if ( $method == 'post' ) {
			$sub = $_POST;
		} elseif ( $method == 'get' ) {
			$sub = $_GET;
		}

		if ( (! isset($sub['form_name'])) OR ($sub['form_name'] != $this->form_name) ) {
			return(FALSE);
		}

		foreach ( $this->fields as $field_name => $field_info ) {

			$form_field_name = $this->_HTML_form_name . '_' . $field_name;
			$type = $this->fields[$field_name]['type'];

			if ( $type == 'checkbox' ) {
				if ( isset($sub[$form_field_name]) AND ($sub[$form_field_name] == 'checked') ) {
					$this->fields[$field_name]['value'] = 'checked';
				}

			} elseif ( $type == 'bitmask' ) {
				$choices = $this->fields[$field_name]['options']['choices'];

				$bitmask = 0;

				foreach ( $choices as $choice => $choice_value ) {
					$fld_name = $form_field_name . '_' . md5($choice);

					if ( isset($sub[$fld_name]) AND ($sub[$fld_name] == 'checked') ) {
						$bitmask |= $choice_value;
					}
				
				}

				$this->fields[$field_name]['value'] = $bitmask;

			} else {
				if ( isset($sub[$form_field_name]) ) {
					$this->fields[$field_name]['value'] = htmlentities($sub[$form_field_name]);
				}
			}

		}

		return(TRUE);
	}


	/**
	* Check a form's contents.
	*/
	function check () {
		$all_errors = 0;

#		Dbg::msg('check() - starting');

		foreach ( $this->fields as $field_name => $field_info ) {

			$type = $this->fields[$field_name]['type'];
			$value = $this->fields[$field_name]['value'];
			$checks = $this->fields[$field_name]['checks'];


			$this->fields[$field_name]['valid'] = TRUE;
			$this->fields[$field_name]['error'] = '';


			$enabled = ($this->fields[$field_name]['options']['enabled'] == 'yes') ? TRUE : FALSE;


#			if ( ! $enabled ) {
#				continue;
#			}


			if ( ($type == 'checkbox') AND $value AND ($value != 'checked') ) {

				$this->fields[$field_name]['valid'] = FALSE;
				$this->fields[$field_name]['error'] = 'Invalid value submitted.';
				continue;

			}


			if ( $type == 'dropdown' ) {

				$choices = $this->fields[$field_name]['options']['choices'];

				if ( $value AND (! in_array($value, $choices)) ) {
					$this->fields[$field_name]['valid'] = FALSE;
					$this->fields[$field_name]['error'] = 'Invalid value submitted.';
					continue;
				}

			}


			foreach ( $checks as $check => $check_errmsg ) {

#				Dbg::msg('check = ' . $check);

				if ( ! method_exists($this, $ckfunc = '_ck_' . $check) ) {
					Err::fatal("invalid check function '$check' declared in form");
				}

				$error = $this->$ckfunc($value);

#				Dbg::var_dump('error', $error);

				if ( $error !== TRUE ) {
					$this->fields[$field_name]['valid'] = FALSE;

					if ( $check_errmsg ) {
						$this->fields[$field_name]['error'] = $check_errmsg;
					} else {
						$this->fields[$field_name]['error'] = $error;
					}

					break;
				}

			}

		}
	}


	/**
	* Set the list of check functions for a field.
	*
	* @param string field name
	* @param array optional list of checks to perform on the field
	*/
#	function check ($field_name, $checks = array()) {
#		if ( ! isset($this->fields[$field_name]) ) {
#			Err::fatal("invalid field '$field_name' - field not declared in this form");
#		}
#
#		if ( ! is_array($checks) ) {
#			Err::fatal("list of checks should be an array");
#		}
#
#		$this->fields[$field_name]['checks'] = $checks;
#	}


	/**
	* Mark a field as invalid and set a custom error message.
	*
	* @param string field name
	* @param string custom error message
	*/
	function invalidate ($field_name, $error_message) {
		if ( ! isset($this->fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		$this->fields[$field_name]['valid'] = FALSE;
		$this->fields[$field_name]['error'] = $error_message;
	}


	/**
	* Disable a field.
	*
	* @param string name of field
	*/
	function disable ($field_name) {
		if ( ! isset($this->fields[$field_name]) ) {
			Err::fatal("invalid field '$field_name' - field not declared in this form");
		}

		$this->fields[$field_name]['disabled'] = 'disabled';
	}


	/**
	* Check to see if the form passes validation.
	* @return bool TRUE => form passed field validation
	*/
	function validate () {
		$ret = TRUE;

#Dbg::var_dump('ret', $ret);

		foreach ( $this->fields as $field_name => $info ) {
#Dbg::var_dump('field_name', $field_name);

			$value = $info['value'];
			$checks = $info['checks'];
#Dbg::var_dump('value', $value);
#Dbg::var_dump('checks', $checks);

			foreach ( $checks as $check ) {
				if ( ! method_exists($this, $ckfunc = '_ck_' . $check) ) {
					Err::fatal("invalid check function '$check' declared in form");
				}

#Dbg::var_dump('ckfunc', $ckfunc);
				$error = $this->$ckfunc($value);

#Dbg::var_dump('error', $error);
				if ( $error === TRUE ) {
					$this->fields[$field_name]['valid'] = TRUE;
					$this->fields[$field_name]['error'] = '';
				} else {
					$this->fields[$field_name]['valid'] = FALSE;
					$this->fields[$field_name]['error'] = $error;
					$ret = FALSE;
					break;
				}
			}
		}

#Dbg::var_dump('ret', $ret);

		return($ret);
	}


	private function _ck_required ($value) {
		if ( empty($value) ) {
			return('This field requires a value.');
		} else {
			return(TRUE);
		}
	}


	private function _ck_digits ($value) {
		if ( empty($value) OR (! preg_match('/[^0-9]/', $value)) ) {
			return(TRUE);
		} else {
			return('Please enter numbers only.');
		}
	}


	function __destruct () {}
}
