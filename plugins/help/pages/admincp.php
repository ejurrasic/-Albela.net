<?php
get_menu('admin-menu', 'admin-help')->setActive();

function lists_pager($app) {
    $app->setTitle(lang('help::manage-help'));
    $term = input('term');
    $helps = get_helps($term);
    return $app->render(view('help::lists', array('helps' => $helps)));
}

function categories_pager($app) {
    $app->setTitle(lang('help::manage-help'));
    return $app->render(view('help::categories', array('categories' => get_help_categories())));
}

function manage_pager($app) {
    $action = input('action', 'order');
    $app->setTitle(lang('help::manage-help'));
    switch($action) {
        case 'delete':
            $id = input('id');
            delete_help($id);
            return redirect_back();
            break;
        case 'edit':
            $id = input('id');
            $help = get_help($id);
            if (!$help) return redirect_back();
            $val = input('val', null, array('content'));
            if ($val) {
		        CSRFProtection::validate();
                save_help($val, $id);
                return (input('val.category')) ? redirect_to_pager('admincp-helps') : redirect_to_pager('admincp-help-category');
            }
            return $app->render(view('help::edit', array('help' => $help)));
            break;
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_help_category_order($ids[$i], $i);
            }
            break;
    }
}

function add_pager($app) {
    $category = input('category', false);
    $app->setTitle(lang('help::manage-help'));
    $message = null;
    $val = input('val', null, array('content'));
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'title' => 'required'
        ));

        if (validation_passes()) {
            $slug = toAscii(input('val.title'));
            if (empty($slug)) $slug = md5(time());
            if (help_slug_exists($slug, input('val.category', 0))) {
                $message = lang('help::help-exists-already');
            } else {
                //we are good to go
                $val['slug'] = $slug;
                add_help($val);
                return (input('val.category')) ? redirect_to_pager('admincp-helps') : redirect_to_pager('admincp-help-category');
            }
        } else {
            $message = validation_first();
        }
    }
    return $app->render(view('help::add', array('category' => $category, 'message' => $message)));
}