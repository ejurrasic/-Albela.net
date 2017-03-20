<?php
function tag_suggestion_pager($app) {
    CSRFProtection::validate(false);
    $term = input('term');
    $friend = input('friend', false);
    $users = search_users($term, 5, $friend);
    return view("user/tag-suggestion", array('users' => $users));
}

function saved_pager($app) {
    $app->setTitle(lang('saved'));

    $type = segment(1, 'posts');
    Pager::setCurrentPage('saved');
    add_menu('saved', array('title' => lang('posts'). ' <span style="color:lightgrey;font-size:12px">'.count(get_user_saved('feed')).'</span>', 'link' => url('saved'), 'id' => 'posts'));

    $content = '<span></span>';

    if ($type == 'posts') {
        $content = view('saved/posts', array('feeds' => get_feeds('saved')));
    }

    $content = fire_hook('saved.content', $content, array($type));
    get_menu('saved', $type)->setActive();
    return $app->render(view('saved/layout', array('content' => $content, 'type'  => $type)));
}

function save_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $type_id = input('id');
    $status = input('status');

    if ($status == 1) {
        remove_user_saving($type, $type_id);
        return json_encode(array(
            'status' => 0,
            'text' => lang('save-post'),
            'message' => lang('successfully-unsaved')
        ));
    } else {
        add_user_saving($type, $type_id);
        return json_encode(array(
            'status' => 1,
            'text' => lang('unsave-post'),
            'message' => lang('successfully-saved')
        ));
    }
}

function save_design_pager($app) {
    $val = input('val');
    if (!$val) redirect_back();
    $expected = array(
        'position' => '',
        'color' => '',
        'link' => '',
        'repeat' => ''
    );
    $val = array_merge($expected, $val);
    /**
     * @var $url
     * @var $type
     * @var $type_id
     */
    extract($val);
    if (preg_match('#_=#', $url)) {
        list($url, $hash) = explode('_=', $url);
    }
    if (!config('design-profile', true)) redirect($url);
    if ($type == 'user') {
        if ($type_id != get_userid()) redirect($url);
    }
    $file = input_file('image');
    if ($file) {
        $uploader = new Uploader($file, 'file');
        if ($uploader->passed()) {
            $uploader->setPath("design/background/");
            $image = $uploader->uploadFile()->result();
            $val['image'] = url_img($image);
        }
    }

    if ($type == 'user') {
        $details = serialize($val);
        $userid = get_userid();
        db()->query("UPDATE users SET design_details='{$details}' WHERE id='{$userid}'");
    } else {
        fire_hook('design.save', null, array($type, $type_id, $val));
    }

    redirect($url);
}

function verify_request_pager($app) {
    $type = input('type');
    $typeId = input('id');
    if (!verify_requested($type, $typeId)) {
        $time = time();
        db()->query("INSERT INTO verification_requests (type,type_id,time) VALUES('{$type}','{$typeId}','{$time}')");
    }
    redirect_back();
}