<?php
function comment_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `entity_type` varchar(100) NOT NULL DEFAULT 'user',
  `type` varchar(255) NOT NULL,
  `type_id` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `image` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `type` (`type`,`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci AUTO_INCREMENT=119 ;");
}
 