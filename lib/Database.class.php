<?php


/**
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version v0.0.0
*/


/**
* Manage a database connection.
* @package MVCAPI
*/
class Database {
	private $_dbh;
	private $_last_query;


	/**
	* Connect to a database.
	* @param string host
	* @param string port number
	* @param string database name
	* @param string username
	* @param string password
	*/
	function connect ($host, $port, $name, $user, $pass) {
		$dsn = sprintf ("%s:host=%s;port=%s;dbname=%s", "mysql", $host, $port, $name);

		try {
			$this->_dbh = new PDO ($dsn, $user, $pass);
		} catch (PDOException $e) {
			Error::fatal ($e->getMessage());
		}
	}


	/**
	* Describe a table and return the list of column names.
	* @param string table name
	* @return array list of column names
	*/
	function describe ($table = NULL) {
		if (is_null ($table)) {
			Error::fatal ("no table name specified");
		}

		if (! $columns = Cache::get($cache_name = "describe_" . $table)) {
			$columns = array();

			$query = "describe " . $table;

			if ( ($results = $this->_dbh->query($query)) === FALSE ) {
				Error::fatal ($this->_pdoError());
			}

			while ($result = $results->fetch()) {
				array_push ($columns, $result["Field"]);
			}

			Cache::set ($cache_name, $columns);
		}

		return ($columns);
	}


	/**
	* Perform a database select, building the query from the passed criteria.
	* @param Criteria
	* @return mixed query results
	*/
	function select (Criteria $criteria) {
		global $LOG;

		$query = "SELECT ";

		# columns and expressions list
		$columns = $criteria->getSelect();

		$column_list = "";
		foreach ($columns as $col => $alias) {
			if ($column_list) {
				$column_list .= " , ";
			}

			$column_list .= $col;

			if ($alias) {
				$column_list .= " AS " . $alias;
			} else {
				$column_list .= " AS " . preg_replace('/\./', "_", $col);
			}
		}
		$query .= $column_list;


		# FROM
		$model = $criteria->getModel();
		$table = $model->getTable();
		$query .= " FROM " . $table . " AS " . $model->getName();


		# LEFT JOIN
		$joins = $criteria->getLeftJoins();
		foreach ($joins as $join) {
			$table = $join[0];
			$model = $join[1];
			$colA = $join[2];
			$colB = $join[3];
			$query .= " LEFT JOIN " . $table . " AS " . $model . " ON " . $colA . "=" . $colB;
		}


		# WHERE
		$wheres = $criteria->getWhere();
		$where_str = "";
		$values = array();
		foreach ($wheres as $where) {
			$operator = $where[0];
			$clauses = $where[1];

			$LOG->msg(Log::DEBUG, "operator = '" . $operator . "'");
			$LOG->msg(Log::DEBUG, "clauses = '" . $clauses . "'");

			if ($where_str) {
				$where_str .= " " . $operator;
			} else {
				$where_str = " WHERE";
			}

			$clause_str = "";
			$LOG->msg(Log::DEBUG, "clause_str = '" . $clause_str . "'");

			foreach ($clauses as $clause) {
				$LOG->msg(Log::DEBUG, "found clause");

				if (! $clause_str) {
					$clause_str = " (";
				}

				if (is_array ($clause)) {
					$LOG->msg(Log::DEBUG, "clause is an array");

					$col = $clause[0];
					$op = $clause[1];
					$val = $clause[2];

					$LOG->msg(Log::DEBUG, "col = '" . $col . "'");
					$LOG->msg(Log::DEBUG, "op = '" . $op . "'");
					$LOG->msg(Log::DEBUG, "val = '" . $val . "'");

					$clause_str .= $col . " " . $op . " ?";

					array_push ($values, $val);
				} else {
					$LOG->msg(Log::DEBUG, "clause is an operator");

					$clause_str .= " " . $clause . " ";
				}
			}
			$clause_str .= ")";

			$where_str .= $clause_str;
		}

		if ($where_str) {
			$query .= $where_str;
		}


#			$clause_operator = $clause["operator"];
#			$sub_clauses = $clause["sub_clauses"];
#
#			if ($where_str) {
#				$where_str .= " " . $clause_operator;
#			} else {
#				$where_str = " WHERE";
#			}

#			$sub_clause_str = "";
#			foreach ($sub_clauses as $sub_clause) {
#				$sub_clause_operator = $sub_clause["operator"];
#				$sub_clause_parts = $sub_clause["sub_clause"];
#
#				if ($sub_clause_str) {
#					$sub_clause_str .= " " . $sub_clause_operator . " ";
#				} else {
#					$sub_clause_str = " (";
#				}
#
#				$sub_clause_str .= $sub_clause_parts["column"] . " " . $sub_clause_parts["operator"] . " ?";
#
#				array_push ($values, $sub_clause_parts["value"]);
#			}
#			$sub_clause_str .= ")";


		# ORDER BY
		$orderby_str = "";
		if ($orderbys = $criteria->getOrderBy()) {
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

#			$orderby_cols = $orderby[0];
#			$orderby_order = $orderby[1];
#			$query .= " ORDER BY " . $orderby_cols . " " . $orderby_order;
		}
#		if ($orderby = $criteria->getOrderBy()) {
#			$order = $criteria->getOrder();
#			$query .= " ORDER BY " . $orderby . " " . $order;
#		}


		# LIMIT
		$limit = $criteria->getLimit();
		$page = $criteria->getPage();
		if ($limit) {
			$query .= " LIMIT " . $limit;

			if ($page) {
				$offset = ($page - 1) * $limit;
				$query .= " OFFSET " . $offset;
			}
		}

		$this->_last_query = $query;

#		my_var_dump ("query", $query);

#		return (NULL);

		$stmnt = $this->_dbh->prepare($query);

		$index = 0;
		foreach ($values as $val) {
			if ($stmnt->bindValue($index + 1, $val) === FALSE) {
				Error::fatal (sprintf ("unable to bind value '%s' to parameter %d", $val, $index + 1));
			}
			$index++;
		}

		$LOG->msg(Log::INFO, "database query = '" . $query . "'");

		if ($stmnt->execute() === FALSE) {
			Error::fatal ($this->_pdoError());
		}

		return ($stmnt->fetchAll(PDO::FETCH_BOTH));
	}


