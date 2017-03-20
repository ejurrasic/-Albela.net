<?php
function relationship_upgrade_database() {
    register_site_page("friend-requests", array('title' => 'friend-requests-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(227778433660005,'friend-requests','content', 'middle');

        Widget::add(22777843366422005,'profile','plugin::relationship|friends', 'right');
        Widget::add(2277446422005,'profile','plugin::relationship|followers', 'right');
        Widget::add(22774464228998005,'profile','plugin::relationship|following', 'right');
        Menu::saveMenu('main-menu', 'find-friends', 'suggestions', 'manual', true, 'ion-android-person-add');
        Menu::saveMenu('main-menu', 'profile', 'me', 'manual', true, 'ion-ios-contact-outline');
    });

    register_site_page("suggestions", array('title' => 'people-you-may-know-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(227747843334660005,'suggestions','content', 'middle');
        Widget::add(227747843334669905,'feed','plugin::relationship|suggestions', 'right');
    });


}