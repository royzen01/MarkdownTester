-- MySQL dump 10.13  Distrib 5.7.21, for Linux (x86_64)
--
-- Host: localhost    Database: it490
-- ------------------------------------------------------
-- Server version	5.7.21-0ubuntu0.16.04.1

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `username` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `h_password` varchar(64) NOT NULL,
  `salt` varchar(30) NOT NULL,
  `firstname` varchar(25) DEFAULT NULL,
  `lastname` varchar(25) DEFAULT NULL,
  `created_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_modified_datetime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book` (
  `book_id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `listing_price` DECIMAL(10,2) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `username_id` varchar(50) NOT NULL,
  `description` varchar(4000) NOT NULL,
  `created_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_modified_datetime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`book_id`),
  KEY `fk_book_user_username_id` (`username_id`),
  CONSTRAINT `fk_book_username_username_id` FOREIGN KEY (`username_id`) REFERENCES `user` (`username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `review` (
  `review_id` int(10) NOT NULL AUTO_INCREMENT,
  `username_id` varchar(50) NOT NULL,
  `book_id` int(10) NOT NULL,
  `review_text` varchar(1000) NOT NULL,
  `review_rating` decimal(2,1) NOT NULL,
  `created_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_modified_datetime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `fk_review_user_username_id` (`username_id`),
  KEY `fk_review_book_book_id` (`book_id`),
  CONSTRAINT `fk_review_user_username_id` FOREIGN KEY (`username_id`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_book_book_id` FOREIGN KEY (`book_id`) REFERENCES `book` (`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `flag`
--

DROP TABLE IF EXISTS `flag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flag` (
  `flag_id` int(10) NOT NULL AUTO_INCREMENT,
  `username_id` varchar(50) NOT NULL,
  `book_id` int(10) NOT NULL,
  `flag_text` varchar(1000) NOT NULL,
  `created_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_modified_datetime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`flag_id`),
  KEY `fk_flag_user_username_id` (`username_id`),
  KEY `fk_flag_book_book_id` (`book_id`),
  CONSTRAINT `fk_flag_user_username_id` FOREIGN KEY (`username_id`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  CONSTRAINT `fk_flag_book_book_id` FOREIGN KEY (`book_id`) REFERENCES `book` (`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `rate_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_type` (
  `rate_type_id` int(10) NOT NULL,
  `rate_description` varchar(50) NOT NULL,
  `created_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_modified_datetime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rate_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `rate_type` (`rate_type_id`, `rate_description`) VALUES(1, 'Flat');
INSERT INTO `rate_type` (`rate_type_id`, `rate_description`) VALUES(2, 'Hourly');

ALTER TABLE book
ADD COLUMN rate_type_id int(10) NOT NULL AFTER description;

ALTER TABLE book
ADD FOREIGN KEY (rate_type_id) REFERENCES rate_type(rate_type_id);

DROP TABLE IF EXISTS `userkey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userkey` (
  `userkey_Id` int(10) NOT NULL AUTO_INCREMENT,
  `username_id` varchar(50) NOT NULL,
  `uniquekey` varchar(100) NOT NULL,
  `created_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_modified_datetime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userkey_Id`),
  KEY `fk_userkey_user_username_id` (`username_id`),
  CONSTRAINT `fk_userkey_user_username_id` FOREIGN KEY (`username_id`) REFERENCES `user` (`username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
