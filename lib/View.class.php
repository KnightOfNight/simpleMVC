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
* Manage setup and display of a view for a particular route.
*
* @package MCS_MVC_API
*
*/
class View {
	/**
	* @var mixed all configuration information for the view
	*/
	private $_config;

	/**
	* @var array list of reserved variables that the View automatically
	* extracts and makes available to views.
	*/
	private $_reserved_vars = array("CONTROLLER", "ACTION", "QUERY");

	private $_controller;
	private $_action;
	private $_query;

	private $_css_files = array();
	private $_js_files = array();
	
	private $_pager;


	/**
	* Create a new View object.
	*
	* Often called from an action method like this...
	* <code>
	* $view = new View($this->name(), __FUNCTION__, $query);
	* </code>
	* ...from within an action method in a Controller object.
	*
	* @param string controller name
	* @param string action
	* @param string query string
	*/
	function __construct ($controller, $action, $query) {
		$this->_config["render_header"] = FALSE;
		$this->_config["render_footer"] = FALSE;
		$this->_config["variables"] = array();
		$this->_config["slots"] = array();
		$this->_config["css_files"] = array();
		$this->_config["js_files"] = array();

		$this->_controller = $this->_config["variables"]["CONTROLLER"] = $controller;
		$this->_action = $this->_config["variables"]["ACTION"] = $action;
		$this->_query = $this->_config["variables"]["QUERY"] = $query;
	}


	/**
	* Set a flag that indicates whether or not the header and footer should be rendered.
	*
	* @param bool TRUE => render header and footer
	*/
	function render_headfoot ($value = TRUE) {
		if (! is_bool($value)) {
			Err::fatal("passed value must be a boolean");
		}

		$this->_config["render_header" ] = $value;
		$this->_config["render_footer" ] = $value;
	}


	/**
	* Set a flag that indicates whether or not the header should be rendered.
	*
	* @param bool TRUE => render header
	*/
	function render_header ($value = TRUE) {
		if (! is_bool($value)) {
			Err::fatal("passed value must be a boolean");
		}

		$this->_config["render_header" ] = $value;
	}


	/**
	* Set a flag that indicates whether or not the footer should be rendered.
	*
	* @param bool TRUE => render footer
	*/
	function render_footer ($value = TRUE) {
		if (! is_bool($value)) {
			Err::fatal("passed value must be a boolean");
		}

		$this->_config["render_footer" ] = $value;
	}


	/**
	* Set a variable to a particular value.  The variable will be available in
	* the view.
	*
	* From inside an action method in a Controller object...
	* <code>
	* $view->variable("somevar", "some string");
	* </code>
	* ...and when writing the action's view, you will have access to a variable
	* called $somevar set to the value "some string".
	*
	* Variable names must be unique, and can store any type of data.
	*
	* @param string name of the variable
	* @param mixed value of the variable
	*/
	function variable ($name, $value) {
		if ( in_array($name, $this->_reserved_vars) ) {
			Err::fatal("variable name cannot be a reserved name");
		}

		$this->_config["variables"][$name] = $value;
	}


#	function pager ($pager = NULL) {
#		if (is_null ($pager)) {
#			if ($this->_pager->pageCount() > 1) {
#				return ($this->_pager);
#			} else {
#				return (NULL);
#			}
#		} else {
#			$this->_pager = $pager;
#		}
#	}


