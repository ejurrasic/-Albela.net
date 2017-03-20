<?php
function event_upgrade_database() {
    register_site_page('events', array('title' => 'event::events', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'events', 'plugin::event|menu', 'left');
        Widget::add(null, 'events', 'content', 'middle');
        Menu::saveMenu('main-menu', 'event::events', 'events', 'manual', true, 'ion-android-calendar');
        Widget::add(null, 'events', 'plugin::event|birthdays', 'right');
    });

    register_site_page('event-profile', array('title' => 'event::events-profile-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'event-profile', 'plugin::event|profile-stat', 'right');
        Widget::add(null, 'event-profile', 'plugin::event|profile-invite', 'right');
        Widget::add(null, 'event-profile', 'content', 'middle');
    });

    register_site_page('event-create', array('title' => 'event::event-create', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'event-create', 'content', 'middle');
    });
}