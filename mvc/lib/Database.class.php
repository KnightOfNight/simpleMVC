<?php


/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/


/**
* Manage a database connection.
* @package simpleMVC
*/
class Database {


	private $_dbh;
	private $_last_query;
	private $_transaction;


	function __construct () {}


	/**
	* Connect to a database.
	*
	* @param array database config
	*/
	function connect ( $cfg ) {
		$host = $cfg['host'];
		$port = $cfg['port'];
		$name = $cfg['name'];
		$user = $cfg['user'];
		$pass = $cfg['pass'];

		$dsn = sprintf("%s:host=%s;port=%d;dbname=%s", "mysql", $host, $port, $name);

		try {
			$this->_dbh = new PDO($dsn, $user, $pass);
		} catch (PDOException $e) {
			Err::fatal($e->getMessage());
		}
	}


	/**
	* Describe a table and return the list of column names.
	*
	* @param string table name
	*
	* @return array column names
	*/
	static function describe ($table) {
		global $simpleMVC;

		if ( (! isset($simpleMVC['database'])) OR (! ($db = $simpleMVC['database']) instanceof Database) ) {
			Err::fatal("Database::" . __function__ . "() called before database connection setup.");
		}

		return( $db->___describe($table) );
	}


	/**
	* Internal function to describe a table and return the list of column names.
	*
	* @param string table name
	*
	* @return array column names
	*/
	function ___describe ($table) {
		if ( ! $columns = Cache::value($cache_name = "describe_" . $table) ) {
			$columns = array();

			$query = "describe " . $table;

			$this->_last_query = $query;

			if ( ($results = $this->_dbh->query($query)) === FALSE ) {
				Err::set_last($this->_last_error());
				return(FALSE);
			}

			while ( $result = $results->fetch() ) {
				array_push($columns, $result["Field"]);
			}

			Cache::value($cache_name, $columns);
		}

		return($columns);
	}

	
	/**
	* Perform a database select, building the query from the passed criteria.
	*
	* @param mixed hash of all search criteria
	*
	* @return mixed query results
	*/
	static function select ($criteria) {
		global $simpleMVC;

		if ( (! isset($simpleMVC['database'])) OR (! ($db = $simpleMVC['database']) instanceof Database) ) {
			Err::fatal("Database::" . __function__ . "() called before database connection setup.");
		}

		return( $db->___select($criteria) );
	}


	/**
	* Internal function to perform a database select.
	*
	* @param mixed hash of all search criteria
	*
	* @return mixed query results
	*/
	function ___select ($criteria) {
		$query = "SELECT ";

		# columns and expressions list
		$columns = $criteria["select"];

		$column_list = "";
		foreach ($columns as $col => $alias) {
			if ($column_list) {
				$column_list .= " , ";
			}

			$column_list .= $col;

			if ($alias) {
				$column_list .= " AS '" . $alias . "'";
			}
		}
		$query .= $column_list;


		# FROM
		$model = $criteria["models"][0];
		$table = $model->table();
		$query .= " FROM " . $table . " AS " . $model->name();


		# LEFT JOIN
		$joins = $criteria["leftjoins"];
		foreach ($joins as $join) {
			$table = $join[0];
			$model = $join[1];
			$colA = $join[2];
			$colB = $join[3];
			$query .= " LEFT JOIN " . $table . " AS " . $model . " ON " . $colA . "=" . $colB;
		}


		# WHERE
		Log::msg(Log::DEBUG, "Database::select() - parsing 'where' information");
		$wheres = $criteria["where"];
		$where_str = "";
		$values = array();
		foreach ($wheres as $where) {
			$operator = $where[0];
			$clauses = $where[1];

			Log::msg(Log::DEBUG, "operator = '" . $operator . "'");
			Log::msg(Log::DEBUG, "clauses = '" . $clauses . "'");

			if ($where_str) {
				$where_str .= " " . $operator;
			} else {
				$where_str = " WHERE";
			}

			$clause_str = "";
			Log::msg(Log::DEBUG, "clause_str = '" . $clause_str . "'");

			foreach ($clauses as $clause) {
				Log::msg(Log::DEBUG, "found clause");

				if (! $clause_str) {
					$clause_str = " (";
				}

				if (is_array ($clause)) {
					Log::msg(Log::DEBUG, "clause is an array");

					$col = $clause[0];
					$op = $clause[1];
					$val = $clause[2];

					Log::msg(Log::DEBUG, "col = '" . $col . "'");
					Log::msg(Log::DEBUG, "op = '" . $op . "'");
					Log::msg(Log::DEBUG, "val = '" . $val . "'");

					if ( preg_match('/^DB:/', $val) ) {
						$val = preg_replace('/^DB:/', "", $val);
						$clause_str .= $col . " " . $op . " " . $val;
					} else {
						$clause_str .= $col . " " . $op . " ?";
						array_push ($values, $val);
					}
				} else {
					Log::msg(Log::DEBUG, "clause is an operator");

					$clause_str .= " " . $clause . " ";
				}
			}
			$clause_str .= ")";

			$where_str .= $clause_str;
		}

		if ($where_str) {
			$query .= $where_str;
		}


		# ORDER BY
		$orderby_str = "";
		if ( $orderbys = $criteria["orderby"] ) {
			foreach ($orderbys as $orderby) {
				$col = $orderby[0];
				$ord = $orderby[1];

				if ($orderby_str) {
					$orderby_str .= " , ";
				} else {
					$orderby_str = " ORDER BY ";
				}

				$orderby_str .= $col . " " . $ord;
			}
					
			$query .= $orderby_str;
		}


		# LIMIT
		$limit = $criteria["limit"];
		$page = $criteria["page"];
		if ($limit) {
			$query .= " LIMIT " . $limit;

			if ($page) {
				$offset = ($page - 1) * $limit;
				$query .= " OFFSET " . $offset;
			}
		}


		$this->_last_query = $query;

		$stmnt = $this->_dbh->prepare($query);

		$index = 0;
		foreach ($values as $val) {
			if ($stmnt->bindValue($index + 1, $val) === FALSE) {
				Err::set_last( sprintf("Unable to bind value '%s' to parameter %d", $val, $index + 1) );
				return(FALSE);
			}
			$index++;
		}

		Log::msg(Log::DEBUG, "Database::select() - database query = '$query'");

		if ($stmnt->execute() === FALSE) {
			Err::set_last($this->_last_error($stmnt));
			return(FALSE);
		}

		return ( $stmnt->fetchAll(PDO::FETCH_BOTH) );
	}


