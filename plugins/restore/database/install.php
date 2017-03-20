<?php
function restore_install_database() {
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS `restore_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `backup_title`  varchar(100) NOT NULL,
    `backup_host`  varchar(100) NOT NULL,
    `last_backup_date`  varchar(100) NOT NULL,
    `last_restore_date`  varchar(100) NULL,
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
    $db->query("INSERT INTO restore_types VALUES('1','Restore Point','FTP Host','','');");
    $db->query("INSERT INTO restore_types VALUES('2','Restore Point','FTP Host','','');");
    $db->query("INSERT INTO restore_types VALUES('3','Restore Point','FTP Host','','');");
    $db->query("INSERT INTO restore_types VALUES('4','Restore Point','FTP Host','','');");
    $db->query("INSERT INTO restore_types VALUES('5','Restore Point','FTP Host','','');");
    $db->query("INSERT INTO restore_types VALUES('6','Restore Point','FTP Host','','');");
}