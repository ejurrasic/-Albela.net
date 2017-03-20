<?php
function react_item_pager($app) {
    $result = array(
        'status' => 1
    );
    $type = input('type');
    $typeId = input('type_id');
    $code = input('code');
    $userid = input('userid');
    $app->userid = $userid;
    api_temporary_login_user($userid);
    like_react($type, $typeId, $code, $userid);

    return json_encode($result);
}

function react_load_pager($app) {
    $result = array(
        'status' => 1,
        'members' => array()
    );

    $type = input("type");
    $typeId = input("type_id");
    $limit = input("limit", 5);
    $people = get_reactors($type, $typeId, $limit);
    foreach($people as $user) {
        $result['members'][] = array(
            get_avatar(75, $user),
            $user['like_type'],
            get_user_name($user),
            $user['id']
        );
    }

    return json_encode($result);
}

function like_item_pager($app) {
    $result = array(
        'status' => 1,
        'likes' => 0,
        'dislikes' => 0,
        'has_done' => false,
        'has_dislike' => true
    );
    $userid = input('userid');
    $type = input('type');
    $typeId = input('type_id');

    //return json_encode($result);
    $app->userid = $userid;
    api_temporary_login_user($userid);
    $w= (has_liked($type, $typeId, 1, $userid)) ? 2 : 1;

    like_item(input('type'), input('type_id'), $w, $userid);
    $likeCount = count_likes($type, $typeId);
    $result['likes'] = ($likeCount) ? $likeCount : 0;
    $dislikeCount = count_likes($type, $typeId, 0);
    $result['dislikes'] = ($dislikeCount) ? $dislikeCount : 0;
    $result['has_like'] = (has_liked($type, $typeId, 1, $userid)) ? true : false;
    $result['has_dislike'] = (has_disliked($type, $typeId, 1, $userid)) ? true : false;
    return json_encode($result);
}


function dislike_item_pager($app) {
    $result = array(
        'status' => 1,
        'likes' => 0,
        'dislikes' => 0,
        'has_like' => true,
        'has_dislike' => true
    );
    $userid = input('userid');
    $type = input('type');
    $typeId = input('type_id');
    $app->userid = $userid;
    api_temporary_login_user($userid);
    $w= (has_disliked($type, $typeId, 1, $userid)) ? 2 : 1;

    dislike_item(input('type'), input('type_id'), $w, $userid);

    $likeCount = count_likes($type, $typeId);
    $result['likes'] = ($likeCount) ? $likeCount : 0;
    $dislikeCount = count_likes($type, $typeId, 0);
    $result['dislikes'] = ($dislikeCount) ? $dislikeCount : 0;

    $result['has_dislike'] = (has_disliked($type, $typeId, 1, $userid)) ? true : false;
    $result['has_like'] = (has_liked($type, $typeId, 1, $userid)) ? true : false;

    return json_encode($result);
}