	/**
	* Create a new database record.
	*
	* @param string table name
	* @param hash column name => value
	* @param bool TRUE => log the query
	*
	* @return integer new record id or FALSE on error
	*/
	static function create ($table, $values, $log_query = TRUE) {
		global $simpleMVC;

		if ( (! isset($simpleMVC['database'])) OR (! ($db = $simpleMVC['database']) instanceof Database) ) {
			Err::fatal("Database::" . __function__ . "() called before database connection setup.");
		}

		return( $db->___create($table, $values, $log_query) );
	}


	/**
	* Internal function to create a new database record.
	*
	* @param string table name
	* @param hash column name => value
	* @param bool TRUE => log the query
	*
	* @return integer new record id or FALSE on error
	*/
	function ___create ($table, $values, $log_query = TRUE) {
		if ( empty($values) ) {
			Err::set_last("No column values set.  Cannot create record.");
			return(FALSE);
		}

	 	$query = "INSERT INTO " . $table . " SET " . $this->_makeset($values);

		$this->_last_query = $query;

		if ( $log_query === TRUE ) {
			Log::msg(Log::DEBUG, "database query = '" . $query . "'");
		}

		$stmnt = $this->_dbh->prepare($query);

		if ( $stmnt->execute($this->_getparams($values)) === FALSE ) {
			Err::set_last($this->_last_error($stmnt));
			return(FALSE);
		}

		return( $this->_dbh->lastInsertId() );
	}


	/**
	* Update a database record.
	*
	* @param string table name
	* @param hash column name => value
	* @param string primary key column name
	* @param bool TRUE => log the query
	*
	* @return bool TRUE => update OK
	*/
	static function update ($table, $values, $key, $log_query = TRUE) {
		global $simpleMVC;

		if ( (! isset($simpleMVC['database'])) OR (! ($db = $simpleMVC['database']) instanceof Database) ) {
			Err::fatal("Database::" . __function__ . "() called before database connection setup.");
		}

		return($db->___update($table, $values, $key, $log_query));
	}


	/**
	* Internal function to update a database record.
	*
	* @param string table name
	* @param hash column name => value
	* @param string primary key column name
	*
	* @param bool TRUE => log the query
	*
	* @return bool TRUE => update OK
	*/
	function ___update ($table, $values, $key, $log_query = TRUE) {
		if ( empty($values) ) {
			Err::set_last("No column values set.  Cannot update record.");
			return(FALSE);
		}

		if ( ! isset ($values[$key]) ) {
			Err::set_last( sprintf("No value set for primary key column '%s'.", $key) );
			return(FALSE);
		}

		$query = "UPDATE " . $table . " SET " . $this->_makeset($values) . " WHERE " . $key . " = :id";

		$this->_last_query = $query;

		if ( $log_query === TRUE ) {
			Log::msg(Log::DEBUG, "database query = '" . $query . "'");
		}

		$stmnt = $this->_dbh->prepare($query);

		if ( $stmnt->execute($this->_getparams($values)) === FALSE ) {
			Err::set_last( $this->_last_error($stmnt) );
			return(FALSE);
		}

		return (TRUE);
	}


	/**
	* Delete a database record.
	*
	* @param string table name
	* @param hash column name => value
	* @param string primary key column name
	* @param bool TRUE => log the query
	*
	* @return bool TRUE => OK
	*/
	static function delete ($table, $values, $key, $log_query = TRUE) {
		global $simpleMVC;

		if ( (! isset($simpleMVC['database'])) OR (! ($db = $simpleMVC['database']) instanceof Database) ) {
			Err::fatal("Database::" . __function__ . "() called before database connection setup.");
		}

		return($db->___delete($table, $values, $key, $log_query));
	}


