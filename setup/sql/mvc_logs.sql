/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table mvc_logs
--

DROP TABLE IF EXISTS mvc_logs;
SET @saved_cs_client     = @@character_set_client;
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
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
