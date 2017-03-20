<?php
function photo_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `photo_albums` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `entity_id` int(11) NOT NULL,
      `entity_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
      `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `description` text COLLATE utf8_unicode_ci NOT NULL,
      `category_id` int(11) NOT NULL,
      `privacy` int(11) NOT NULL DEFAULT '1',
      `time` int(11) NOT NULL,
      `default_photo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`id`),
      KEY `category_id` (`category_id`),
      KEY `entity_id` (`entity_id`),
      KEY `entity_type` (`entity_type`),
      KEY `privacy` (`privacy`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

    $db->query("CREATE TABLE IF NOT EXISTS `photo_categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `order` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `order` (`order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
}
 