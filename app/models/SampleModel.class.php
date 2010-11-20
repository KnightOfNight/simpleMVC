<?php

class SampleModel extends BaseModel {
	function __construct () {
		# If you need to override the name of the database table from the
		# default (app_<lower case singular of class name>) then you should use
		# these lines.
		#
		# $this->table = "TABLENAME";
		# $this->setup();
	}
}
