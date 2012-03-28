SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `my_table` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_created` int(10) unsigned NOT NULL,
  `item_class` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_something` tinyint(3) unsigned NOT NULL,
  `is_something_else` tinyint(3) unsigned NOT NULL,
  `date_reviewed` int(10) unsigned NOT NULL,
  `reviewed_by` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_something` (`is_something`,`date_created`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `giraffe_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `giraffe_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `necks` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `giraffe_id` (`giraffe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
