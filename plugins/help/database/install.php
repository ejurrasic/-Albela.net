<?php
function help_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `helps` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `content` text COLLATE utf8_unicode_ci NOT NULL,
      `category` int(11) NOT NULL,
      `tags` text COLLATE utf8_unicode_ci NOT NULL,
      `help_order` int(11) NOT NULL,
      `modify_time` int(11) NOT NULL,
      `time` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;");

}
 