	/**
	* Add a CSS file to the list of files to be included in the page.
	*
	* Can only be called from the view for a particular action.
	*
	* @param string CSS file
	*/
	protected function useCSS ($css_file) {
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
	protected function showCSS () {
		global $CONFIG;

		foreach ($this->_css_files as $file) {
			$fullpath = $CONFIG->getVal("application.base_path") . "/css/" . $file;
?>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $fullpath ?>" />
<?php
		}
	}


	/**
	* Add a JS file to the list of files to be included in the page.
	*
	* Can only be called from the view for a particular action.
	*
	* @param string JS file
	*/
	protected function useJS ($js_file) {
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
	protected function showJS () {
		global $CONFIG;

		foreach ($this->_js_files as $file) {
			$fullpath = $CONFIG->getVal("application.base_path") . "/js/" . $file;

?>
<script type="text/javascript" src="<?php echo $fullpath ?>"></script>
<?php
		}
	}


	/**
	* Create a slot in a page that can be filled in with text, HTML, etc.
	* Often used to allow the page header to be set by subsequent code.
	*
	* Can only be called from within a View.
	*
	* <code>
	* <title><?php $this->slot("page_title") ?></title>
	* </code>
	*
	* @param string name of the slot
	*/
	protected function slot ($slot_name) {
		if (isset ($this->_config["slots"][$slot_name])) {
			echo $this->_config["slots"][$slot_name];
		}
	}


	/**
	* Fill a slot with its contents.
	*
	* Can only be called from within a View.
	*
	* <code>
	* $this->fillslot("page_title", "This Page's Title");
	* </code>
	*
	* @param string name of the slot
	* @param string value of the slot
	*/
	protected function fillslot ($slot_name, $slot_value) {
		$this->_config["slots"][$slot_name] = $slot_value;
	}


	/**
	* Render the view.
	*/
    function render () {
		# All local variables in this function should actually be class
		# variables so that they are not exposed to the view code.


		global $CONFIG;


		# Find the body view corresponding to the controller and action.
		#
		if ( ! File::ready($this->_render["body_file"] = VIEWDIR.DS.$this->_controller.DS.$this->_action.".php") ) {
			Err::fatal( "Unable to render view for route '/$this->_controller/$this->_action', page body '" . $this->_render["body_file"] . "' not found." );
		}


		# Find the page header file if it is set to render in this view.
		#
		if ( $this->_config["render_header"] ) {
			if ( File::ready($this->_render["header_file"] = VIEWDIR.DS.$this->_controller.DS."header.php") ||
					File::ready($this->_render["header_file"] = VIEWDIR.DS."header.php") ) {
			} else {
				Err::fatal("Unable to render view for route '/$this->_controller/$this->_action', no page header found.");
			}
		}


		# Find the page footer file if it is set to render in this view.
		#
		if ( $this->_config["render_footer"] ) {
			if (File::ready($this->_render["footer_file"] = VIEWDIR.DS.$this->_controller.DS."footer.php") ||
					File::ready($this->_render["footer_file"] = VIEWDIR.DS."footer.php") ) {
			} else {
				Err::fatal("Unable to render view for route '/$this->_controller/$this->_action', no page footer found.");
			}
		}


		# Extract all the variables so that they are available to the view.
		#
		extract($this->_config["variables"]);


		# Run the PHP in the body view and capture the output.  This is run
		# first so that the body can modify the overall page, such as adding
		# CSS or JS files or by filling slots.
		#
		ob_start();
		include($this->_render["body_file"]);
		$this->_body_contents = ob_get_clean();


		# Display the page header.
		#
		if ( isset($this->_render["header_file"]) ) {
			ob_start();
			include($this->_render["header_file"]);
			ob_end_flush();
		}


		# Display the page body.
		#
		echo $this->_body_contents;


		# Display the page footer.
		#
		if ( isset($this->_render["footer_file"]) ) {
			ob_start();
			include($this->_render["footer_file"]);
			ob_end_flush();
		}
    }


	/**
	* Include a view in the current view.
	*
	* @param string name of the view to include, formatted as <controller>/<view name> (.php is optional)
	*/
	private function include_view ($path = "") {
		if ( empty($path) ) {
			Err::fatal("no view passed");
		}

		# trim all leading slashes
		$path = Route::trim($path);

		$parts = explode('/', $path);

		$controller = $parts[0];
		$view = $parts[1];

		$file = VIEWDIR.DS.$controller.DS.$view;

		if ( ! File::Ready($file) ) {
			$file .= ".php";
			if ( ! File::Ready($file) ) {
				Err::fatal("unable to find view '$path'");
			}
		}


		# Get the global variable for the application configuration.
		#
		global $CONFIG;


		# Extract all the variables so that they are available to the view.
		#
		extract ($this->_config["variables"]);


		include($file);
	}


	function __destruct () {}
}
