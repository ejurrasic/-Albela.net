<?php

function profile_pager($app) {
    get_menu("user-profile", "timeline")->setActive();
    load_functions("feed::feed");

    return $app->render(view('profile/timeline', array('feeds' => get_feeds('timeline', app()->profileUser['id']))));
}

function profile_about_pager($app) {
    get_menu("user-profile", "about")->setActive();
    $type = input('type', 'overview');

    //register the about menus
    add_menu('user-profile-about', array('title' => lang('overview'), 'link' => profile_url('about', $app->profileUser), 'id' => 'overview'));
    foreach(get_custom_field_categories('user') as $category) {
        add_menu('user-profile-about', array('title' => lang($category['title']), 'link' => profile_url('about?id='.$category['id'].'&type=custom', $app->profileUser), 'id' => 'field-'.$category['id']));
    }

    //allow plugins to hook in
    fire_hook('user-profile-about', null, array($type, $app));
    switch($type) {
        case 'overview' :
            get_menu("user-profile-about", "overview")->setActive();
            $content = view('profile/about/overview');
            break;
        case 'custom':
            $id = input('id');
            $category = get_custom_field_category($id);
            if (!$category) return redirect(profile_url("about"));
            get_menu("user-profile-about", "field-".$id)->setActive();
            $content = view("profile/about/field", array('id' => $id));
            break;
        default:
            $content = fire_hook('user-profile-about-content', '', array($type, $app));
            break;
    }
    return $app->render(view('profile/about/layout', array('content' => $content)));
}

function profile_change_cover_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
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
            fire_hook("user.cover", null, array($original, $uploader->insertedId));
            $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/cover/resized/');
            $cover = $uploader->crop(0,  0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
            $result['image'] = url_img($cover);
            $result['original'] = url_img($original);
            $result['id'] = $uploader->insertedId;
            update_user(array('cover' => $original, 'resized_cover' => $cover));

            $result['status'] = 1;
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function profile_position_cover_pager($app) {
    CSRFProtection::validate(false);
    $pos = input('pos');
    $width = input('width', 623);
    $user = get_user();
    $cover = path($user['cover']);
    $uploader = new Uploader($cover, 'image', false , true);
    $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/cover/resized/');
    $pos = abs($pos);
    $pos = ($pos / $width);
    $yCordinate = 0;
    $srcWidth = $uploader->getWidth();
    $srcHeight = $srcWidth * 0.4;
    if (!empty($pos) & $pos < $srcWidth) {
        $yCordinate = $pos  * $uploader->getWidth();
    }
    $cover = $uploader->crop(0,  $yCordinate, $srcWidth, $srcHeight)->result();

    update_user(array('resized_cover' => $cover));
    return url_img($cover);
}

function remove_cover_pager($app) {
    CSRFProtection::validate(false);
    $user = get_user();
    if (!$user['resized_cover']) return false;
    delete_file(path($user['resized_cover']));

    return update_user(array('resized_cover' => '', 'cover' => ''));
}

function change_logo_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => '',
        'id' => ''
    );

    if (input_file('avatar')) {
        $uploader = new Uploader(input_file('avatar'), 'image');
        $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/');
        if ($uploader->passed()) {
            $avatar = $uploader->resize()->toDB("profile-avatar", get_userid(), 1)->result();

            update_user_avatar($avatar, null, $uploader->insertedId, false);
            $result['status'] = 1;
            $result['image'] = url_img($avatar, 200);
            $result['id'] = $uploader->insertedId;
            $result['large'] = url_img($avatar, 920);
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function load_preview_pager($app) {
    CSRFProtection::validate(false);
    $type  = input('type');
    $id = input('id');
    $content = fire_hook('preview.card', null, array($type, $id));
    if ($type == 'user') {
        $content = view('account/profile-card', array('user' => find_user($id)));
    }

    echo $content;
}