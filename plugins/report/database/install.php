<?php
function report_install_database() {
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `reports` (
      `report_id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `link` text COLLATE utf8_unicode_ci NOT NULL,
      `message` text COLLATE utf8_unicode_ci NOT NULL,
      `time` int(11) NOT NULL,
      PRIMARY KEY (`report_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
}
 