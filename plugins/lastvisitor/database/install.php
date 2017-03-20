<?php
function lastvisitor_install_database() {
    $db = db();

    $db->query("
    CREATE TABLE IF NOT EXISTS `lastvisitor_profile_view` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `viewer_id`  varchar(100) NOT NULL,
    `viewed_id`  varchar(100) NOT NULL,
    `view_date`  varchar(100) NOT NULL,
    `gender`  varchar(100) NULL,
    `has_avatar`  varchar(100) NULL,
     PRIMARY KEY (`id`)
)    ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
    ");

}
 