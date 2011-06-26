<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*
*/


/**
*
* Manage a database search; setup the search criteria, execute the search, etc.
*
* In this class, all column names are expected to be fully qualified, e.g.
* <model>.<column>
*
* @package MCS_MVC_API
*
*/
class Search {
	private $_criteria = array();


	# Operators that can be used when building a where clause.
	const op_and = "AND";
	const op_or = "OR";
	const op_gt = ">";
	const op_ge = ">=";
	const op_eq = "=";
	const op_le = "<=";
	const op_lt = "<";
	const op_like = "LIKE";
	const op_notlike = "LIKE";
	const op_is = "IS";
	const op_isnot = "IS NOT";


	# Sort orders.
	const ord_asc = "ASC";
	const ord_desc = "DESC";


	/**
	* Create a new Search object.
	*
	* <code>
	* $criteria = new Search("ItemModel");
	* </code>
	*
	* @param string the name of the primary database model used in the search
	* @return Search instance of the object
	*/
	function __construct (BaseModel $model) {
		$this->_criteria["models"] = array();
		array_push($this->_criteria["models"], $model);

		$this->_criteria["select"] = array();

		$this->_criteria["leftjoins"] = array();

		$this->_criteria["where"] = array();

		$this->_criteria["orderby"] = array();

		$this->_criteria["limit"] = 0;

		$this->_criteria["page"] = 1;
	}


	/**
	* Add one or more columns to be selected.
	*
	* @param mixed column names, strings or array
	*/
	function select ($column = NULL) {
		if ( $column === NULL ) {
			$this->_criteria["select"] = array();
			return;
		}

		if ( is_array($column) ) {
			$columns = $column;
		} else {
			$columns = func_get_args();
		}

		$select = $this->_criteria["select"];

		foreach ( $columns as $column ) {
			if ( $this->_check_column($column) ) {
				$select[$column] = $column;
			} else {
				Err::fatal("Unable to add column '$column' to select clause, column not found.");
			}
		}

		$this->_criteria["select"] = $select;

		return;
	}


	/**
	* Add an expression and its column alias to the list of columns to be selected.
	*
	* This method is used when you want to select an expression rather than a specific column.
	*
	* Example...
	* <code>
	* $search->selectExpression ("concat(last, \", \", first)", "lastcommafirst");
	* </code>
	* ...would result in the following being added to the select statement...
	* <code>
	* ...concat(last, ", ", first) as lastcommafirst...
	* </code>
	*
	* @param string expression
	* @param string alias
	*/
	function select_expression ($column, $alias) {
		$select = $this->_criteria["select"];
		$select[$column] = $alias;
		$this->_criteria["select"] = $select;
	}


	/**
	* Add a where clause to the query.
	*
	* <code>
	* $criteria->where (NULL, array (array ("table1.col1", Search::op_eq, "value")));
	*
	* This call is equivalent to the following 2.
	* $criteria->where (NULL, array ( array ("table1.col1", Search::op_eq,
	*      "value1"), Search:op_or, array ("table1.col1", Search::op_eq, "value2") ));
	* $criteria->where (NULL, array (array ("table1.col1", Search::op_eq, "value1")));
	* $criteria->where (Search::op_or, array (array ("table1.col1", Search::op_eq, "value2")));
	* </code>
	*
	* A clause takes the following form...
	* <code>
	* array (array (<col>, <operator>, <val>))
	* e.g. array (array ("model.col", Search::op_eq, "col val"))
	* </code>
	* It is an array of arrays because you can expand the clause to include multiple
	* subclauses.  Like so...
	* <code>
	* array ( array (<col>, <operator>, <val>), <operator>, array (<col>, <operator>, <val>) )
	* e.g. array (array ("model.col", Search::op_eq, "col val"), Search::op_or,
	*     array ("model.col", Search::op_eq, "col val 2"))
	* </code>
	*
	* You can add as many clauses as you want, each containing as many subclauses as you want.
	*
	* If you pass NULL for both arguments, you will remove all where clauses.
	*
	* @param string Search::op_and or Search::op_or
	* @param array clause to add to the query
	*/
	function where ($andor = "", $clause = array()) {
		if ( is_null($andor) AND is_null($clause) ) {
			$this->_criteria["where"] = array();
			return;
		} elseif ( is_null($andor) ) {
			$andor = Search::op_and;
		} elseif ( is_null($clause) ) {
			Err::fatal("clause must be specified");
		} elseif ( ($andor != Search::op_and) AND ($andor != Search::op_or) ) {
			Err::fatal("invalid operator '$andor'");
		} elseif (! is_array($clause)) {
			Err::fatal("clause must be an array");
		}

		$where = $this->_criteria["where"];

		$last_item = "";
		$parsed_clause = array();

		for ($idx = 0; isset($clause[$idx]); $idx++) {
			$item = $clause[$idx];

			# $item is a clause
			if ( is_array($item) ) {
			
				if ( is_array($last_item) ) {
					# Last item was also a clause.  Add the default operator "and".
					array_push ($parsed_clause, Search::op_and);
				}

				if ( count ($item) != 3 ) {
					# Invalid clause found.
					Err::fatal ("invalid clause, less than 3 elements found");
				}

				$col = $item[0];
				$op = $item[1];
				$val = $item[2];

#				if ( ! $this->_check_column($col) ) {
#					Err::fatal("invalid column '$col'");
#				}

				if ( ! $this->_checkOperator($op) ) {
					Err::fatal("invalid operator '$op'");
				}

			# $item is a operator
			} else {

				# check operator
				if ( ($item != Search::op_and) AND ($item != Search::op_or) ) {
					Err::fatal("clause invalid: found invalid operator '$item'");
				}

				# check previous item
				if ( ! is_array($last_item) ) {
					# Last item was not a clause, it was an operator, so this one is extra.
					continue;
				}

				# check next item
				if ( ! isset ($clause[$idx+1]) ) {
					# got an extra operator, just skip it, next item is empty
					continue;
				}
			}

			array_push ($parsed_clause, $item);

			$last_item = $item;
		}

		array_push ($where, array ($andor, $parsed_clause));

#Dbg::var_dump ("where", $where);

		$this->_criteria["where"] = $where;
	}


