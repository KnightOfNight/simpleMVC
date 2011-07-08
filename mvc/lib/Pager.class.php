<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.3.0
*/


/**
* Provide easy access to all the different page numbers that might be needed:
* first, last, previous, next, etc.
*
* @package MCS_MVC_API
*/
class Pager {
	private $_page_num;
	private $_page_count;


	/**
	* Create a new Pager object.
	*
	* @param integer current page number
	* @param integer number of results per page
	* @param integer total number of results
	*/
	function __construct ($page_num, $results_per_page, $num_results) {

		$page_count = (int) ($num_results / $results_per_page);
		if ( $num_results % $results_per_page ) {
			$page_count++;
		}

		if ( ($page_num < 1) OR ($page_num > $page_count) ) {
			$page_num = 1;
		}

		$this->_page_num = $page_num;
		$this->_page_count = $page_count;

	}


	/**
	* Return the current page number.
	* @return integer current page number
	*/
	function page_num () {
		return ($this->_page_num);
	}


	/**
	* Return the number of pages.
	* @return integer number of pages
	*/
	function page_count () {
		return ($this->_page_count);
	}


	/**
	* Return the next page, if any.
	* @return integer next page
	*/
	function next_page () {
		$next_page = ($this->_page_num < $this->_page_count) ? ($this->_page_num + 1) : 0;

		return($next_page);
	}


	/**
	* Return the previous page, if any.
	* @return integer previous page
	*/
	function prev_page () {
		$prev_page = ($this->_page_num > 1) ? ($this->_page_num - 1) : 1;

		return($prev_page);
	}


	/**
	* Return the range of page numbers around the current page number.
	*
	* @return array low page number, high page number
	*/
	function range () {

		$offset = 4;

		$low_end = max(1, ($this->_page_num - $offset));

		$offset = 4;

		$high_end = min($this->_page_count, ($this->_page_num + $offset));

		$range = array();
		for ( $i = $low_end; $i <= $high_end; $i++ ) {
			array_push($range, $i);
		}
		
		return($range);
	}


	function __destruct () {}
}
