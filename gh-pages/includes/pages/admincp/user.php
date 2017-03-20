<?php
function members_pager($app) {
    get_menu("admin-menu", "admin-users")->setActive();
    $app->setTitle(lang('manage-members'));
    $filter = input('filter', 'active');
    $users = get_users($filter, 20, input('term'));
    return $app->render(view('user/lists', array('users' => $users, 'filter' => $filter)));
}

function posts_pager($app) {
    $app->setTitle(lang('manage-posts'));
    get_menu("admin-menu", "cms")->setActive();
    return $app->render(view('posts/list', array('feeds' => get_all_feeds())));
}

function user_action_pager($app) {
    get_menu("admin-menu", "admin-users")->setActive();
    $app->setTitle(lang('manage-members'));
    $type = input('type', 'edit');

    switch($type) {
        case 'edit':
            $user = find_user(input('id'), true);
            if (!$user) return redirect(url_to_pager('admin-members-list'));
            $val = input('val');
            if ($val) {
		        CSRFProtection::validate();
                /**
                 * @var $password
                 */
                extract($val);
                if(isset($val['password'])) {
                    $password = ($password) ? hash_make($password) : $user['password'];
                    $val['password'] = $password;
                }
                update_user($val, $user['id'], false, true);

                redirect(url_to_pager('admin-members-list'));
            }
            return $app->render(view('user/edit-user', array('user' => $user)));
            break;
        case 'delete':
            delete_user(input('id'));
            redirect(url_to_pager('admin-members-list'));
            break;
    }
}

function custom_fields_pager($app) {
    $type = input('type', 'user');
    $action = input('action', 'list');
    $message = "";

    get_menu('admin-menu', 'admin-custom-field')->setActive();
    //get_menu("admin-menu", "admin-users")->setActive();
    if ($type == 'user') {
        //get_menu('admin-menu', 'admin-custom-field')->findMenu('user-custom-fields')->setActive();
    }

    fire_hook("admincp.custom-field", null, array($type));

    switch($action) {
        case 'add':
            $val = input('val');
            $app->setTitle(lang('add-new-custom-field'));
            if ($val) {
		        CSRFProtection::validate();
                $added = add_custom_field($type, $val);
                if ($added) redirect(url("admincp/custom-fields/?type=".$type));
                $message = "Failed to add custom field maybe it already exists";
            }
            $content = view("custom-fields/add", array('type' => $type, "message" => $message));
            break;
        case 'edit':
            $id = input('id');
            $app->setTitle(lang('edit-custom-field'));
            $field = get_custom_field($id);
            if (!$field) redirect_to_pager("admin-user-custom-fields");

            $val = input('val');
            if ($val) {
		        CSRFProtection::validate();
                $save = add_custom_field($type, $val, true, $id);
                if ($save) redirect(url("admincp/custom-fields/?type=".$type));
            }

            $content = view("custom-fields/edit", array('field' => $field, 'type' => 'user'));
            break;
        case 'delete':
            $id = input('id');
            delete_custom_field($id);
            redirect(url("admincp/custom-fields/?type=".$type));
            break;
        case 'order':
            CSRFProtection::validate(false);
            $ids = input('data');
            $category = input('category');
            for($i = 0; $i < count($ids); $i++) {
                update_custom_field_order($category, $ids[$i], $i);
            }
            exit;
            break;
        default:
            $app->setTitle(lang('custom-fields'));
            $content = view("custom-fields/list", array('type' => $type));
            break;
    }
    return $app->render($content);
}

function custom_fields_category_pager($app) {
    $action = input("action", "list");
    $type = input("type", "user");
    $message = null;

    get_menu('admin-menu', 'admin-custom-field')->setActive();
    //get_menu("admin-menu", "admin-users")->setActive();
    if ($type == 'user') {
        get_menu('admin-menu', 'admin-custom-field')->findMenu('users-custom-fields')->setActive();
    }
    fire_hook("admincp.custom-field", null, array($type));

    switch($action) {
        default:
            $app->setTitle(lang('custom-fields-categories'));
            $categories = get_custom_field_categories($type);
            $content = view("custom-fields/categories", array('categories' => $categories, 'type' => $type));
            break;
        case "edit":
            $app->setTitle(lang('edit-custom-field-category'));
            $category = get_custom_field_category(input("id"));
            if (!$category) return redirect(url_to_pager("admin-custom-fields-category")."?type=".$type);
            $title = input("title");
            if ($val = input('val')) {
                save_custom_field_category(input("id"), $val);
                return redirect(url_to_pager("admin-custom-fields-category")."?type=".$type);
            }
            $content = view("custom-fields/edit-category", array("category" => $category));
            break;
        case "delete":
            delete_custom_field_category(input("id"));
            return redirect(url_to_pager("admin-custom-fields-category")."?type=".$type);
            break;
        case "add":
            $app->setTitle(lang('add-custom-field-category'));
            $val = input("val");
            if ($val) {
		        CSRFProtection::validate();
                $added = add_custom_field_category($val, $type);
                if ($added) return redirect(url_to_pager("admin-custom-fields-category")."?type=".$type);
                $message = "Failed to add custom field category, try again..";
            }
            $content = view("custom-fields/add-category", array("message" => $message, 'type' => $type));
            break;
    }
    return $app->render($content);
}

function roles_pager($app) {
    get_menu("admin-menu", "admin-users")->setActive();
    $app->setTitle(lang('user-roles'));
    $action = input('action', 'lists');

    switch($action) {
        default:
            $val = input('val');
            $message = "";
            $errorMessage = "";
            if ($val) {
		        CSRFProtection::validate();
                $add = add_user_role($val);
                if ($add) {
                    $message = lang('successfully-done');
                } else {
                    $errorMessage = lang('user-role-error');
                }
            }
            $content = view("user/roles", array('message' => $message, 'errorMessage' => $errorMessage));
            break;
        case 'edit':
            $role = get_user_role(input('id'));
            if (!$role or !$role['can_edit']) return redirect_to_pager('admin-user-roles');
            $val = input('val');
            if ($val) {
                CSRFProtection::validate();
                save_user_role($val, $role);
                return redirect_to_pager('admin-user-roles');
            }
            $content = view("user/edit-role", array('dbrole' => $role));
            break;
        case 'delete' :
            $role = get_user_role(input('id'));
            if (!$role or !$role['can_delete']) return redirect_to_pager('admin-user-roles');
            delete_user_role($role);
            return redirect_to_pager('admin-user-roles');
            break;
    }
    return $app->render($content);
}


function verify_requests_pager($app) {
    $app->setTitle(lang('verification-requests'));

    return $app->render(view('user/requests', array('requests' => get_verification_requests())));
}

function verify_requests_action_pager($app) {
    $id = input('id');
    $type = input('type');
    $query = db()->query("SELECT * FROM verification_requests WHERE id='{$id}' ");
    if ($query->num_rows > 0) {
        $request = $query->fetch_assoc();
        $typeId = $request['type_id'];
        if ($type == 'ignore') {
            db()->query("UPDATE verification_requests SET ignored='1' WHERE id='{$id}'");
        } else {
            db()->query("DELETE FROM verification_requests WHERE id='{$id}'");
            if ($request['type'] == 'user') {
                db()->query("UPDATE users SET verified='1' WHERE id='{$typeId}'");
            } else {
                db()->query("UPDATE pages SET verified='1' WHERE page_id='{$typeId}'");
            }
        }
    }

    return redirect_back();
}