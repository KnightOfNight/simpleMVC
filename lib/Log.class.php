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
class Log {
	const NONE = 0;
	const ERROR = 1;
	const WARN = 2;
	const INFO = 4;
	const DEBUG = 8;

	private $_level = 0;
	private $_log_levels = array (	Log::NONE => "NONE",
									Log::ERROR => "ERROR",
									Log::WARN => "WARN",
									Log::INFO => "INFO",
									Log::DEBUG => "DEBUG");
	private $_max_level = 0;


	/**
	* Create a new Log object.
	*
	* @param integer log level
	* @return Log a new Log object
	*/
	function __construct ($level) {
		$max_level = array_sum (array_keys($this->_log_levels));

		$this->_level = ($level < 0) ? Log::NONE : ($level > $max_level) ? $max_level : $level;

		$this->msg(Log::INFO, "MCSMVC starting");
	}


	/**
	* Write out a log message if the message level is allowed by the selected
	* log level.
	*
	* @param int the level of the message being logged
	* @param string the message to log
	*/
	function msg ($level, $message) {
		if ( ($this->_level === Log::NONE) || (! ($level & $this->_level)) ) {
			return;
		}

		$time = explode (" ", microtime ());

		$log = new LogModel();

		$log->value("session", $GLOBALS["SESSION_ID"]);
		$log->value("unixtime", $time[1]);
		$log->value("unixtimeus", $time[0] * 1000000);
		$log->value("level", $level);
		$log->value("message", $message);

		$log->create(FALSE);
	}


	/**
	* Write a final message to the log and close it.
	*/
	function close () {
		$this->msg (Log::INFO, sprintf ("MCSMVC finishing, %.6fs elapsed", microtime(TRUE) - $GLOBALS["START_TIME"]));
	}


    function __destruct () {}
}
