<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
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
	# Private variables.

	# Array of database model objects.
	private $_models = array();

	# Columns to be selected.
	private $_select_cols = array();

	# Left joins.
	private $_left_joins = array();

	# Order-by.
	private $_order_by = array();

	# Where clauses.
	private $_where = array();

	# Limit on the results returned.
	private $_limit = 0;

	# Page within the results returned.
	private $_page = 0;


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
	* $criteria = new Search(new DatabaseModel($this->db));
	* </code>
	*
	* @param BaseModel an instance of a BaseModel object
	* @return Search instance of the object
	*/
	function __construct (BaseModel $model) {
		if (! ($model instanceof BaseModel)) {
			Err::fatal ("passed model is not an instance of BaseModel");
		}

		array_push ($this->_models, $model);
	}


	/**
	* Add a left join to the query.
	* <code>
	* $criteria->addLeftJoin(new CategoryModel($this->db), "category.id", "item.category_id");
	* </code>
	* @param BaseModel database model object
	* @param string column A
	* @param string column B
	*/
	function addLeftJoin (BaseModel $model, $colA, $colB) {
		if (! ($model instanceof BaseModel)) {
			Err::fatal ("passed model is not an instance of BaseModel");
		}

		array_push ($this->_models, $model);

		if ($this->_checkColumn ($colA) AND $this->_checkColumn ($colB)) {
			array_push ($this->_left_joins, array ($model->table(), $model->name(), $colA, $colB));
		} else {
			Err::fatal (sprintf ("either column '%s' or column '%s' is not valid", $colA, $colB));
		}
	}


	/**
	* Add a column to the list of order-by columns.
	*
	* @param string column name
	* @param string sort order (Search::ord_asc or Search::ord_desc)
	*/
	function addOrderBy ($col = NULL , $order = NULL) {
		if (is_null ($col)) {
			Err::fatal ("column name must be specified");
		} elseif (! $this->_checkColumn ($col)) {
			Err::fatal (sprintf ("invalid column '%s'", $col));
		}

		if (is_null ($order)) {
			$order = Search::ord_asc;
		} elseif ( ($order != Search::ord_asc) AND ($order != Search::ord_desc) ) {
			Err::fatal (sprintf ("invalid sort order '%s'", $order));
		}

		array_push ($this->_order_by, array ($col, $order));
	}


	/**
	* Add one or more columns to be selected.
	*
	* @param string one or more names of columns to be selected
	* @return hash list of columns to be selected, and their respective aliases where present
	*/
	function select ($column = NULL) {
		$columns = func_get_args();

		if ( empty ($columns) ) {
			return($this->_select);

		} elseif ( $columns[0] === NULL ) {
			$this->_select = array();

		} else {
			foreach ( $columns as $column ) {
				if ($this->_checkColumn ($column)) {
					$this->_select{$column} = "";
				} else {
					Err::fatal (sprintf ("unable to add column to select query - invalid column '%s'", $col));
				}
			}
		}

		return($this->_select);
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
	* ...concat(last, ", ", first as lastcommafirst...
	* </code>
	*
	* @param string expression to select.
	* @param string column alias
	*/
	function selectExpression ($column, $alias) {
		$this->_select_cols{$column} = $alias;
	}


	/**
	* Add a where clause to the query.
	*
	* <code>
	* $criteria->addWhere (NULL, array (array ("table1.col1", Search::op_eq, "value")));
	*
	* This call is equivalent to the following 2.
	* $criteria->addWhere (NULL, array ( array ("table1.col1", Search::op_eq,
	*      "value1"), Search:op_or, array ("table1.col1", Search::op_eq, "value2") ));
	* $criteria->addWhere (NULL, array (array ("table1.col1", Search::op_eq, "value1")));
	* $criteria->addWhere (Search::op_or, array (array ("table1.col1", Search::op_eq, "value2")));
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
	* You can add as many clauses as you want, containing as many subclauses as you want.
	*
	* @param string Search::op_and or Search::op_or
	* @param array clause to add to the query
	*/
	function addWhere ($andor, $clause = NULL) {
		global $L;

		if (is_null ($andor)) {
			$andor = Search::op_and;
		}

		if (is_null ($clause)) {
			Err::fatal ("clause must be specified");
		} elseif (! is_array ($clause)) {
			Err::fatal ("claus must be an array");
		}

		$last_item = "";
		$parsed_clause = array();

		for ($idx = 0; isset ($clause[$idx]); $idx++) {
			$item = $clause[$idx];

			$L->msg(Log::DEBUG, "item = '" . $item . "'");

			if (is_array ($item)) {
				# $item is a clause
			
				if (is_array ($last_item)) {
					# Got a clause immediately after another clause, add the default operator "and".
					array_push ($parsed_clause, Search::op_and);
				}

				if (count ($item) != 3) {
					# Invalid clause found.
					Err::fatal ("invalid clause, less than 3 elements found");
				}

				$col = $item[0];
				$op = $item[1];
				$val = $item[2];

				if (! $this->_checkColumn ($col)) {
					Err::fatal (sprintf ("invalid column '%s'", $col));
				}

				if (! $this->_checkOperator ($op)) {
					Err::fatal (sprintf ("invalid operator '%s'", $op));
				}
			} else {
				# $item is a operator

				# check previous item
				if (! is_array ($last_item)) {
					# got an extra operator, just skip it (no preceeding clause).
					continue;
				}

				# check next item
				if (! isset ($clause[$idx+1])) {
					# got an extra operator, just skip it (next item is empty)
					continue;
				} elseif (! is_array ($clause[$idx + 1])) {
					# got an extra operator, just skip it (next item is not a clause)
					continue;
				}

				# check operator
				if ( ($item != Search::op_and) AND ($item != Search::op_or) ) {
					Err::fatal (sprintf ("clause invalid: found invalid operator '%s'", $item));
				}
			}

			array_push ($parsed_clause, $item);

			$last_item = $item;
		}

		array_push ($this->_where, array ($andor, $parsed_clause));
#Dbg::var_dump ("_where", $this->_where);
	}


	/**
	* Get list of all left joins.
	* @return mixed ( (<table name>, <model name>, <col. A>, <col. B>) , ... )
	*/
	function getLeftJoins () {
		return ($this->_left_joins);
	}


	/**
	* Get the limit on the number of rows returned by the query.
	* @return integer row limit
	*/
	function getLimit () {
		return ($this->_limit);
	}


	/**
	* Get the order-by info.
	*
	* The returned value takes the following form...
	* <code>
	* array (
	*	array (<col>, <ord>)
	* )
	* </code>
	* ...where <ord> is Search::ord_asc or Search::ord_desc.
	*
	* @return array ( (<col>, <ord>) , ... ) 
	*/
	function getOrderBy () {
		return ($this->_order_by);
	}


	/**
	* Get the number of the page within the result set.
	*
	* @return integer page number
	*/
	function getPage () {
		return ($this->_page);
	}


	/**
	* Get the current list of columns and/or expressions to be selected and any
	* corresponding aliases.
	*
	* @return hash { <column name> => <alias (if any)> }
	*/
	function getSelect () {
		return ($this->_select_cols);
	}


	/**
	* Get the first model associated with this criteria, AKA the primary table
	* in the query.
	*
	* @return BaseModel
	*/
	function getModel () {
		return ($this->_models[0]);
	}


	/**
	* Get all of the where clauses for the query.
	*
	* The returned value takes the following form...
	* <code>
	* array (
	*	array (<op>, array (<clauses>))
	* )
	* </code>
	* ...where each of <clauses> is either an array (<col>,<op>,<val>) or string <op>.
	* @return array ( ( <op>, ( <clause> , ... ) ) , ...)
	*/
	function getWhere () {
		return ($this->_where);
	}


	/**
	* Set the limit on the number of rows returned by the query.
	*
	* @param integer limit rows per query
	*/
	function setLimit ($limit = NULL) {
		$limit = (int) $limit;

		if ($limit < 0) {
			$limit = 0;
		}

		$this->_limit = $limit;
	}


	/**
	* Set the list of columns and sort order by which the results should be
	* ordered.
	*
	* @param array list of fully qualified column names (<table>.<column>)
	* @param string Search::ord_asc or Search::ord_desc
	*/
#	function setOrderBy (array $cols, string $order = NULL) {
#		if (is_null ($cols)) {
#			Err::fatal ("list of columns must be specified");
#		} elseif (! is_array ($cols)) {
#			Err::fatal ("list of columns must be an array");
#		}
#
#		if (is_null ($order)) {
#			$order = Search::ord_asc;
#		} elseif ( ($order != Search::ord_asc) AND ($order != Search::ord_desc) ) {
#			Err::fatal (sprintf ("invalid sort order '%s'", $order));
#		}
#
#		foreach ($cols as $col) {
#			if (! $this->_checkColumn ($col)) {
#				Err::fatal (sprintf ("invalid column '%s'", $col));
#			}
#		}
#
#		$this->_order_by = $cols;
#		$this->_order = $order;
#	}


	/**
	* Set the number of the page within the result set.
	* @param integer page number
	*/
	function setPage ($page = NULL) {
		if ($page < 1) {
			$page = 1;
		}

		$this->_page = $page;
	}


	/**
	* Clear the sort order.
	*/
	function clearOrderBy () {
		$this->_order_by = "";
		$this->_order = "";
	}


	/**
	* Clear the list of selected columns and expressions.
	*/
	function clearSelect () {
		$this->_select_cols = array();
	}


	/**
	* Clear the list of where clauses.
	*/
	function clearWhere () {
		$this->_where = array();
	}


	private function _checkColumn ($col) {
		$col_parts = explode (".", $col);

		$model_name = $col_parts[0];
		$col_name = $col_parts[1];

		foreach ($this->_models as $model) {
			if ($model_name == $model->name()) {
				if (in_array ($col_name, $model->columns())) {
					return (TRUE);
				}
			}
		}

		return (FALSE);
	}


	private function _checkOperator ($op) {
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
