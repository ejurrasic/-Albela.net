<?php
function create_group_pager($app) {
    $message = null;
    $val = input('val');
    $app->setTitle(lang('group::create-group'));

    if ($val) {
		CSRFProtection::validate();
        $title = input('val.title');
        $slug = toAscii($title);
        if (empty($slug)) $slug = md5(time());

        $val['name'] = $slug;
        $rules = array(
            'title' => 'required|min:2',
            'name' => 'required|min:2|username'
        );

        $fieldRules = array();
        foreach(get_form_custom_fields('page') as $field) {
            if ($field['required']) {
                $fieldRules[$field['title']] = 'required';
            }
        }

        $validator = validator($val, $rules);

        if (validation_passes()) {
            $groupId = group_add($val);
            $group = find_group($groupId);
            return redirect(group_url(null, $group));
        } else {
            $message = validation_first();
        }
    }
    return group_render(view('group::create', array('message' => $message)), 'create', true);
}

function manage_group_pager($app) {
    $app->setTitle(lang('group::groups'));
    $type = input('type', 'recommend');
    $app->groupType = $type;
    $_SESSION['group_list_type'] = isset($_SESSION['group_list_type']) ? $_SESSION['group_list_type'] : config('default-group-list-type', 'list');
    $list_type = $_SESSION['group_list_type'];
    return group_render(view('group::lists', array('groups' => get_groups($type, input('term'), 10, input('filter')), 'type' => $type, 'list_type' => $list_type)), 'manage');
}

function group_delete_pager($app) {
    $groupId = segment(2);
    $group = find_group($groupId);

    if (!is_group_admin($group)) return redirect_to_pager('group-manage');


    delete_group($group);

    return (input('admin', false)) ? redirect_back() : redirect_to_pager('group-manage');
}
/**
 * Help function to render page with its layout
 */
function group_render($content, $type = "all", $fullWidth = false) {

    return app()->render(view("group::layout", array('content' => $content, 'type' => $type, 'fullWidth' => $fullWidth)));
}


function admin_group_pager($app) {
    $app->setTitle(lang('group::groups-manager'));
    get_menu('admin-menu', 'plugins')->setActive();
    return $app->render(view("group::lists", array("groups" => get_all_groups())));
}

function group_admin_edit_pager($app) {
    $app->setTitle(lang('group::groups-manager'));
    get_menu('admin-menu', 'plugins')->setActive();
    $group = find_group(segment(3));
    if (!$group) redirect_back();
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        save_group_settings($val, $group['group_id']);
        redirect(url('admincp/groups'));
    }
    return $app->render(view("group::edit", array("group" => $group)));
}
 