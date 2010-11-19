<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.2.0
*
*/


/**
*
* Manage all of the criteria for a database select: which columns get selected,
* what sort order to use, where clause, etc.
* In this class, all column names are expected to be fully qualified, e.g. <model>.<column>
*
* @package MCS_MVC_API
*
*/
class Criteria {
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
	* Create a new Criteria object.
	*
	* <code>
	* $criteria = new Criteria(new DatabaseModel($this->db));
	* </code>
	*
	* @param BaseModel an instance of a BaseModel object
	* @return Criteria instance of the object
	*/
	function __construct (BaseModel $model) {
		if (! ($model instanceof BaseModel)) {
			Err::fatal ("passed model is not an instance of BaseModel");
		}

		array_push ($this->_models, $model);
	}


	/**
	* Select columns to be returned in the query.
	* @param array list of fully qualified column names (<table>.<column>)
	* <code>
	* $criteria->selectCols (array ("table1.col1", "table1.col2"));
	* </code>
	*/
#	function selectCols ($cols = NULL) {
#		if (is_null ($cols)) {
#			Err::fatal ("list of columns must be specified");
#		} elseif (! is_array ($cols)) {
#			Err::fatal ("list of columns must be an array of column names");
#		}
#
#		foreach ($cols as $col) {
#			if ($this->_checkColumn ($col)) {
#				array_push ($this->_select_cols, $col);
#			} else {
#				Err::fatal (sprintf ("invalid column '%s'", $col));
#			}
#		}
#	}


	/**
	* Select an expression to be returned in the query.
	* <code>
	* $criteria->selectExpression ("concat(col1, \" \" , col2)", "col1_and_col2");
	* </code>
	* @param string MySQL expression
	* @param string column alias for the expression result
	*/
#	function selectExpression ($expression, $alias) {
#		if (is_null ($expression) AND is_null ($alias)) {
#			Err::fatal ("expression and alias must both be specified");
#		}
#
#		$this->_select_cols[$expression] = $alias;
#	}


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
	* @param string sort order (Criteria::ord_asc or Criteria::ord_desc)
	*/
	function addOrderBy ($col = NULL , $order = NULL) {
		if (is_null ($col)) {
			Err::fatal ("column name must be specified");
		} elseif (! $this->_checkColumn ($col)) {
			Err::fatal (sprintf ("invalid column '%s'", $col));
		}

		if (is_null ($order)) {
			$order = Criteria::ord_asc;
		} elseif ( ($order != Criteria::ord_asc) AND ($order != Criteria::ord_desc) ) {
			Err::fatal (sprintf ("invalid sort order '%s'", $order));
		}

		array_push ($this->_order_by, array ($col, $order));
	}


	/**
	* Add one or more columns to be selected.
	*
	* @param mixed a single column name or an array of column names
	* @param string column alias (if single column name specified)
	*/
	function addSelect ($cols = null, $alias = NULL) {
		if (is_null ($cols)) {
			Err::fatal ("column name(s) must be specified");
		}

		if (is_array ($cols)) { 
			foreach ($cols as $col) {
				if ($this->_checkColumn ($col)) {
					$this->_select_cols{$col} = "";
				} else {
					Err::fatal (sprintf ("invalid column '%s'", $col));
				}
			}
		} else {
			$this->_select_cols{$cols} = $alias;
		}
	}


	/**
	* Add a where clause to the query.
	*
	* <code>
	* $criteria->addWhere (NULL, array (array ("table1.col1", Criteria::op_eq, "value")));
	*
	* This call is equivalent to the following 2.
	* $criteria->addWhere (NULL, array ( array ("table1.col1", Criteria::op_eq,
	*      "value1"), Criteria:op_or, array ("table1.col1", Criteria::op_eq, "value2") ));
	* $criteria->addWhere (NULL, array (array ("table1.col1", Criteria::op_eq, "value1")));
	* $criteria->addWhere (Criteria::op_or, array (array ("table1.col1", Criteria::op_eq, "value2")));
	* </code>
	*
	* A clause takes the following form...
	* <code>
	* array (array (<col>, <operator>, <val>))
	* e.g. array (array ("model.col", Criteria::op_eq, "col val"))
	* </code>
	* It is an array of arrays because you can expand the clause to include multiple
	* subclauses.  Like so...
	* <code>
	* array ( array (<col>, <operator>, <val>), <operator>, array (<col>, <operator>, <val>) )
	* e.g. array (array ("model.col", Criteria::op_eq, "col val"), Criteria::op_or,
	*     array ("model.col", Criteria::op_eq, "col val 2"))
	* </code>
	*
	* You can add as many clauses as you want, containing as many subclauses as you want.
	*
	* @param string Criteria::op_and or Criteria::op_or
	* @param array clause to add to the query
	*/
	function addWhere ($andor, $clause = NULL) {
		global $L;

		if (is_null ($andor)) {
			$andor = Criteria::op_and;
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
					array_push ($parsed_clause, Criteria::op_and);
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
				if ( ($item != Criteria::op_and) AND ($item != Criteria::op_or) ) {
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


#	/**
#	* Get the sort order: ascending or descending.
#	* @return string Criteria::ord_asc or Criteria::ord_desc
#	*/
#	function getOrder () {
#		return ($this->_order);
#	}


	/**
	* Get the order-by info.
	*
	* The returned value takes the following form...
	* <code>
	* array (
	*	array (<col>, <ord>)
	* )
	* </code>
	* ...where <ord> is Criteria::ord_asc or Criteria::ord_desc.
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
	* @param string Criteria::ord_asc or Criteria::ord_desc
	*/
#	function setOrderBy (array $cols, string $order = NULL) {
#		if (is_null ($cols)) {
#			Err::fatal ("list of columns must be specified");
#		} elseif (! is_array ($cols)) {
#			Err::fatal ("list of columns must be an array");
#		}
#
#		if (is_null ($order)) {
#			$order = Criteria::ord_asc;
#		} elseif ( ($order != Criteria::ord_asc) AND ($order != Criteria::ord_desc) ) {
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
		return (	($op === Criteria::op_and)	OR
					($op === Criteria::op_or)	OR
					($op === Criteria::op_gt)	OR
					($op === Criteria::op_ge)	OR
					($op === Criteria::op_eq)	OR
					($op === Criteria::op_le)	OR
					($op === Criteria::op_lt)	OR
					($op === Criteria::op_like)	OR
					($op === Criteria::op_notlike)	OR
					($op === Criteria::op_is)	OR
					($op === Criteria::op_isnot)
				);
	}


	function __destruct () {}
}
