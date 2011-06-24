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
* option hidden: string, default 'no'
*
* checks: hash ('check function' => 'custom error message'), optional, default is none
*
* @package MCS_MVC_API
*/
class BaseForm2 {

	private $_form_name;		# name of the form
	private $_html_form_name;	# may include suffix
	private $_options;			# form level options
	private $_fields;			# all fields
	private $_method;			# form submission method

	private $_field_types = array( 'text', 'dropdown', 'checkbox', 'bitmask' );
	private $_fields_with_choices = array( 'dropdown', 'bitmask' );

	/**
	* Create a new BaseForm object.
	*
	* @param string form name
	* @param string form name suffix
	*/
	function __construct ($form_name, $suffix = NULL) {

		$this->_form_name = $form_name;

		$this->_html_form_name = 'f_' . $form_name;

		if ( $suffix ) {
			$this->_html_form_name .= '_' . $suffix;
		}

		if ( $cache = Cache::value($cache_key = 'form:' . $this->_form_name) ) {
#			$this->_fields = $cache['fields'];
#			$this->_options = $cache['options'];
#			return;
		}

		$cfg_file = ROOT.DS.'app/forms/json'.DS.$this->_form_name.'.json';

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
			$this->_fields = $config['fields'];
		}


		if ( ! isset($config['options']) ) {
			$this->_options = array();
		} elseif ( ! is_array($config['options']) ) {
           	Err::fatal ("invalid form config: 'options' must be an array");
		} else {
			$this->_options = $config['options'];
		}


		foreach ( $this->_fields as $field_name => $UNUSED_field_info ) {
			$this->_check_field_config($field_name);
		}


		$cache = array();
		$cache['fields'] = $this->_fields;
		$cache['options'] = $this->_options;
		Cache::value($cache_key, $cache);