	# Save or update a database record for the passed model.
	#
	function save ($model) {
		if (! ($model instanceof BaseModel)) {
			Error::fatal ("passed model is not an instance of BaseModel");
		}

		$table = $model->getTable();
		$values = $model->getColVals();

		if (isset ($values["id"])) {
			$query = "UPDATE " . $table . " SET ";
			$set_str = "";
		} else {
			$query = "INSERT INTO " . $table . " SET ";
			$set_str = "created_at=NULL";
		}

		foreach ($values as $col => $val) {
			if ($set_str) {
				$set_str .= " , ";
			}
			$set_str .= $col . "=:" . $col;
		}

		$query .= $set_str;

		if (isset ($values["id"])) {
			$query .= " WHERE id=:id";
		}

		$stmnt = $this->_dbh->prepare($query);

		foreach ($values as $col => $val) {
			if ($stmnt->bindValue(":" . $col, $val) === FALSE) {
				Error::fatal (sprintf ("unable to bind value '%s' to ':%s'", $val, $col));
			}
		}

		if ($stmnt->execute() === FALSE) {
			Error::fatal ($this->_pdoError());
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
			Error::fatal ("passed model is not an instance of BaseModel");
		}

		$table = $model->getTable();
		$values = $model->getColVals();

		if (! isset ($values["id"])) {
			Error::fatal ("no value set for column 'id'");
		}

		$id = $values["id"];

		$query = "DELETE FROM " . $table . " WHERE id=?";

		$stmnt = $this->_dbh->prepare($query);

		if ($stmnt->bindValue(1, $id) === FALSE) {
			Error::fatal (sprintf ("unable to bind value '%s' to parameter 1", $id));
		}

		if ($stmnt->execute() === FALSE) {
			Error::fatal ($this->_pdoError());
		}
	}


	/**
	* Perform a general database query and return the results.
	* @param string query to send to database
	* @return mixed results
	*/
	function query ($query) {
		if ( ($results = $this->_dbh->query($query)) === FALSE ) {
			Error::fatal ($this->_pdoError());
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
