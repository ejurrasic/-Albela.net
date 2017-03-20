<?php
function group_upgrade_database() {
    register_site_page('group-manage', array('title' => 'group::groups', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'group-manage', 'content', 'middle');
        Widget::add(null, 'group-manage', 'plugin::group|menu', 'left');
        Widget::add(null, 'group-manage', 'plugin::group|top', 'left');
        Widget::add(null, 'group-manage', 'plugin::group|featured', 'left');

        Menu::saveMenu('main-menu', 'group::groups', 'groups', 'manual', 1, 'ion-ios-people');
    });

    register_site_page('group-create', array('title' => 'group::group-create', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'group-create', 'content', 'middle');
    });

    register_site_page('group-profile', array('title' => 'group::group-profile', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'group-profile', 'content', 'middle');
        Widget::add(null, 'group-profile', 'plugin::group|info', 'right');
        Widget::add(null, 'group-profile', 'plugin::group|members', 'right');
    });

    db()->query("ALTER TABLE  `groups` ADD  `featured` INT NOT NULL DEFAULT  '0' AFTER  `who_can_add_member` ;");

}