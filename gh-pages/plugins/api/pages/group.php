<?php
function browse_pager($app) {
    $search = input("term");
    $type = input("type", "recommend");
    $page = input("page");
    $limit = input("limit", 10);
    $groups = get_groups($type, input('term'), $limit, 'all');
    $result = array();
    foreach($groups->results() as $group) {
        $result[] = api_arrange_group($group);
    }

    return json_encode($result, JSON_UNESCAPED_UNICODE);
}

function create_pager() {
    $val = array(
        'title' => input('title'),
        'name' => input('name'),
        'description' => input('description'),
        'privacy' => input('privacy')
    );

    $result = array(
        'status' => 0,
        'message' => ''
    );

    if ($val) {
        $rules = array(
            'title' => 'required|min:2',
            'name' => 'required|min:2|username'
        );

        $validator = validator($val, $rules);

        if (validation_passes()) {
            $groupId = group_add($val);
            $group = find_group($groupId);
            $result['status']  = 1;
            $result = array_merge($result, api_arrange_group($group));
            return json_encode($result);
        } else {
            $result['message'] = validation_first();
        }
    }

    return json_encode($result);
}

function edit_pager($app) {

}

function delete_pager($app) {
    $groupId = input('group_id');
    $group = find_group($groupId);
    delete_group($group);

    return json_encode(array('status' => 1));
}

function join_pager($app) {
    $groupId = input('group_id');
    $status = input('status');
    $group = find_group($groupId);
    $result = array(
        'status' => 0
    );
    if ($status == 0) {
        //we want to join this group
        if (!can_join_group($group)) return json_encode($result);
        group_add_member($groupId);
        $result['status'] = 1;
        return json_encode($result);
    } else {
        group_remove_member($groupId);
        $result['status'] = 1;
        return json_encode($result);
    }
}

function cover_pager($app) {
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    $groupId = input('group_id');
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
            $result['data_one'] = url_img($cover);
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

function logo_pager($app) {
    $groupId = input('group_id');
    $group = find_group($groupId);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    if (!$group) return json_encode($result);
    if (!is_group_admin($group)) return json_encode($result);

    if (input_file('image')) {
        $uploader = new Uploader(input_file('image'), 'image');
        $uploader->setPath($group['group_id'].'/'.date('Y').'/photos/logo/');
        if ($uploader->passed()) {
            $image = $uploader->resize()->toDB("group-logo", $group['group_id'])->result();

            update_group_details(array('group_logo' => $image), $group['group_id']);
            fire_hook('group.logo.updated', null, array($group['group_id'], $uploader->insertedId, $image));
            $result['status'] = 1;
            $result['data_one'] = url_img($image, 600);
            $result['id'] = $uploader->insertedId;
            $result['large'] = url_img($image, 920);
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}