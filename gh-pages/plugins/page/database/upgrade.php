<?php
function page_upgrade_database() {
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS `page_invites` (
      `page_id` int(11) NOT NULL,
      `inviter_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    register_site_page('pages', array('title' => 'page::pages', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function(){
        Widget::add(null, 'pages', 'content', 'middle');
        Widget::add(null, 'pages', 'plugin::page|menu', 'right');
        Widget::add(null, 'pages', 'plugin::page|featured', 'right');
        Widget::add(null, 'feed', 'plugin::page|latest', 'right');
        Widget::add(null, 'profile', 'plugin::page|user-profile-likes', 'right');
        Menu::saveMenu('main-menu', 'page::pages', 'pages', 'manual', true, 'ion-card');
    });

    register_site_page('page-create', array('title' => 'page::page-create', 'column_type' => ONE_COLUMN_LAYOUT), function(){
        Widget::add(null, 'page-create', 'content', 'middle');
    });

    register_site_page('page-manage', array('title' => 'page::page-manage', 'column_type' => ONE_COLUMN_LAYOUT), function(){
        Widget::add(null, 'page-manage', 'content', 'middle');
    });

    register_site_page('page-roles', array('title' => 'page::page-roles', 'column_type' => ONE_COLUMN_LAYOUT), function(){
        Widget::add(null, 'page-roles', 'content', 'middle');
    });

    register_site_page('page-manage-fields', array('title' => 'page::page-manage-fields', 'column_type' => ONE_COLUMN_LAYOUT), function(){
        Widget::add(null, 'page-manage-fields', 'content', 'middle');
    });

    register_site_page('page-profile', array('title' => 'page::page-profile', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function(){
        Widget::add(null, 'page-profile', 'content', 'middle');
        Widget::add(null, 'page-profile', 'plugin::page|info', 'right');
        Widget::add(null, 'page-profile', 'plugin::page|profile-invite', 'right');
        Widget::add(null, 'page-profile', 'plugin::page|profile-photo', 'right');
    });

    register_site_page('page-profile-about', array('title' => 'page::page-profile-about', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function(){
        Widget::add(null, 'page-profile-about', 'content', 'middle');
        Widget::add(null, 'page-profile-about', 'plugin::page|info', 'right');
        Widget::add(null, 'page-profile-about', 'plugin::page|profile-invite', 'right');
        Widget::add(null, 'page-profile-about', 'plugin::page|profile-photo', 'right');
    });

    register_site_page('page-profile-photos', array('title' => 'page::page-profile-photos', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function(){
        Widget::add(null, 'page-profile-photos', 'content', 'middle');
        Widget::add(null, 'page-profile-photos', 'plugin::page|info', 'right');
        Widget::add(null, 'page-profile-photos', 'plugin::page|profile-invite', 'right');
        Widget::add(null, 'page-profile-photos', 'plugin::page|profile-photo', 'right');
    });

    db()->query("ALTER TABLE  `pages` ADD  `featured` INT NOT NULL DEFAULT  '0' AFTER  `page_category_id` ;");
}
 