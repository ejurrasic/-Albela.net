<?php

function like_item_pager($app) {
    CSRFProtection::validate(false);
    $action = input('action', 'like');
    if ($action == 'like') {
        echo like_item(input('type'), input('type_id'), input('w'));
    } else{
        echo dislike_item(input('type'), input('type_id'), input('w'));
    }
}

function load_people_pager($app) {
    CSRFProtection::validate(false);
    $action = input('action', 'like');
    $type = input('type');
    $typeId = input('id');
    $code = input('code', 'all');
    if ($action == 3) {
        return view('like::reactors', array('type' => $type, 'type_id' => $typeId));
    } else {
        return view('like::people', array('likes' => get_likes_people($type, $typeId, $action)));
    }
}

function react_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $typeId = input('id');
    $code = input('code');
    like_react($type, $typeId, $code);
    return view('like::reacts', array('type' => $type, 'type_id' => $typeId));
}
 