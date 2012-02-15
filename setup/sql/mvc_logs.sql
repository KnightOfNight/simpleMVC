DROP TABLE IF EXISTS mvc_logs;

SET character_set_client = utf8;

CREATE TABLE mvc_logs (
  id int(10) unsigned NOT NULL auto_increment,
  session varchar(50) NOT NULL,
  unixtime int(10) unsigned NOT NULL,
  unixtimeus int(10) unsigned NOT NULL,
  level tinyint(2) unsigned NOT NULL,
  message varchar(1000) NOT NULL,
  PRIMARY KEY (id),
  INDEX mvc_logs_k_1 (session),
  INDEX mvc_logs_k_2 (unixtime),
  INDEX mvc_logs_k_3 (unixtimeus),
  INDEX mvc_logs_k_4 (level),
  INDEX mvc_logs_mk_1 (session, unixtime, unixtimeus)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_roman_ci;

