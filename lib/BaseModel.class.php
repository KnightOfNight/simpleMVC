<?php


/**
* @author >X @ MCS 'Net Productions
* @package MCS_MVC_API
* @version 0.1.0
*/


/**
* Base Model class - handle the lowest level functions of a database model
* object.
*
* @package MCS_MVC_API
*/
class BaseModel {
	/**
	* @var string name of the instantiated model
	*/
	public $name;

	/**
	* @var string name of the model's associated table
	*/
	public $table;

	/**
	* @var array list of all columns in the table
	*/
	public $columns;

	/**
	* @var hash all columns and their associated values
	*/
	public $values;


	/**
	* Create a new BaseModel object.
	* <code>
	* class ItemModel extends BaseModel {
	* }
	* </code>
	*
	* @param Database an instance of a Database object
	* @return BaseModel a new BaseModel object
	*/
	function __construct () {
		global $DB;

		$this->name = strtolower (str_replace ("Model", "", get_class ($this)));
		$this->table = Inflection::pluralize ($this->name);
		$this->columns = $DB->describe ($this->table);
	}


	function __destruct () {}
}