	/**
	* Add a left join to the query.
	* <code>
	* $search->leftjoin(new CategoryModel($this->db), "category.id", "item.category_id");
	* </code>
	* @param BaseModel database model object
	* @param string column A
	* @param string column B
	*/
	function leftjoin (BaseModel $model, $colA, $colB) {
		array_push ($this->_criteria["models"], $model);

		$joins = $this->_criteria["leftjoins"];

		if ( $this->_check_column($colA) AND $this->_check_column($colB) ) {
			array_push($joins, array($model->table(), $model->name(), $colA, $colB));
		} elseif (! $this->_check_column($colA)) {
			Err::fatal("unable to add left join; column A '$colA' is not valid");
		} elseif (! $this->_check_column($colB)) {
			Err::fatal("unable to add left join; column B '$colB' is not valid");
		}

		$this->_criteria["leftjoins"] = $joins;
	}


	/**
	* Add a column to the list of order-by columns.
	*
	* @param string column name
	* @param string sort order (Search::ord_asc or Search::ord_desc)
	*/
	function orderby ($col, $order = NULL) {
		if ( is_null($col) ) {
			Err::fatal("column name must be specified");
		} elseif (! $this->_check_column($col)) {
			Err::fatal("invalid column '$col'");
		}

		if ( is_null($order) ) {
			$order = Search::ord_asc;
		} elseif ( ($order != Search::ord_asc) AND ($order != Search::ord_desc) ) {
			Err::fatal("invalid sort order '$order'");
		}

		$orderby = $this->_criteria["orderby"];

		array_push($orderby, array ($col, $order));

		$this->_criteria["orderby"] = $orderby;
	}


	/**
	* Set the limit on the number of rows returned by the query.
	*
	* @param integer limit rows per query
	*/
	function limit ($limit) {
		$limit = (int) $limit;

		if ($limit < 0) {
			$limit = 0;
		}

		$this->_criteria["limit"] = $limit;
	}


	/**
	* Set the number of the page within the result set.
	*
	* @param integer page number
	*/
	function page ($page) {
		$page = (int) $page;

		if ($page < 1) {
			$page = 1;
		}

		$this->_criteria["page"] = $page;
	}


	/**
	* Execute the current search.
	*
	* @return mixed results of the search
	*/
	function go () {
		if ( empty($this->_criteria["select"]) ) {
			Err::fatal("you must select at least one column to be returned in the search results");
		}

		global $DB;

		return( $DB->select($this->_criteria) );
	}


	/**
	* Reset the search: list of selected columns, order by, limit, and page #.
	*/
	function reset () {
		$this->_criteria["select"] = array();
		$this->_criteria["orderby"] = array();
		$this->_criteria["limit"] = 0;
		$this->_criteria["page"] = 1;
	}


	private function _check_column ($col) {
		$col_parts = explode(".", $col);
		$col_model = $col_parts[0];
		$col_name = $col_parts[1];

		$models = $this->_criteria["models"];

		foreach ( $models as $model ) {
			if ( $col_model == $model->name() ) {
				if ( in_array($col_name, $model->columns()) ) {
					return (TRUE);
				}
			}
		}

		return (FALSE);
	}


	private function _checkOperator ($op) {
		$op = strtoupper($op);

		return (	($op === Search::op_and)	OR
					($op === Search::op_or)	OR
					($op === Search::op_gt)	OR
					($op === Search::op_ge)	OR
					($op === Search::op_eq)	OR
					($op === Search::op_le)	OR
					($op === Search::op_lt)	OR
					($op === Search::op_like)	OR
					($op === Search::op_notlike)	OR
					($op === Search::op_is)	OR
					($op === Search::op_isnot)
				);
	}


	function __destruct () {}
}
