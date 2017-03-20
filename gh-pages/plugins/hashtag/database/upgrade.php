<?php
function hashtag_upgrade_database() {
    hashtag_dump_site_pages();
}

function hashtag_dump_site_pages() {
    register_site_page('hashtag', array('title' => lang('hashtag::hashtag'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
            Widget::add(null, 'hashtag', 'content', 'middle');
            Widget::add(null, 'hashtag', 'plugin::hashtag|discover', 'right');
            Menu::saveMenu('main-menu', 'hashtag::discover', 'hashtag', 'manual', true, 'ion-pound');
        }
    );
}