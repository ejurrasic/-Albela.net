<?php
function notification_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_userid` int(11) NOT NULL,
  `to_userid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `type_id` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `data` text NOT NULL,
  `seen` int(11) NOT NULL DEFAULT '0',
  `mark_read` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
");
}
 