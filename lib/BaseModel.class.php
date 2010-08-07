<?php

/**
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version v0.0.0
*/

/**
* Database model base class.
* @package MVCAPI
*/
class BaseModel {
	# Database object.
	private $_db;

	# Name of this model.
	private $_name;

	# Database table associated with this model.
	private $_table;

	# List of columns in the table.
	private $_columns;

	# Values assigned to columns.
	private $_values;


	/**
	* Create a new BaseModel object.
	* <code>
	* class DatabaseModel extends BaseModel {
	* }
	* </code>
	* @param Database an instance of a Database object
	* @return BaseModel
	*/
	function __construct (Database $db) {
		$this->_db = $db;

		$this->_name = strtolower (str_replace ("Model", "", get_class ($this)));

		$this->_table = Inflection::pluralize ($this->_name);

		$this->_columns = $this->_db->describe ($this->_table);
	}


	/**
	* Get the list of column names in this model.
	* @return array
	*/
	function getColNames () {
		return ($this->_columns);
	}


	/**
	* Get the value for a particular column.
	* @param string name of the column
	* @return string the value of the column
	*/
	function getColVal ($column = NULL) {
		if (is_null ($column)) {
			Error::fatal ("column must be specified");
		} elseif (! in_array ($column, $this->_columns)) {
			Error::fatal (sprintf ("invalid column '%s'", $column));
		}

		return ($this->_values[$column]);
	}


	/**
	* Get the values for all columns.
	* @return hash list of all columns and their associated values
	*/
	function getColVals () {
		return ($this->_values);
	}


	/**
	* Get the name of this model.
	* @return string model name
	*/
	function getName () {
		return ($this->_name);
	}


	/**
	* Get the name of the database table associated with this model.
	* @return string table name
	*/
	function getTable () {
		return ($this->_table);
	}


	/**
	* Set the value for a column.
	* @param string fully qualified column name
	* @param string value to assign to the column
	*/
	function setColVal ($column = NULL, $value = NULL) {
		if (is_null ($column) OR is_null ($value)) {
			Error::fatal ("column and value must both be specified");
		} elseif (! in_array ($column, $this->_columns)) {
			Error::fatal (sprintf ("invalid column '%s'", $column));
		}

		$this->_values[$column] = $value;
	}


	function __destruct () {}
}
