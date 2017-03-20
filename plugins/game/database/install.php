<?php
function game_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `games` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `game_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `game_description` text COLLATE utf8_unicode_ci NOT NULL,
  `game_logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `game_cover` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `game_cover_resized` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `game_file` text COLLATE utf8_unicode_ci NOT NULL,
  `game_code` text COLLATE utf8_unicode_ci NOT NULL,
  `game_width` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `game_height` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `players_count` int(11) NOT NULL,
  `players` text COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;");

    $db->query("
    CREATE TABLE IF NOT EXISTS `game_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cover` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;
    ");




    
}
 