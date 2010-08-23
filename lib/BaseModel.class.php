<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
*/


/**
* Base Model class - handle the lowest level functions of a database model
* object.
*
* @package MCS_MVC_API
*/
class BaseModel {
	private $_name;
	private $_table;
	private $_columns;
	private $_values;

	/**
	* Create a new BaseModel object.
	* <code>
	* class ItemModel extends BaseModel {
	* }
	* </code>
	*
	* @return BaseModel a new BaseModel object
	*/
	function __construct () {
		global $DB;

		$this->name = strtolower (str_replace ("Model", "", get_class ($this)));
		$this->table = Inflection::pluralize ($this->name);
		$this->columns = $DB->describe ($this->table);

		$this->_name = strtolower (str_replace ("Model", "", get_class ($this)));
		$this->_table = Inflection::pluralize ($this->name);
		$this->_columns = $DB->describe ($this->table);
	}


	/**
	* Save a database model object.
	*
	* @param bool log the query
	* @return integer the insert ID or number of records changed
	*/
	function create ($log_query = TRUE) {
		global $DB;

		if (! isset ($DB)) {
			Err::fatal("Unable to create record, no database connection present.");
		}

		return ( $DB->create($this->_table, $this->_columns, $this->_values, $log_query) );
	}


	/**
	* Update a database model object.
	*
	* @param bool log the query
	* @return bool true
	*/
	function update ($log_query = TRUE) {
		global $DB;

		if (! isset ($DB)) {
			Err::fatal("Unable to create record, no database connection present.");
		}

		return ( $DB->update($this->_table, $this->_columns, $this->_values, $log_query) );
	}


	/**
	* Return the instantiated model name.
	*
	* @return string model name
	*/
	function name () {
		return ($this->_name);
	}


	/**
	* Return the name of the table used by this model.
	*
	* @return string table name
	*/
	function table () {
		return ($this->_table);
	}


	/**
	* Return the list of columns in the model's table.
	*
	* @return array list of column names
	*/
	function columns () {
		return ($this->_columns);
	}


	/**
	* Get or set a particular column value.
	*
	* @param string column name
	* @param mixed optional value to set
	* @return mixed column value
	*/
	function value ($column, $value = FALSE) {
		if (! in_array ($column, $this->_columns)) {
			Err::fatal(sprintf ("Invalid column name '%s'.", $column));
		}

		if ($value === FALSE) {
			if (isset ($this->_values[$column])) {
				$value = $this->_values[$column];
			} else {
				$value = NULL;
			}
		} elseif ($value === NULL) {
			unset ($this->_values[$column]);
		} else {
			$this->_values[$column] = $value;
		}

		return ($value);
	}


	/**
	* Return a list of columns and their respective values.  Only columns that
	* have a value set will appear in the list.
	*
	* @return hash column name => value
	*/
	function values () {
		return ($this->_values);
	}


	function __destruct () {}
}
