<?php
function booster_install_database() {
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS `post_boost` (
	`pb_id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`post_id` INT(11) NOT NULL,
	`type` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`plan_type` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`plan_id` INT(11) NOT NULL,
	`quantity` INT(11) NOT NULL,
	`target_location` TEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`target_gender` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`target_age` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`impression_stats` INT(11) NOT NULL DEFAULT '0',
	`views` INT(11) NOT NULL DEFAULT '0',
	`paid` INT(11) NOT NULL DEFAULT '0',
	`status` INT(11) NOT NULL DEFAULT '0',
	`time` INT(11) NOT NULL,
	`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`pb_id`)
)
COLLATE='utf8_unicode_ci' ENGINE=InnoDB AUTO_INCREMENT=1;");

}
