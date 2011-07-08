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
		$max_level = array_sum( array_keys($this->_log_levels) );

		$this->_level = ($level < 0) ? Log::NONE : ($level > $max_level) ? $max_level : $level;

		$this->___msg(Log::INFO, Config::get('framework.version') . ' starting');
	}


	/**
	* Write out a log message.  If the message level is not allowed by the
	* current application log level, the message will not be written.
	*
	* @param integer message level
	* @param string message
	*/
	static function msg ($level, $message) {
		global $simpleMVC;

        if ( (! isset($simpleMVC['log'])) OR (! ($log = $simpleMVC['log']) instanceof Log) ) {
            Err::fatal("Log::" . __function__ . "() called before logging setup.");
        }

		$log->___msg($level, $message);
	}


	/**
	* Internal function to write out a log message.
	*
	* @param integer message level
	* @param string message
	*/
	function ___msg ($level, $message) {
		if ( ($this->_level === Log::NONE) || (! ($level & $this->_level)) ) {
			return;
		}

		$time = explode (" ", microtime ());

		$log = new LogModel();

		$log->value("session", session_id());
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
		global $simpleMVC;

		$start_time = $simpleMVC['start_time'];

		$this->msg(Log::INFO, Config::get('framework.version') . ' finishing, ' . sprintf ("%.6fs elapsed", microtime(TRUE) - $start_time));
	}


    function __destruct () {}
}
