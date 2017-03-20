<?php
function hashtag_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `hashtags` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `hashtag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `count` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `hashtag` (`hashtag`(191))
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;");
}
