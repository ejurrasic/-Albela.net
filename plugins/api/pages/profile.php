<?php
function load_details_pager($app) {
    $user = find_user(input("the_userid"));
    $photos = get_photos($user['id'], 'user-all', 3);
    $requestPhotos = array();
    if ($photos) {
        foreach($photos as $photo) {
            $requestPhotos[] = url_img($photo['path'], 600);
        }
    }

    $profileInfo = array();
    $birthday = $user['birth_day'].','.lang($user['birth_month']);
    if(can_view_birthdate($user)) {
        $birthday .= ",".$user['birth_year'];
    }
    $profileInfo[] = array('name' => "online_time", "value" => apiTimeAgo($user['online_time']));
    $profileInfo[] = array('name' => "gender", "value" => $user['gender']);
    $profileInfo[] = array('name' => "birth", "value" => $birthday);
    $profileInfo[] = array('name' => "bio", "value" => $user['bio']);
    if ($user['city']) $profileInfo[] = array('name' => "city", "value" => $user['city']);
    if ($user['state']) $profileInfo[] = array('name' => "state", "value" => $user['state']);
    if ($user['country']) $profileInfo[] = array('name' => "country", "value" => $user['country']);

    foreach(get_custom_fields('user', null) as $field) {
        if(get_user_data($field['title'], $user)) {
            $profileInfo[] = array('name' => lang($field['title']), "value" => get_user_data($field['title'], $user));
            //$profileInfo[lang($field['title'])] = get_user_data($field['title'], $user);
        }
    }
    return json_encode(array(
        'name' => get_user_name($user),
        'avatar' => get_avatar(200, $user),
        'id' => $user['id'],
        'verified' => ($user['verified']) ? true : false,
        'cover' => get_user_cover($user, false),
        'friend_status' => friend_status($user['id']),
        'follow_status' => is_following($user['id']),
        'recent_photos' => $requestPhotos,
        'profile_info' => $profileInfo,
        'can_post_timeline' => (can_post_on_timeline($user)) ? true : false
    ));
}

function friends_pager($app) {
    $user = find_user(input("the_userid"));
    $limit = 20;
    $term = input('term');

    if ($term) {
        $users = implode(',', get_friends($user['id']));
        $fields = get_users_fields();
        if (empty($users)) $users = "0";
        $query = "SELECT {$fields} FROM users WHERE id IN ({$users}) AND (first_name LIKE '%{$term}%' OR last_name LIKE '%{$term}%' OR email_address LIKE '%{$term}%' OR username LIKE '%{$term}%')";
        $users = paginate($query, $limit);
    } else {
        $users = get_friends($user['id']);

        if (empty($users)) $users = array("0");
        $users = list_connections($users, $limit);
    }
    $result = array();
    if ($users) {
        foreach($users->results() as $user) {
            $result[] = api_arrange_user($user);
        }
    }
    return json_encode($result);
}

function photos_pager($app) {
    $user = find_user(input("the_userid"));
    $limit = input("limit", 10);
    $offset = input("offset", 0);
    $photos = get_photos($user['id'], 'user-all', $limit, $offset);
    $result = array();
    foreach($photos as $photo) {
        $result[] = array(
            'id' => $photo['id'],
            'path' => url_img($photo['path'], 600)
        );
    }

    return json_encode($result);
}

function albums_pager($app) {
    $user = find_user(input("the_userid"));
    $limit = input("limit", 10);
    $offset = input("offset", 0);
    $albums = get_photo_albums('user',$user['id'], false, $limit, $offset);

    $result = array();
    if ($offset == 0) {
        $count = count_user_photos($user['id']);
        if ($count > 0) {
            $result[] = array(
                'id' => "profile",
                'title' => "profile-photos",
                'image' => url_img(get_last_user_photo($user['id'])['path'], 600),
                "userid" => $user['id']
            );
        }

        $count = count_user_photos($user['id'], "user-posts");
        if ($count > 0) {
            $result[] = array(
                'id' => "timeline",
                'title' => "timeline-photos",
                'image' => url_img(get_last_user_photo($user['id'], "user-posts")['path'], 600),
                "userid" => $user['id']
            );
        }

        $count = count_user_photos($user['id'], 'profile-cover');
        if ($count > 0) {
            $result[] = array(
                'id' => "cover",
                'title' => "cover-photos",
                'image' => url_img(get_last_user_photo($user['id'], 'profile-cover')['path'], 600),
                "userid" => $user['id']
            );
        }
    }

    foreach($albums as $album) {
        $result[] = array(
            'id' => $album['id'],
            'title' => $album['title'],
            'image' => $album['image']
        );
    }

    return json_encode($result);
}


function change_avatar_pager($app) {
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'data_one' => '',
        'id' => ''
    );

    if (input_file('avatar')) {
        $uploader = new Uploader(input_file('avatar'), 'image');
        $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/');
        if ($uploader->passed()) {
            $avatar = $uploader->resize()->toDB("profile-avatar", get_userid(), 1)->result();

            update_user_avatar($avatar, null, $uploader->insertedId, false);
            $result['status'] = 1;
            $result['data_one'] = url_img($avatar, 200);
            //$result['large'] = url_img($avatar, 920);
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function change_cover_pager($app) {
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'data_one' => ''
    );

    if (input_file('cover')) {
        $uploader = new Uploader(input_file('cover'), 'image');
        $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/cover/');
        if ($uploader->passed()) {
            $original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->toDB("profile-cover", get_userid())->result();

            $user = get_user();
            //delete the old resized cover
            if ($user['resized_cover']) {
                delete_file(path($user['resized_cover']));
            }
            //fire_hook("user.cover", null, array($original, $uploader->insertedId));
            $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/cover/resized/');
            $cover = $uploader->crop(0,  0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
            $result['data_one'] = url_img($cover);
            update_user(array('cover' => $original, 'resized_cover' => $cover));
            $result['status'] = 1;
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}