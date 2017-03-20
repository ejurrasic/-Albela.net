<?php
function chat_install_database() {
    $db = db();

    try{
        $db->query("ALTER TABLE  `users` ADD  `online_time` INT NOT NULL DEFAULT  '0' AFTER  `online_status` ;");
    } catch(Exception $e){}

    $db->query("CREATE TABLE IF NOT EXISTS `conversations` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL DEFAULT 'single',
  `user1` int(11) NOT NULL,
  `user2` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `last_update_time` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;
    ");

    $db->query("CREATE TABLE IF NOT EXISTS `conversation_members` (
  `member_cid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `active` int(11) DEFAULT '1',
  `time` int(11) NOT NULL,
  KEY `member_cid` (`member_cid`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("CREATE TABLE IF NOT EXISTS `conversation_messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `sender` int(11) NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `files` text COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `cid` (`cid`,`sender`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=42 ;
    ");

    $db->query("CREATE TABLE IF NOT EXISTS `conversation_messages_read` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `message_id` (`message_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

}
 