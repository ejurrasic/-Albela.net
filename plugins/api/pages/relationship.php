<?php
function list_requests_pager($app) {
    $requests = get_friend_requests();
    $result = array();
    foreach($requests->results() as $request) {
        $result[] = api_arrange_user($request);
    }

    return json_encode($result);
}

function process_follow_pager($app) {
    process_follow(input('type'), input('to_userid'));
    return json_encode(array("status" => 1));
}

function add_friend_pager($app) {
    add_friend(input('to_userid'));
    return json_encode(array("status" => 1));
}

function remove_friend_pager() {
    remove_friend(input('to_userid'));
    return json_encode(array("status" => 1));
}

function confirm_friend_request_pager($app) {
    $userid = input('to_userid');
    confirm_friend_request($userid);
    return json_encode(array("status" => 1));
}

function friend_suggestion_pager($app) {
    $limit = input("limit", 10);
    $term = input("term");
    if ($term) {
        $users = search_users($term);
    } else {
        $users = relationship_suggest($limit);
    }

    $result = array();
    foreach($users->results() as $user) {
        if ($user) {
            $a = api_arrange_user($user);
            $a['friend_status'] = friend_status($user['id']);
            $result[] = $a;
        }
    }
    return json_encode($result);
}

function friend_online_pager($app) {
    $limit = input("limit", 10);
    $term = input("term");
    $users = chat_get_onlines();

    $result = array();
    foreach($users as $user) {
        if ($user) {
            $a = api_arrange_user($user);
            $a['friend_status'] = friend_status($user['id']);
            $result[] = $a;
        }
    }
    return json_encode($result);
}