<?php
function blog_upgrade_database() {
    $db = db();
    try{$db->query("ALTER TABLE  `blogs` ADD  `featured` TINYINT(1) NOT NULL DEFAULT  '0'");}catch(Exception $e){$error = $e;}
    register_site_page('blogs', array('title' => 'blog::blogs', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'blogs', 'content', 'middle');
        Widget::add(null, 'blogs', 'plugin::blog|menu', 'right');
        Widget::add(null, 'feed', 'plugin::blog|latest', 'right');
        Widget::add(null, 'profile', 'plugin::blog|profile-recent', 'right');
        Menu::saveMenu('main-menu', 'blog::blogs', 'blogs', 'manual', true, 'ion-android-clipboard');
    });
    register_site_page('blog-add', array('title' => 'blog::blogs-add-page', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'blog-add', 'content', 'middle');
    });
    register_site_page('blog-manage', array('title' => 'blog::manage-blogs', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'blog-manage', 'content', 'middle');
    });
    register_site_page('blog-page', array('title' => 'blog::blog-view-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'blog-page', 'content', 'middle');
        Widget::add(null, 'blogs', 'plugin::blog|menu', 'right');
        Widget::add(null, 'blog-page', 'plugin::blog|related', 'right');
    });
}