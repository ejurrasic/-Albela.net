<?php
function announcement_upgrade_database() {
    $db = db();

    $db->query("ALTER TABLE  `announcements` ADD  `active` INT NOT NULL DEFAULT  '1'");

    register_site_page('announcements', array('title' => 'announcement::announcements', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'announcements', 'content', 'middle');
        Menu::saveMenu('header-account-menu', 'announcement::announcements', 'announcements');
    });
}
 