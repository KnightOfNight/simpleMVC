<?php


/**
* @author >X @ MCS 'Net Productions
* @package simpleMVC
* @version 0.5.0
*/


/**
* Singleton class that provides acess to debugging tools.
*
* @package simpleMVC
*/
class Dbg {
	private function __construct () {}


	/**
	* Dump the contents and structure of a variable to the screen.
	*
	* @param string variable name
	* @param mixed variable contents
	*/
	static function var_dump ($var_name, $var_contents) {
		printf ("<pre>\n");
		printf ("%s => ", $var_name);
		var_dump ($var_contents);
		printf ("</pre>\n");
	}


	/**
	* Print a message to the screen.
	*
	* @param string message
	*/
	static function msg ($message) {
		printf ("<pre>\n");
		printf ("%s\n", $message);
		printf ("</pre>\n");
	}


	/**
	* Display the current stack trace.
	*/
	static function backtrace () {
		$backtrace = debug_backtrace();

		if (empty($backtrace)) {
			return;
		}

		$backtrace = array_reverse($backtrace);

		echo "<table><thead><tr><th>Function</th><th>File</th><th>Line</th></tr>";

		foreach ($backtrace as $trace) {
			echo "<tr>";

			echo "<td>";
    		if ( isset($trace["class"]) ) {
				echo $trace["class"] . "::";
			}
			echo $trace["function"] . "( ";
			if ( isset($trace["args"]) ) {
				for ($i = 0; isset($trace["args"][$i]); $i++) {
					$arg = $trace["args"][$i];

					if (is_array($arg)) {
						print_r($arg);
					} else {
						echo $arg;
					}

					if ( isset($trace["args"][$i+1]) ) {
						echo " , ";
					}
	
				}
			}
			echo " )";
			echo "</td>";

			echo "<td>";
			echo $trace["file"];
			echo "</td>";

			echo "<td>";
			echo $trace["line"];
			echo "</td>";

			echo "</tr>";
		}

		echo "</table>";
	}


	function __destruct () {}
}
