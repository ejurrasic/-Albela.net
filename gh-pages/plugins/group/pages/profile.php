<?php
function group_profile_pager($app) {
    get_menu('group-profile', 'posts')->setActive();
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => get_group_details('page_title'), 'description' => get_group_details('page_desc'), 'image' => get_group_details('page_logo') ? url_img(get_group_details('page_logo'), 200) : '', 'keywords' => ''));
    return $app->render(view('group::profile/posts', array('feeds' => get_feeds('group', $app->profileGroup['group_id']))));
}

function group_profile_edit_pager($app) {
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        save_group_settings($val, $app->profileGroup['group_id']);
        redirect(group_url());
    }
    return $app->render(view('group::profile/edit', array('message' => $message)));
}

function group_profile_members_pager($app) {
    get_menu('group-profile', 'members')->setActive();
    return $app->render(view('group::profile/members', array('users' => get_group_members($app->profileGroup['group_id']))));
}

function add_member_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $uid = input('uid');
    $group = find_group($id);

    if ($group and !group_can_add_member($group)) return false;
    if (is_group_member($id, $uid)) {
        return lang('group::is-already-group-member');
    } else {
        group_add_member($id, $uid);
        //send notification to this user
        send_notification($uid, 'group.add.member', $id);
        return lang('group::member-added-successfully');
    }
}
function member_role_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $uid = input('uid');
    $value = input('v');
    $group = find_group($id);

    if ($group and !is_group_admin($group)) return false;
    if ($value == 1) {
        make_group_moderator($group, $uid);
    } else {
        remove_group_moderator($group, $uid);
    }

    return 'Member role set successfully';
}

function join_pager($app) {
    CSRFProtection::validate(false);
    $groupId = input('id');
    $status = input('status');
    $group = find_group($groupId);
    if ($status == 0) {
        //we want to join this group
        if (!can_join_group($group)) return false;
        group_add_member($groupId);
    } else {
        group_remove_member($groupId);
    }

}

function upload_cover_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    $groupId = input('id');
    $group = find_group($groupId);
    if (!$group) return json_encode($result);
    if (!is_group_admin($group)) return json_encode($result);

    if (input_file('image')) {
        $uploader = new Uploader(input_file('image'), 'image');
        $uploader->setPath($group['group_id'].'/'.date('Y').'/photos/cover/');
        if ($uploader->passed()) {
            $original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->toDB("group", $group['group_id'])->result();


            //delete the old resized cover
            if ($group['group_cover_resized']) {
                delete_file(path($group['group_cover_resized']));
            }

            //lets now crop this image for the resized cover
            $uploader->setPath($group['group_id'].'/'.date('Y').'/photos/cover/resized/');
            $cover = $uploader->crop(0,  0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
            $result['image'] = url_img($cover);
            $result['original'] = url_img($original);
            $result['id'] = $uploader->insertedId;
            update_group_details(array('group_cover' => $original, 'group_cover_resized' => $cover), $group['group_id']);
            $result['status'] = 1;
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function reposition_cover_pager($app) {
    CSRFProtection::validate(false);
    $pos = input('pos');
    $width = input('width', 623);
    $groupId = input('id');
    $group = find_group($groupId);
    if (!$group) return false;
    if (!is_group_admin($group)) return false;

    $cover = path($group['group_cover']);
    $uploader = new Uploader($cover, 'image', false , true);
    $uploader->setPath($group['group_id'].'/'.date('Y').'/photos/cover/resized/');
    $pos = abs($pos);
    $pos = ($pos / $width);
    $yCordinate = 0;
    $srcWidth = $uploader->getWidth();
    $srcHeight = $srcWidth * 0.4;
    if (!empty($pos) & $pos < $srcWidth) {
        $yCordinate = $pos  * $uploader->getWidth();
    }
    $cover = $uploader->crop(0,  $yCordinate, $srcWidth, $srcHeight)->result();

    //delete old resized image if available
    if ($group['group_cover_resized']) {
        delete_file(path($group['group_cover_resized']));
    }
    update_group_details(array('group_cover_resized' => $cover), $group['group_id']);
    return url_img($cover);
}

function remove_cover_pager($app) {
    CSRFProtection::validate(false);
    $groupId = input('id');
    $group = find_group($groupId);
    if (!$group) return false;
    if (!is_group_admin($group)) return false;
    delete_file(path($group['group_cover_resized']));

    update_group_details(array('group_cover' => '', 'group_cover_resized' => ''), $group['group_id']);
}

function change_logo_pager($app) {
    CSRFProtection::validate(false);
    $groupId = input('id');
    $group = find_group($groupId);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    if (!$group) return json_encode($result);
    if (!is_group_admin($group)) return json_encode($result);

    if (input_file('logo')) {
        $uploader = new Uploader(input_file('logo'), 'image');
        $uploader->setPath($group['group_id'].'/'.date('Y').'/photos/logo/');
        if ($uploader->passed()) {
            $image = $uploader->resize()->toDB("group-logo", $group['group_id'])->result();

            update_group_details(array('group_logo' => $image), $group['group_id']);
            fire_hook('group.logo.updated', null, array($group['group_id'], $uploader->insertedId, $image));
            $result['status'] = 1;
            $result['image'] = url_img($image, 200);
            $result['id'] = $uploader->insertedId;
            $result['large'] = url_img($image, 920);
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}