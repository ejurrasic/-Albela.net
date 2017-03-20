<?php
function activity_upgrade_database() {
    $db = db();
    register_site_page("activities", array('title' => 'activity::activities', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'activities','content', 'middle');
        Menu::saveMenu('header-account-menu', 'activity::activity-log', 'activities');
        Widget::add(null,'activities','plugin::relationship|suggestions', 'right');

    });
}