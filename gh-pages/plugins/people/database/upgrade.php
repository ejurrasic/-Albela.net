<?php
function people_upgrade_database() {
    people_dump_site_pages();
}

function people_dump_site_pages() {
    register_site_page("people", array('title' => lang('people::people'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null,'people','content', 'middle');
        Widget::add(null,'people','plugin::people|filter', 'left');
        Menu::saveMenu('main-menu', 'people::people', 'people', 'manual', 1, 'ion-android-people');
    });
}