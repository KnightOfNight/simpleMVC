<?php

class Pager {
	private $_page_num;
	private $_page_count;
	private $_row_count;


	function __construct (Criteria $passed_criteria) {
		global $DB;

		$criteria = clone $passed_criteria;

		$passed_page = $passed_criteria->getPage();
		$passed_limit = $passed_criteria->getLimit();

		# Reset some of the criteria so that the lookup checks the entire
		# result set.
		$criteria->clearSelect();
		$criteria->setLimit(0);
		$criteria->setPage(0);
		$criteria->clearOrderBy();

		# Set the column to be selected to count(<primary key>).
		$model = $criteria->getModel()->getName();
		$column = $model . ".id";
		$criteria->addSelect("count(" . $column . ")", "row_count");

		# Perform the database lookup.
		$results = $DB->select($criteria);

		# Get the number of rows and the number of pages.
		$row_count = $results[0]["row_count"];
		$page_count = (int) ($row_count / $passed_limit) + 1;

		# Check the page number that was originally passed in and reset it if
		# necessary (if out of bounds).
		if ($passed_page < 1) {
			$page = 1;
		} elseif ($passed_page > $page_count) {
			$page = $page_count;
		} else {
			$page = $passed_page;
		}

		$this->_page_num = $page;
		$this->_page_count = $page_count;
		$this->_row_count = $row_count;
	}


	function page () {
		return ($this->_page_num);
	}


	function pageCount () {
		return ($this->_page_count);
	}


	function rowCount () {
		return ($this->_row_count);
	}


	function __destruct () {}
}
