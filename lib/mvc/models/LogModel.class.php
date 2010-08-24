<?php

class LogModel extends BaseModel {
	function __construct () {
		$this->table = "mvc_logs";
		$this->setup();
	}
}