		if ( method_exists($this, 'setup') ) {
			$this->setup();
		}

	}


	/**
	* Perform a detailed check of a field configuration.  Die if invalid in any way.
	*
	* @param string field name
	*/
	private function _check_field_config ($field_name) {

		# type
		if ( ! isset($this->_fields[$field_name]['type']) ) {
			Err::fatal("invalid form config: field '$field_name' - no field type specified");
		}

		if ( ! in_array($this->_fields[$field_name]['type'], $this->_field_types) ) {
			Err::fatal("invalid form config: field '$field_name' - invalid field type '" . $this->_fields[$field_name]['type'] . "'");
		}

		# label
		if ( ! isset($this->_fields[$field_name]['label']) ) {
			Err::fatal("invalid form config: field '$field_name' - field label is required");
		}

		# value
		if ( ! isset($this->_fields[$field_name]['value']) ) {
			$this->_fields[$field_name]['value'] = NULL;
		}

		# valid
		$this->_fields[$field_name]['valid'] = TRUE;

		# error
		$this->_fields[$field_name]['error'] = '';

		# options and checks
		foreach ( array('options', 'checks') as $item) {
			if ( ! isset($this->_fields[$field_name][$item]) ) {
				$this->_fields[$field_name][$item] = array();
			} elseif ( ! is_array($this->_fields[$field_name][$item]) ) {
				Err::fatal("invalid form config: field '$field_name' - '$item' must be an array");
			}
		}

		# option: enabled
		if ( ! isset($this->_fields[$field_name]['options']['enabled']) ) {
			$this->_fields[$field_name]['options']['enabled'] = 'yes';
		}

		# option: hidden
		if ( ! isset($this->_fields[$field_name]['options']['hidden']) ) {
			$this->_fields[$field_name]['options']['hidden'] = 'no';
		}

		# option: choices
		if ( in_array($this->_fields[$field_name]['type'], $this->_fields_with_choices) ) {
			if ( ! isset($this->_fields[$field_name]['options']['choices']) ) {
				Err::fatal("invalid form config: field '$field_name' is type '$this->_fields[$field_name]['type']' but no 'choices' found in 'options'");
			}

			if ( ! is_array($this->_fields[$field_name]['options']['choices']) ) {

				$parts = explode('.', $this->_fields[$field_name]['options']['choices']);

				if ( count($parts) != 2 ) {
					Err::fatal("invalid form config: field '$field_name' - 'choices' is invalid, should be array or 'class.method'");
				}

				$class = $parts[0];
				$method = $parts[1];

				if ( ! method_exists($class, $method) ) {
					Err::fatal("invalid form config: field '$field_name' - 'choices' is invalid, method not found");
				}

				$this->_fields[$field_name]['options']['choices'] = call_user_func( array($class, $method) );

				if ( empty($this->_fields[$field_name]['options']['choices']) ) {
					Err::fatal("invalid form config: field '$field_name' - 'choices' is invalid, method returned empty list");
				}

			}

		}
		
		# checks
		foreach ( $this->_fields[$field_name]['checks'] as $check_name => $check_errmsg ) {
			if ( ! method_exists($this, $ckfunc = '_ck_' . $check_name) ) {
				Err::fatal("invalid form config: field '$field_name' - check '$check_name' is invalid, method '$ckfunc' not found");
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
	function html_start ($method, $action) {
		if ( ($method != 'post') AND ($method != 'get') ) {
			Err::fatal(__FUNCTION__ . "() - invalid form method '$method'");
		}

		$this->_method = $method;

		$id = $this->_html_form_name;

?><form id="<?= $id ?>" class="mvc_form" method="<?= $method ?>" action="<?= $action ?>">
<div>
<input class="mvc_form_internal" name="form_name" value="<?= $this->_form_name ?>" type="hidden"></input>
<?php
	}


	/**
	* Output HTML that finishes the form.
	*/
	function html_finish () {
?></div></form>
<?php
	}


	/**
	* Output HTML that displays a field label.
	*
	* @param string field name
	*/
	function html_label ($field_name) {
		$this->_check_field_name($field_name, __FUNCTION__);

		$field_info = $this->_fields[$field_name];

		$id = $this->_html_form_name . '_l_' . $field_name;
		$for = $this->_html_form_name . '_' . $field_name;
		$label = $field_info['label'];

?><label id="<?= $id ?>" class="mvc_form_label" for="<?= $for ?>"><?= $label ?></label>
<?php
	}


	/**
	* Output HTML that displays a field.
	*
	* @param string field name
	*/
	function html_field ($field_name) {
		$this->_check_field_name($field_name, __FUNCTION__);

		$field_info = $this->_fields[$field_name];

		$type = $field_info['type'];

		$func = '_html_' . $type;

		$this->$func($field_name);
	}


	/**
	* Output the HTML for a field type 'text'.
	*
	* @param string field name
	*/
	private function _html_text ($field_name) {

		# common
		$id = $name = $this->_html_form_name . '_' . $field_name;
		$value = $this->_fields[$field_name]['value'];

		$options = $this->_fields[$field_name]['options'];
		$disabled = ($options['enabled'] == 'no') ? ' disabled="disabled"' : '';
		$hidden = ($options['hidden'] == 'yes') ? ' type="hidden"' : '';

		# specific
		$inputclass = 'mvc_form_text_field';

		$size = ( isset($options['size']) AND ($options['size'] > 0) ) ?  $options['size'] : 10;

		$maxlength = ( isset($options['maxlength']) AND ($options['maxlength'] > 0) ) ?  $options['maxlength'] : 80;

		$password = ( isset($options['password']) AND ($options['password'] == 'yes') ) ? ' type="password"' : '';

		$password = ( $hidden AND $password ) ? '' : $password;

		# HTML
?><input id="<?= $id ?>" class="<?= $inputclass ?>" name="<?= $name ?>" value="<?= htmlentities($value) ?>" size="<?= $size ?>" maxlength="<?= $maxlength ?>"<?= $disabled ?><?= $password ?><?= $hidden ?>></input>
<?php

		$this->_html_field_error($field_name);

	}

	/**
	* Output the HTML for a field type 'dropdown'.
	*
	* @param string field name
	*/
	private function _html_dropdown ($field_name) {

		# common
		$id = $name = $this->_html_form_name . '_' . $field_name;
		$value = $this->_fields[$field_name]['value'];

		$options = $this->_fields[$field_name]['options'];
		$disabled = ($options['enabled'] == 'no') ? ' disabled="disabled"' : '';
		$hidden = ($options['hidden'] == 'yes') ? ' type="hidden"' : '';

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

		$this->_html_field_error($field_name);

	}


	/**
	* Output the HTML for a field type 'checkbox'.
	*
	* @param string field name
	*/
	private function _html_checkbox ($field_name) {

		# common
		$id = $name = $this->_html_form_name . '_' . $field_name;
		$value = $this->_fields[$field_name]['value'];

		$options = $this->_fields[$field_name]['options'];
		$disabled = ($options['enabled'] == 'no') ? ' disabled="disabled"' : '';
		$hidden = ($options['hidden'] == 'yes') ? ' type="hidden"' : '';

		# specific
		$inputclass = 'mvc_form_checkbox_field';
		$checked = ( $value === TRUE ) ? ' checked="checked"' : '';

		# HTML
?><input id="<?= $id ?>" class="<?= $inputclass ?>" name="<?= $name ?>" value="checked" type="checkbox"<?= $checked ?><?= $disabled ?><?= $hidden ?>></input>
<?php

		$this->_html_field_error($field_name);

	}


	/**
	* Output the HTML for a field type 'bitmask'.
	*
	* @param string field name
	*/
	private function _html_bitmask ($field_name) {

		# common
		$id = $name = $this->_html_form_name . '_' . $field_name;
		$value = $this->_fields[$field_name]['value'];
		$options = $this->_fields[$field_name]['options'];
		$disabled = ($options['enabled'] == 'no') ? ' disabled="disabled"' : '';
		$hidden = ($options['hidden'] == 'yes') ? ' type="hidden"' : '';

		# specific
		$tableclass = 'mvc_form_bitmask';
		$inputclass = 'mvc_form_bitmask_field';
		$choices = $options['choices'];
		$columns = ( isset($options['columns']) AND ($options['columns'] > 0) AND ($options['columns'] < count($choices)) ) ?  $options['columns'] : 3;

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

			$label_id = $this->_html_form_name . '_l_' . $field_name . '_' . md5($choice);
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

		$this->_html_field_error($field_name);

	}


	/**
	* Output the HTML for a field error.
	*
	* @param string field name
	*/
	private function _html_field_error ($field_name) {
		$type = $this->_fields[$field_name]['type'];
		$valid = $this->_fields[$field_name]['valid'];
		$error = $this->_fields[$field_name]['error'];

		if ( $type == 'text' ) {
?><br/>
<?php
		}

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
	function field_label ($field_name, $label = NULL) {
		$this->_check_field_name($field_name, __FUNCTION__);

		if ( $label === NULL ) {
			return($this->_fields[$field_name]['label']);
		} else {
			$this->_fields[$field_name]['label'] = $label;
		}

	}


	/**
	* Get/Set the value for a field.
	*
	* @param string field name
	* @param mixed optional field value
	*/
	function field_value ($field_name, $value = NULL) {
		$this->_check_field_name($field_name, __FUNCTION__);

		if ( $value === NULL ) {
			return($this->_fields[$field_name]['value']);
		} else {
			$this->_fields[$field_name]['value'] = $value;
		}
	}


	/**
	* Get/Set an option for a field.
	*
	* @param string field name
	* @param string option name
	* @param string optional option value
	*/
	function field_option ($field_name, $option_name, $value = NULL) {
		$this->_check_field_name($field_name, __FUNCTION__);

		if ( $value === NULL ) {
			return($this->_fields[$field_name]['options'][$option_name]);
		} else {
			$this->_fields[$field_name]['options'][$option_name] = $value;
		}

	}


	/**
	* Mark a field as invalid and set a custom error message.
	*
	* @param string field name
	* @param string custom error message
	*/
	function field_invalidate ($field_name, $error_message) {
		$this->_check_field_name($field_name, __FUNCTION__);

		$this->_fields[$field_name]['valid'] = FALSE;
		$this->_fields[$field_name]['error'] = $error_message;
	}


	/**
	* Check to see if the form has been submitted, populate field values.
	* Does not check input.
	*
	* @param string form method, i.e. 'get' or 'post'
	*/
	function submitted ($method) {
		if ( $method == 'post' ) {
			$sub = $_POST;
		} elseif ( $method == 'get' ) {
			$sub = $_GET;
		} else {
			Err::fatal("invalid form method '$method'");
		}

		if ( (! isset($sub['form_name'])) OR ($sub['form_name'] != $this->_form_name) ) {
			return(FALSE);
		}

		foreach ( $this->_fields as $field_name => $UNUSED_field_info ) {

			$form_field_name = $this->_html_form_name . '_' . $field_name;
			$type = $this->_fields[$field_name]['type'];

			if ( $type == 'checkbox' ) {
				if ( isset($sub[$form_field_name]) AND ($sub[$form_field_name] == 'checked') ) {
					$this->_fields[$field_name]['value'] = TRUE;
				}

			} elseif ( $type == 'bitmask' ) {
				$choices = $this->_fields[$field_name]['options']['choices'];

				$bitmask = 0;

				foreach ( $choices as $choice => $choice_value ) {
					$fld_name = $form_field_name . '_' . md5($choice);

					if ( isset($sub[$fld_name]) AND ($sub[$fld_name] == 'checked') ) {
						$bitmask |= $choice_value;
					}
				
				}

				$this->_fields[$field_name]['value'] = $bitmask;

			} else {
				if ( isset($sub[$form_field_name]) ) {
					$this->_fields[$field_name]['value'] = $sub[$form_field_name];
				}
			}

		}

		return(TRUE);
	}


	/**
	* Check all fields in a form.
	*/
	function check_all () {
		$errors = 0;

		foreach ( $this->_fields as $field_name => $UNUSED_field_info ) {

			if ( ! $this->_check_field($field_name) ) {
				$errors++;
			}

		}

		if ( $errors ) {
			return(FALSE);
		} else {
			return(TRUE);
		}
	}


	/**
	* Check a particular field.
	*
	* @param string field name
	*/
	private function _check_field ($field_name) {
		$type = $this->_fields[$field_name]['type'];
		$value = $this->_fields[$field_name]['value'];
		$checks = $this->_fields[$field_name]['checks'];

		$this->_fields[$field_name]['valid'] = TRUE;
		$this->_fields[$field_name]['error'] = '';

		if ( ($type == 'checkbox') AND $value AND ($value !== TRUE) ) {
			$this->_fields[$field_name]['value'] = FALSE;
			return(TRUE);
		}

		if ( $type == 'dropdown' ) {
			$choices = $this->_fields[$field_name]['options']['choices'];

			if ( $value AND (! in_array($value, $choices)) ) {
				$this->_fields[$field_name]['valid'] = FALSE;
				$this->_fields[$field_name]['error'] = 'Invalid value submitted.';
				return(FALSE);
			}
		}

		if ( $type = 'bitmask' ) {
			if ( $value < 0 ) {
				$this->_fields[$field_name]['value'] = 0;
			}
		}

		foreach ( $checks as $check => $check_errmsg ) {

			if ( ! method_exists($this, $ckfunc = '_ck_' . $check) ) {
				Err::fatal("invalid check function '$check' declared in form");
			}

			$error = $this->$ckfunc($value);

			if ( $error !== TRUE ) {
				$this->_fields[$field_name]['valid'] = FALSE;

				$this->_fields[$field_name]['error'] = ( $check_errmsg ) ? $check_errmsg : $error;

				return(FALSE);
			}

		}

		return(TRUE);
	}


	/**
	* Add a check to the list of checks for a field.
	*
	* @param string field name
	* @param string check name
	* @param string optional error message
	*/
	function add_check ($field_name, $check, $error = '') {
		$this->_check_field_name($field_name, __FUNCTION__);

		$checks = $this->_fields[$field_name]['checks'];

		if ( array_keys($checks, $check, TRUE) ) {
			return;
		}

		if ( ! method_exists($this, $ckfunc = '_ck_' . $check) ) {
			Err::fatal("check '$check' is invalid, method '$ckfunc' not found");
		}

		$this->_fields[$field_name]['checks'][$check] = $error;
	}


	/**
	* Check a field name.
	*
	* @param string field name
	*/
	private function _check_field_name ($field_name, $func) {
		if ( ! isset($this->_fields[$field_name]) ) {
			Err::fatal("$func() invalid field name '$field_name'");
		}
	}


	/**
	* Sample check function - checks for required value.
	*
	* @param mixed value
	*/
	private function _ck_required ($value) {
		if ( empty($value) ) {
			return('This field requires a value.');
		} else {
			return(TRUE);
		}
	}


	/**
	* Sample check function - checks for all digits, empty is OK.
	*
	* @param mixed value
	*/
	private function _ck_digits ($value) {
		if ( empty($value) OR (! preg_match('/[^0-9]/', $value)) ) {
			return(TRUE);
		} else {
			return('Please enter numbers only.');
		}
	}


	function __destruct () {}
}