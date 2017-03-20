<?php
function group_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `group_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `group_description` text COLLATE utf8_unicode_ci NOT NULL,
  `group_logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `group_cover` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `group_cover_resized` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `moderators` text COLLATE utf8_unicode_ci NOT NULL,
  `privacy` int(11) NOT NULL DEFAULT '1',
  `who_can_post` int(11) NOT NULL DEFAULT '1',
  `who_can_add_member` int(11) NOT NULL DEFAULT '1',
  `group_created_time` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`),
  KEY `user_id` (`user_id`,`group_name`,`group_title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;
");

    $db->query("
    CREATE TABLE IF NOT EXISTS `group_members` (
  `member_id` int(11) NOT NULL,
  `member_group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");




    
}
 