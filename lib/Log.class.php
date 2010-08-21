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
* Provide basic logging functionality to the application.
*
* @package MVCAPI
*
*/
class Log {
	private $_log_level = 0;
	private $_log_file = "";
	private $_log_fp = "";

	const NONE = 0;
	const ERROR = 1;
	const WARN = 2;
	const INFO = 4;
	const DEBUG = 8;

	const MINLVL = 0;
	const MAXLVL = 15;

	const LOGFILE = "mcsmvc.log";
	const LOCK_RETRIES = 5;

	private $_log_level_names = array (0 => "NONE", 1 => "ERROR", 2 => "WARN", 4 => "INFO", 8 => "DEBUG");


	/**
	* Create a new Log object.
	* @param integer log level
	* @return Log a new Log object
	*/
	function __construct ($level) {
		if ( ($level < Log::MINLVL) OR ($level > Log::MAXLVL) ) {
			$this->_log_level = Log::NONE;
		} else {
			$this->_log_level = $level;
		}

		if ($this->_log_level === Log::NONE) {
			return;
		}

		$this->_log_file = ROOT.DS."tmp".DS."logs".DS.Log::LOGFILE;

		if ( ! ($this->_log_fp = fopen ($this->_log_file, "a")) ) {
			Error::fatal ("unable to open log file '" . $this->_log_file . "'");
		}

		for ($i = 0; $i < Log::LOCK_RETRIES; $i++) {
			if (flock ($this->_log_fp, LOCK_EX)) {
				break;
			}
			usleep (250000);
		}

		if ($i === Log::LOCK_RETRIES) {
			Error::fatal ("unable to lock log file '" . $this->_log_file . "'");
		}

		$level = $this->_log_level;
		$this->_log_level = Log::INFO;
		$this->msg (Log::INFO, "starting");
		$this->_log_level = $level;
	}


	/**
	* Write out a log message if the message level is if the current log level allows it.
	*
	* @param int the level of the message being logged
	* @param string the message to log
	*/
	function msg ($level, $message) {
		if ($this->_log_level === Log::NONE) {
			return;
		}

		if ($level & $this->_log_level) {
			fwrite ($this->_log_fp, $GLOBALS["SESSION_ID"] . ":" . sprintf ("%.7f", microtime (TRUE)) . " - " . $this->_log_level_names[$level] . " - " . $message . "\n");
		}
	}

    function __destruct () {
		$this->_log_level = Log::INFO;
		$this->msg (Log::INFO, sprintf ("finishing, %.6fs elapsed", microtime(TRUE) - $GLOBALS["START_TIME"]));

		if (! fclose ($this->_log_fp)) {
			Error::fatal ("unable to close log file '" . $this->_log_file . "'");
		}
	}
}
