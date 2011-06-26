<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
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
	private $_columns = array();
	private $_values = array();

	/**
	* Create a new BaseModel object.
	*
	* @return BaseModel a new BaseModel object
	*/
	function __construct () {
		global $DB;
		global $CONFIG;

		$this->_name = strtolower( str_replace("Model", "", get_class($this)) );

		if ( isset ($this->table) ) {
			$this->_table = $this->table;
		} else {
			$prefix = $CONFIG->getVal("database.prefix");
			$this->_table = $prefix . Inflection::pluralize($this->_name);
		}

		$this->_columns = $DB->describe($this->_table);
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


	/**
	* Save a database model object.
	*
	* @param bool log the query
	* @return integer the new insert ID
	*/
	function create ($log_query = TRUE) {
		global $DB;

		if ( ! isset ($DB) ) {
			Err::fatal("Unable to create record, no database connection present.");
		}

		if ( in_array("created_at", $this->_columns) ) {
			$this->_values["created_at"] = "DB:now()";
		}

		return( $DB->create($this->_table, $this->_values, $log_query) );
	}


	/**
	* Update a database model object.
	*
	* @param bool log the query
	* @return bool true or fatal error
	*/
	function update ($log_query = TRUE) {
		global $DB;

		if ( ! isset ($DB) ) {
			Err::fatal("Unable to update record, no database connection present.");
		} elseif ( ! isset ($this->_values["id"]) ) {
			Err::fatal("Unable to update record, primary key (column 'id') not set.");
		}

		return( $DB->update($this->_table, $this->_values, "id", $log_query) );
	}


	/**
	* Load the model with the values from a specific record, search by column
	* name=>value.
	*
	* @param string column name
	* @param mixed column value
	* @param bool log the query
	*/
	function load ($name, $value, $log_query = TRUE) {
		$columns = preg_replace('/^(.*)$/', "$this->_name.$1", $this->_columns);
		$search_col = $this->_name . '.' . $name;

		Dbg::var_dump('this columns', $this->_columns);
		Dbg::var_dump('columns', $columns);
		Dbg::var_dump('search_col', $search_col);
		
		$search = new Search($this);
		$search->select($columns);
		$search->where( NULL, array( array($search_col, Search::op_eq, $value)) );
		$results = $search->go();

		if ( ($res_count = count($results)) != 1 ) {
			global $ERROR;
			$ERROR = "Search results found $res_count matches for '$search_col' = '$value'.";
			return(FALSE);
		}

		return(TRUE);
	}


	function __destruct () {}
}
