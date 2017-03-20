<?php
load_functions('people::people');

register_asset("people::css/people.css");

register_asset("people::js/people.js");

register_pager("people", array(
    'as' => 'people',
    'use' => 'people::people@people_pager')
);

register_pager("people/ajax", array(
    'as' => 'people-ajax',
    'use' => 'people::ajax@ajax_pager')
);


/*if(config('people-dashboard-menu-link', false)){add_menu("dashboard-main-menu", array("icon" => "<i class='ion-android-people'></i>", "id" => "people", "title" => lang("people::people"), "link" => url("people")));}
if(config('people-explorer-menu-link', true)){add_menu("dashboard-menu", array("icon" => "<i class='ion-android-people'></i>", "id" => "people", "title" => lang("people::people"), "link" => url("people")));}
if(config('people-footer-menu-link', false)){add_menu("footer-menu", array("id" => "people", "title" => lang('people::people'), "link" => url("people")));}*/

add_available_menu('people::people', 'people', 'ion-android-people');
