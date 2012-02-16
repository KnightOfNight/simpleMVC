<?php


/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/


/**
* Base Model class - handle the lowest level functions of a database model
* object.
*
* @package simpleMVC
*/
class BaseModel {

	private $_model_name;
	private $_table_name;
	private $_columns = array();
	private $_values = array();

	/**
	* Create a new BaseModel object.
	*
	* @return BaseModel a new BaseModel object
	*/
	function __construct () {
		$this->_model_name = strtolower( str_replace('Model', '', get_class($this)) );

		if ( isset ($this->table) ) {
			$this->_table_name = $this->table;
		} else {
			$prefix = Config::get('database.prefix');
			$this->_table_name = $prefix . Inflection::pluralize($this->_model_name);
		}

		if ( ($this->_columns = Database::describe($this->_table_name)) === FALSE ) {
			Err::critical("Unable to load database model '$this->_model_name', unable to describe table.\n\n" . Err::last());
		}

		if ( ! in_array('id', $this->_columns) ) {
			Err::critical("Unable to load database model '$this->_model_name', primary key column 'id' not found in table.");
		}
	}


	/**
	* Return the instantiated model name.
	*
	* @return string model name
	*/
	function name () {
		return ($this->_model_name);
	}


	/**
	* Return the name of the table used by this model.
	*
	* @return string table name
	*/
	function table () {
		return ($this->_table_name);
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
			Err::critical("Invalid column name '$column'.");
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
		if ( in_array('id', array_keys($this->_values)) ) {
			Err::critical("Unable to create record, primary key column 'id' is already set.");
		}

		$values = $this->_values;

		if ( in_array('created_at', $this->_columns) ) {
			$values['created_at'] = 'DB:now()';
		}

		if ( in_array('updated_at', array_keys($this->_values)) ) {
			unset($values['updated_at']);
		}

		if ( ($ret = Database::create($this->_table_name, $values, $log_query)) === FALSE ) {
			Err::critical("Unable to create database record.\n\n" . Err::last());
		}

		return($ret);
	}


	/**
	* Update a database model object.
	*
	* @param bool log the query
	* @return bool true or fatal error
	*/
	function update ($log_query = TRUE) {
		if ( (! in_array('id', array_keys($this->_values))) OR (! isset($this->_values['id'])) ) {
			Err::critical("Unable to update record, primary key column 'id' is not set.");
		}

		$values = $this->_values;

		if ( in_array('created_at', $this->_columns) ) {
			unset($values['created_at']);
		}

		if ( in_array('updated_at', $this->_columns) ) {
			$values['updated_at'] = 'DB:now()';
		}

		if ( ($ret = Database::update($this->_table_name, $values, 'id', $log_query)) === FALSE ) {
			Err::critical("Unable to update database record.\n\n" . Err::last());
		}

		return($ret);
	}


	/**
	* Delete a database model object.
	*
	* @param bool log the query
	* @return bool true or fatal error
	*/
	function delete ($log_query = TRUE) {
		if ( ! isset($this->_values['id']) ) {
			Err::critical("Unable to delete record, primary key column 'id' is not set.");
		}

		$values = $this->_values;

		if ( ($ret = Database::delete($this->_table_name, $values, 'id', $log_query)) === FALSE ) {
			Err::critical("Unable to delete database record.\n\n" . Err::last());
		}

		return($ret);
	}


	/**
	* Load the model with the values from a specific record, search by column
	* name=>value.
	*
	* @param string column name
	* @param mixed column value
	* @param bool log the query
	*/
	function load ($column_name, $value, $log_query = TRUE) {
		$where_col = $this->_model_name . '.' . $column_name;

		$select_cols = preg_replace('/^(.*)$/', "$this->_model_name.$1", $this->_columns);

#		Dbg::var_dump('this columns', $this->_columns);
#		Dbg::var_dump('select_cols', $select_cols);
#		Dbg::var_dump('where_col', $where_col);
		
		$search = new Search($this);
		$search->select($select_cols);
		$search->where( NULL, array( array($where_col, Search::op_eq, $value)) );
		$results = $search->go();

		if ( ($res_count = count($results)) > 1 ) {
			Err::critical("BaseModel::load() - unable to load search results.  Found MULTIPLE matches for '$where_col'='$value'.");
		} elseif ( $res_count < 1 ) {
			Err::critical("BaseModel::load() - unable to load search results.  Found NO matches for '$where_col'='$value'.");
		}

#		Dbg::var_dump('results', $results);

		$result = $results[0];

		foreach ( $select_cols as $select_col ) {
			$model_col = preg_replace("/^$this->_model_name\./", "", $select_col);

			if ( ! in_array($model_col, $this->_columns) ) {
				Err::critical("BaseModel::load() - unable to load search results.  Found unknown table column '$select_col'.");
			}

			$this->_values[$model_col] = $result[$select_col];
		}

		return(TRUE);
	}


	/**
	* Check for the existence of a row matching the specified criteria
	* column_name=>value.
	*
	* @param string column name
	* @param mixed column value
	* @param bool log the query
	* @return int number of rows found
	*/
	function exists ($column_name, $value, $log_query = TRUE) {
		$where_col = $this->_model_name . '.' . $column_name;

#		$select_cols = preg_replace('/^(.*)$/', "$this->_model_name.$1", $this->_columns);

		$select_cols = $this->_model_name . ".id";

#		Dbg::var_dump('this columns', $this->_columns);
#		Dbg::var_dump('select_cols', $select_cols);
#		Dbg::var_dump('where_col', $where_col);
		
		$search = new Search($this);
		$search->select($select_cols);
		$search->where( NULL, array( array($where_col, Search::op_eq, $value)) );
		$results = $search->go();

		return( count($results) );
	}


	function __destruct () {}
}
