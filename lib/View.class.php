<?php


/**
*
* @author >X @ MCS 'Net Productions
* @package MVCAPI
* @version 0.1.0
*
*/


/**
*
* Manage setup and display of a view for a particular controller/action/query.
*
* @package MVCAPI
*
*/
class View {
	# Private variables.

	# Name of the controller, action, and query for this view.
	private $_controller;
	private $_action;
	private $_query;

	# TRUE => render header and footer
	private $_render_hf;

	# Variables available to the view.
	private $_variables = array();

	# The names and values of slots, and the lists of CSS and JS files.
	private $_slots = array();
	private $_css_files = array();
	private $_js_files = array();
	
	private $_pager;


	/**
	* Create a new View object.
	*
	* Often called like this...
	* <code>
	* $this->view = new View($this->controller, __FUNCTION__, $query);
	* </code>
	* ...from within an action method in a Controller object.
	* @param string name of the controller
	* @param string name of the action
	* @param string passed query
	*/
	function __construct ($controller = NULL, $action = NULL, $query = NULL) {
		if (is_null ($controller) OR is_null ($action) OR is_null ($query)) {
			Error::fatal ("controller, action, and query must all be specified");
		}

		$this->_variables["CONTROLLER"] = $this->_controller = $controller;
		$this->_variables["ACTION"] = $this->_action = $action;
		$this->_variables["QUERY"] = $this->_query = $query;

		$this->_render_hf = TRUE;
	}


	/**
	* Set a flag that indicates whether or not the header and footer should be
	* rendered.  Useful in actions that only render part of a page or that only
	* return data to another action.
	*
	* @param bool TRUE => render header and footer
	*/
	function renderHF ($value = TRUE) {
		if (! is_bool ($value)) {
			Error::fatal ("passed value must be a boolean (TRUE or FALSE)");
		}

		$this->_render_hf = $value;
	}


	/**
	* Set a variable to a particular value.  The variable will be available in
	* the view.
	*
	* From inside an action method in a Controller object...
	* <code>
	* $this->view->setVariable("somevar", "some string");
	* </code>
	* ...and when writing the action's view, you will have access to a variable
	* called $somevar set to the value "some string".
	*
	* Variable names must be unique, and can store any type of data.
	*
	* @param string name of the variable
	* @param mixed value of the variable
	*/
	function setVariable ($name = NULL, $value = NULL) {
		if (is_null ($name) OR is_null ($value)) {
			Error::fatal ("variable name and value must both be specified");
		}

		$this->_variables[$name] = $value;
	}


	function pager ($pager = NULL) {
		if (is_null ($pager)) {
			if ($this->_pager->pageCount() > 1) {
				return ($this->_pager);
			} else {
				return (NULL);
			}
		} else {
			$this->_pager = $pager;
		}
	}


	/**
	* Add a CSS file to the list of files to be included in the page.
	*
	* Can only be called from the view for a particular action.
	*
	* @param string CSS file
	*/
	function useCSS ($css_file) {
		if (get_class($this) != "View") {
			Error::fatal ("method must be called from a View object");
		}

		array_push ($this->_css_files, $css_file);
	}


	/**
	* Display HTML code to include all CSS files for a page.
	*
	* Can only be called from the view for a particular action.
	*
	* As this outputs HTML that is only valid in place on a page, it is highly
	* recommended that you call this from within a page's <head> section.
	* Otherwise you risk invalid HTML.
	*/
	function showCSS () {
		global $CONFIG;

		if (get_class($this) != "View") {
			Error::fatal ("method must be called from a View object");
		}

		foreach ($this->_css_files as $file) {
        	printf ("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"%s/css/%s\" />\n", $CONFIG->getVal("application.base_url"), $file);
		}
	}


	/**
	* Add a JS file to the list of files to be included in the page.
	*
	* Can only be called from the view for a particular action.
	*
	* @param string JS file
	*/
	function useJS ($js_file) {
		if (get_class($this) != "View") {
			Error::fatal ("method must be called from a View object");
		}

		array_push ($this->_js_files, $js_file);
	}


	/**
	* Display HTML code to include all JS files for a page.
	*
	* Can only be called from the view for a particular action.
	*
	* As this outputs HTML that is only valid in place on a page, it is highly
	* recommended that you call this from a page header, preferably in the
	* <head> section.  Otherwise you risk invalid HTML.
	*/
	function showJS () {
		global $CONFIG;

		if (get_class($this) != "View") {
			Error::fatal ("method must be called from a View object");
		}

		foreach ($this->_js_files as $file) {
			printf ("<script type=\"text/javascript\" src=\"%s/js/%s\"></script>\n", $CONFIG->getVal("application.base_url"), $file);
		}
	}


	/**
	* Create a slot in a page that can be filled in with text, HTML, etc.
	*
	* Can only be called from the view for a particular action.
	*
	* Often used to create a slot in a shared page header that can be filled in
	* with the page title by a particular action.
	*
	* @param string name of the slot
	*/
	function makeSlot ($slot_name) {
		if (get_class($this) != "View") {
			Error::fatal ("method must be called from a View object");
		}

		if (isset ($this->_slots[$slot_name])) {
			echo $this->_slots[$slot_name];
		}
	}


	/**
	* Fill a slot with its contents.
	*
	* Can only be called from the view for a particular action.
	*
	* Often used to set the title of a page.  See slot() above.
	*
	* @param string name of the slot
	* @param string value of the slot
	*/
	function fillSlot ($slot_name, $slot_value) {
		if (get_class($this) != "View") {
			Error::fatal ("method must be called from a View object");
		}

		$this->_slots[$slot_name] = $slot_value;
	}


	/**
	* Display HTML code to include all CSS files for a page.
	*
	* Can only be called from the view for a particular action.
	*
	* As this outputs HTML that is only valid in place on a page, it is highly
	* recommended that you call this from a page header, preferably in the
	* <head> section.  Otherwise you risk invalid HTML.
	*/
#	function includeCSS () {
#		global $CONFIG;
#
#		foreach ($this->_css_files as $file) {
#        	printf ("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"%s/css/%s\" />\n", $CONFIG->getVal("application.base_url"), $file);
#		}
#	}


	/**
	* Render the view.  Usually only called from the __destruct() function in a
	* Controller object.
	*/
    function render () {
		extract ($this->_variables);

		if (! File::ready ($body = VIEWDIR.DS.$this->_controller.DS.$this->_action.".php")) {
			Error::fatal (sprintf ("unable to read page body '%s'", $body));
		}

		ob_start ();
		include ($body);
		$body_contents = ob_get_clean ();

		if ($this->_render_hf) {
			if (File::ready ($header = VIEWDIR.DS.$this->_controller.DS."header.php") ||
					File::ready ($header = VIEWDIR.DS."header.php")) {
				include ($header);
			} else {
				Error::fatal (sprintf ("unable to load any page header"));
			}
		}

		echo $body_contents;

		if ($this->_render_hf) {
			if (File::ready ($footer = VIEWDIR.DS.$this->_controller.DS."footer.php") ||
					File::ready ($footer = VIEWDIR.DS."footer.php")) {
				include ($footer);
			} else {
				Error::fatal (sprintf ("unable to load any page footer"));
			}
		}
    }


	function __destruct () {}
}
