<?php
load_functions("relationship::relationship");
function process_follow_pager($app) {
    CSRFProtection::validate(false);
    process_follow(input('type'), input('userid'));
}

function add_friend_pager($app) {
    CSRFProtection::validate(false);
    return add_friend(input('userid'));
}

function remove_friend_pager() {
    CSRFProtection::validate(false);
    return remove_friend(input('userid'));
}

function load_friend_requests_pager($app) {
    CSRFProtection::validate(false);
    pusher()->reset('friend-request');
    fire_hook('relationship.requests.load');
    return view("relationship::dropdown", array('requests' => get_friend_requests(true)));
}

function confirm_friend_request_pager($app) {
    CSRFProtection::validate(false);
    $userid = input('userid');
    return confirm_friend_request($userid);
}

function friend_requests_pager($app) {
    get_menu("dashboard-main-menu", 'find-friends')->setActive(true);
    $app->setTitle(lang('relationship::friend-requests'));
    pusher()->reset('friend-request');
    fire_hook('relationship.requests.load');
    return $app->render(view("relationship::requests", array('requests' => get_friend_requests())));
}

function suggestions_pager($app) {
    $app->setTitle(lang('relationship::people-suggestion'));
    get_menu("dashboard-main-menu", 'find-friends')->setActive(true);
    $users = relationship_suggest(15);

    return $app->render(view('relationship::suggestions-page', array('users' => relationship_suggest(15))));
}

function preload_friend_requests_pager($app) {
    CSRFProtection::validate(false);
    return view("relationship::dropdown", array('requests' => preload_friend_requests(input('ids'))));
}