	/**
	* Internal function to delete a database record.
	*
	* @param string table name
	* @param hash column name => value
	* @param string primary key column name
	* @param bool TRUE => log the query
	*
	* @return bool TRUE => OK
	*/
	function ___delete ($table, $values, $key, $log_query = TRUE) {
		if ( ! isset ($values[$key]) ) {
			Err::set_last( sprintf("No value set for primary key column '%s'.", $key) );
			return(FALSE);
		}

		$query = "DELETE FROM " . $table . " WHERE " . $key . " = :id";

		$this->_last_query = $query;

		if ( $log_query === TRUE ) {
			Log::msg(Log::DEBUG, "database query = '" . $query . "'");
		}

		$stmnt = $this->_dbh->prepare($query);

		if ( $stmnt->execute( array(':id' => $values[$key]) ) === FALSE ) {
			Err::set_last( $this->_last_error($stmnt) );
			return(FALSE);
		}

		return (TRUE);
	}


	/**
	* Get the last database query that was executed.
	*
	* @return string
	*/
	function getLastQuery () {
		return($this->_last_query);
	}


	/**
	* Begin a database transaction.
	*
	* @return mixed TRUE or error message
	*/
	static function transaction_begin () {
		global $simpleMVC;

		if ( (! isset($simpleMVC['database'])) OR (! ($db = $simpleMVC['database']) instanceof Database) ) {
			Err::fatal("Database::" . __function__ . "() called before database connection setup.");
		}

		return( $db->___transaction_begin() );
	}


	/**
	* Internal function to begin a database transaction.
	*
	* @return mixed TRUE or error message
	*/
	function ___transaction_begin () {
		if ( $this->_transaction ) {
			return('Database transaction already in progress.');
		}

		$this->_last_query = '';

		if ( $this->_dbh->beginTransaction() ) {
			return($this->_transaction = TRUE);
		} else {
			return("Unable to begin transaction.\n\n" . $this->_last_error());
		}
	}


	/**
	* Commit a database transaction.
	*
	* @return mixed TRUE or error message
	*/
	static function transaction_commit () {
		global $simpleMVC;

		if ( (! isset($simpleMVC['database'])) OR (! ($db = $simpleMVC['database']) instanceof Database) ) {
			Err::fatal("Database::" . __function__ . "() called before database connection setup.");
		}

		return( $db->___transaction_commit() );
	}

	/**
	* Internal function to commit a database transaction.
	*
	* @return mixed TRUE or error message
	*/
	function ___transaction_commit () {
		if ( ! $this->_transaction ) {
			return('No database transaction in progress.');
		}

		$this->_last_query = '';

		if ( $this->_dbh->commit() ) {
			$this->_transaction = FALSE;

			return(TRUE);
		} else {
			return("Unable to commit transaction.\n\n" . $this->_last_error());
		}
	}


	/**
	* Return a string containing details about any error on the last query.
	*
	* @param mixed optional statement handle to check for errors
	*/
	private function _last_error ($sth = NULL) {
		$error = '';

		if ( $this->_last_query ) {
			$error .= "Unable to execute database query.\n\n$this->_last_query\n\n";
		}

		$dbh_info = $this->_dbh->errorInfo();
		$dbh_code = $dbh_info[0];
		$drv_code = ( isset($dbh_info[1]) ) ? $dbh_info[1] : NULL;
		$drv_msg = ( isset($dbh_info[2]) ) ? $dbh_info[2] : NULL;

		$error .= "Database error: SQLSTATE $dbh_code";

		if ( $drv_code ) {
			$error .= ", $drv_code";
		}

		if ( $drv_msg ) {
			$error .= ", $drv_msg";
		}

		if ( $sth ) {
			$sth_info = $sth->errorInfo();

			$sth_code = $sth_info[0];
			$drv_code = ( isset($sth_info[1]) ) ? $sth_info[1] : NULL;
			$drv_msg = ( isset($sth_info[2]) ) ? $sth_info[2] : NULL;

			$error .= "\n\nStatement error: SQLSTATE $sth_code";

			if ( $drv_code ) {
				$error .= ", $drv_code";
			}

			if ( $drv_msg ) {
				$error .= ", $drv_msg";
			}
		}
	
		return($error);
	}


	private function _makeset ($values) {
		$set = "";

		foreach ($values as $col_name => $col_val) {
			$set .= $set ? " , " : "";

			if ( preg_match('/^DB:/', $col_val) ) {
				$val = preg_replace('/^DB:/', "", $col_val);
				$set .= $col_name . " = " . $val;
			} else {
				$param_name = ":" . $col_name;
				$set .= $col_name . " = " . $param_name;
			}
		}

		return($set);
	}


	private function _getparams ($values) {
		$params = array();

		foreach ($values as $col_name => $col_val) {
			if ( preg_match('/^DB:/', $col_val) ) {
				continue;
			}

			$param_name = ":" . $col_name;

			$params[$param_name] = $col_val;
		}

		return($params);
	}


	function __destruct () {}


}
