<?php
function ads_upgrade_database() {
    register_site_page('ads-manage', array('title' => lang('ads::manage-ads'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null,'ads-manage', 'content', 'middle');
        Menu::saveMenu('header-account-menu', 'ads::create-ads', 'ads/create');
        Menu::saveMenu('header-account-menu', 'ads::manage-ads', 'ads');
        Widget::add(null,'feed','plugin::ads|ads', 'right');
    });
    register_site_page('ads-create', array('title' => lang('ads::ads-create'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null,'ads-create', 'content', 'middle');
    });
}