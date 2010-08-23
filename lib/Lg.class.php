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
* Provide basic logging functionality to the application.
*
* @package MCS_MVC_API
*
*/
class Lg {
	const NONE = 0;
	const ERROR = 1;
	const WARN = 2;
	const INFO = 4;
	const DEBUG = 8;

	private $_level = 0;
	private $_log_levels = array (	Lg::NONE => "NONE",
									Lg::ERROR => "ERROR",
									Lg::WARN => "WARN",
									Lg::INFO => "INFO",
									Lg::DEBUG => "DEBUG");
	private $_max_level = 0;


	/**
	* Create a new Lg object.
	*
	* @param integer log level
	* @return Lg a new Lg object
	*/
	function __construct ($level) {
		$max_level = array_sum (array_keys($this->_log_levels));

		$this->_level = ($level < 0) ? Lg::NONE : ($level > $max_level) ? $max_level : $level;

		$this->msg (Lg::INFO, "starting");
	}


	/**
	* Write out a log message if the message level is allowed by the selected
	* log level.
	*
	* @param int the level of the message being logged
	* @param string the message to log
	*/
	function msg ($level, $message) {
		if ( ($this->_level === Lg::NONE) || (! ($level & $this->_level)) ) {
			return;
		}

#Debug::var_dump("level", $level);

		global $DB;

		if (! isset ($DB)) {
			Error::fatal("Database connection is not open, unable to log message...\n\n" . $message);
		}

		$log = new LogModel();

		$time = explode (" ", microtime ());

		$log->value("session", $GLOBALS["SESSION_ID"]);
		$log->value("unixtime", $time[1]);
		$log->value("unixtimeus", $time[0] * 1000000);
		$log->value("level", $level);
		$log->value("message", $message);

#Debug::var_dump("time", $time);
#Debug::var_dump("log", $log);

		$log->save();
	}


	/**
	* Write a final message to the log and close it.
	*/
	function close () {
		$this->msg (Lg::INFO, sprintf ("finishing, %.6fs elapsed", microtime(TRUE) - $GLOBALS["START_TIME"]));
	}


    function __destruct () {}
}
