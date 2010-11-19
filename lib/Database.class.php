<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.2.0
*/


/**
* Manage a database connection.
* @package MCS_MVC_API
*/
class Database {
	private $_dbh;
	private $_log_queries;
	private $_last_query;


	/**
	* Connect to a database.
	*
	* @param string host
	* @param integer port number
	* @param string database name
	* @param string username
	* @param string password
	*/
	function connect ($host, $port, $name, $user, $pass) {
		$dsn = sprintf ("%s:host=%s;port=%d;dbname=%s", "mysql", $host, $port, $name);

		try {
			$this->_dbh = new PDO ($dsn, $user, $pass);
		} catch (PDOException $e) {
			Err::fatal($e->getMessage());
		}
	}


	/**
	* Describe a table and return the list of column names.
	*
	* @param string table name
	* @return array list of column names
	*/
	function describe ($table) {
		if ( ! $columns = Cache::value($cache_name = "describe_" . $table) ) {
			$columns = array();

			$query = "describe " . $table;

			if ( ($results = $this->_dbh->query($query)) === FALSE ) {
				Err::fatal($this->_pdoError());
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
	* @return mixed query results
	*/
	function select ($criteria) {
		global $L;

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
#			} else {
#				$column_list .= " AS " . preg_replace('/\./', "_", $col);
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
		$L->msg(Log::DEBUG, "Database::select() - parsing 'where' information");
		$wheres = $criteria["where"];
		$where_str = "";
		$values = array();
		foreach ($wheres as $where) {
			$operator = $where[0];
			$clauses = $where[1];

			$L->msg(Log::DEBUG, "operator = '" . $operator . "'");
			$L->msg(Log::DEBUG, "clauses = '" . $clauses . "'");

			if ($where_str) {
				$where_str .= " " . $operator;
			} else {
				$where_str = " WHERE";
			}

			$clause_str = "";
			$L->msg(Log::DEBUG, "clause_str = '" . $clause_str . "'");

			foreach ($clauses as $clause) {
				$L->msg(Log::DEBUG, "found clause");

				if (! $clause_str) {
					$clause_str = " (";
				}

				if (is_array ($clause)) {
					$L->msg(Log::DEBUG, "clause is an array");

					$col = $clause[0];
					$op = $clause[1];
					$val = $clause[2];

					$L->msg(Log::DEBUG, "col = '" . $col . "'");
					$L->msg(Log::DEBUG, "op = '" . $op . "'");
					$L->msg(Log::DEBUG, "val = '" . $val . "'");

					$clause_str .= $col . " " . $op . " ?";

					array_push ($values, $val);
				} else {
					$L->msg(Log::DEBUG, "clause is an operator");

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

#Dbg::var_dump ("query", $query);

		$stmnt = $this->_dbh->prepare($query);

		$index = 0;
		foreach ($values as $val) {
			if ($stmnt->bindValue($index + 1, $val) === FALSE) {
				Err::fatal (sprintf ("unable to bind value '%s' to parameter %d", $val, $index + 1));
			}
			$index++;
		}

		$L->msg(Log::DEBUG, "Database::select() - database query = '$query'");

		if ($stmnt->execute() === FALSE) {
			Err::fatal ($this->_pdoError());
		}

		return ($stmnt->fetchAll(PDO::FETCH_BOTH));
	}


	/**
	* Create a new database record.
	*
	* @param string table name
	* @param hash column name => value
	* @param bool TRUE => log the query
	* @return integer new record id
	*/
	function create ($table, $values, $log_query = TRUE) {
		if ( empty($values) ) {
			Err::fatal("No column values set.  Cannot update or create record.");
		}

	 	$query = "INSERT INTO " . $table . " SET " . Database::makeset($values);

		if ( $log_query === TRUE ) {
			global $L;
			$L->msg(Log::DEBUG, "database query = '" . $query . "'");
		}

		$stmnt = $this->_dbh->prepare($query);

		if ( $stmnt->execute(Database::getparams($values)) === FALSE ) {
			Err::fatal($this->_pdoError());
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
	*/
	function update ($table, $values, $key, $log_query = TRUE) {
		if ( empty($values) ) {
			Err::fatal("No column values set.  Cannot update or create record.");
		}

		if ( ! isset ($values[$key]) ) {
			Err::fatal( sprintf("No value set for primary key column '%s'.", $key) );
		}

		$query = "UPDATE " . $table . " SET " . Database::makeset($values) . " WHERE id = :id";

		if ( $log_query === TRUE ) {
			global $L;
			$L->msg(Log::DEBUG, "database query = '" . $query . "'");
		}

		$stmnt = $this->_dbh->prepare($query);

		if ( $stmnt->execute(Database::getparams($values)) === FALSE ) {
			Err::fatal( $this->_pdoError() );
		}

		return (TRUE);
	}


	static private function makeset ($values) {
		$set = "";

#Dbg::msg("Database::makeset");

		foreach ($values as $col_name => $col_val) {
#Dbg::var_dump("col_name", $col_name);
#Dbg::var_dump("col_val", $col_val);

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


	static private function getparams ($values) {
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


	/**
	* Save a database record.

* from the passed model.  If the 'id' column has a
	* value, then the record will updated.  Otherwise a new record will be
	* created.
	*
	* @param string table name
	* @param array list of column names
	* @param hash column name => value
	* @return integer current value of the 'id' column or new value on create
	*/
	function save ($table, $columns, $values) {
		if (empty ($values)) {
			Err::fatal("No column values set.  Cannot update or create record.");
		}

		$query = "";
		$set = "";
		$where = "";
		$params = array();

		if (isset ($values["id"])) {
			$query = "UPDATE " . $table . " SET ";
			$where .= "WHERE id = :id";
		} else {
			$query = "INSERT INTO " . $table . " SET ";

			if (in_array ("created_at", $columns)) {
				$set = "created_at = NULL";
			}
		}

		foreach ($values as $col_name => $col_val) {
			$param_name = ":" . $col_name;

			$params[$param_name] = $col_val;

			$set .= $set ? " , " : "";
			$set .= $col_name . " = " . $param_name;
		}

		$query .= $set;

		$query .= $where ? " " . $where : "";

#Dbg::var_dump("params", $params);
#Dbg::var_dump("query", $query);

		$stmnt = $this->_dbh->prepare($query);

#Dbg::var_dump("stmnt", $stmnt);

		if ($stmnt->execute($params) === FALSE) {
			Err::fatal ($this->_pdoError());
		}

		if (isset ($values["id"])) {
			return ($values["id"]);
		} else {
			return ($this->_dbh->lastInsertId());
		}
	}


	# Delete a database record for the passed model.
	#
	function delete ($model) {
		if (! ($model instanceof BaseModel)) {
			Err::fatal ("passed model is not an instance of BaseModel");
		}

		$table = $model->table;
		$values = $model->values;

		if (! isset ($values["id"])) {
			Err::fatal ("no value set for column 'id'");
		}

		$id = $values["id"];

		$query = "DELETE FROM " . $table . " WHERE id=?";

		$stmnt = $this->_dbh->prepare($query);

		if ($stmnt->bindValue(1, $id) === FALSE) {
			Err::fatal (sprintf ("unable to bind value '%s' to parameter 1", $id));
		}

		if ($stmnt->execute() === FALSE) {
			Err::fatal ($this->_pdoError());
		}
	}


	/**
	* Perform a general database query and return the results.
	* @param string query to send to database
	* @return mixed results
	*/
	function query ($query) {
		if ( ($results = $this->_dbh->query($query)) === FALSE ) {
			Err::fatal ($this->_pdoError());
		}

		return ($results->fetchAll());
	}


	/**
	* Get the last database query that was executed.
	* @return string
	*/
	function getLastQuery () {
		return ($this->_last_query);
	}


	# Return the last PDO error
	#
	private function _pdoError () {
		$results_error = $this->_dbh->errorInfo();

#Dbg::var_dump("results_error", $results_error);

		return ("database query failed: SQLSTATE[" . $results_error[0] . "] [" . $results_error[1] . "] " . $results_error[2]);
	}


	/**
	* Pagination Count
	*/
#	function totalPages() {
#		if ($this->_query && $this->_limit) {
#			$pattern = '/SELECT (.*?) FROM (.*)LIMIT(.*)/i';
#			$replacement = 'SELECT COUNT(*) FROM $2';
#			$countQuery = preg_replace($pattern, $replacement, $this->_query);
#			$this->_result = mysql_query($countQuery, $this->_dbHandle);
#			$count = mysql_fetch_row($this->_result);
#			$totalPages = ceil($count[0]/$this->_limit);
#			return $totalPages;
#		} else {
#			/* Error Generation Code Here */
#			return -1;
#		}
#	}
}
