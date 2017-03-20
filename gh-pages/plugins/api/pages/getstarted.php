<?php
function upload_avatar_pager($app) {
    $userid = input("userid");
    $result = array(
        'status' => 0,
        'image' => '',
        'message' => ''
    );

    if (input_file('avatar') && $userid) {
        $uploader = new Uploader(input_file('avatar'), 'image');
        $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/');
        if ($uploader->passed()) {
            $avatar = $uploader->resize()->toDB("profile-avatar", get_userid(), 1)->result();
            $result['status'] = 1;
            $result['image'] = url_img($avatar, 600);
            update_user_avatar($avatar, null, $uploader->insertedId, false);
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function suggestion_pager($app) {
     $userid = input("userid");
    $result = array();
    $users = relationship_suggest(20);
    foreach($users as $user) {
        $dUser = array(
            'userid' => $user['id'],
            'name' => get_user_name($user),
            'image' => get_avatar(75, $user),
            'bio' => $user['bio']
        );
        $result[] = $user;
    }

    return json_encode($result);
}