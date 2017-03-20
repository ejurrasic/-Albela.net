<?php
function help_upgrade_database() {
    register_site_page("helps", array('title' => 'help::help', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(null, 'helps', 'content', 'middle');
        Menu::saveMenu('footer', 'help::helps', 'help', 'manual', true, 'ion-help-buoy');
    });
    register_site_page("help", array('title' => 'help::helps', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(null, 'help', 'content', 'middle');
    });
    register_site_page("sub-help", array('title' => 'help::sub-help', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(null, 'sub-help', 'content', 'middle');
    });
}