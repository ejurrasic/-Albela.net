<?php
function like_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `likes` (
  `like_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `like_type` int(11) NOT NULL DEFAULT '1',
  `type` varchar(255) NOT NULL,
  `type_id` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `type` (`type`,`type_id`),
  KEY `like_type` (`like_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=179 ;
");
}
 