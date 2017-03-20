<?php
function booster_upgrade_database() {
    db()->query("ALTER TABLE `post_boost` ADD COLUMN `clicks` INT(11) NOT NULL DEFAULT '0'");
    db()->query("ALTER TABLE `post_boost` ADD COLUMN `click_stats` INT(11) NOT NULL DEFAULT '0'");
    db()->query("UPDATE `post_boost` SET `type`='Post' WHERE (`type`='Post Booster' OR `type`='Boost Post')");
}