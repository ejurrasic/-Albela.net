<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('events-manager')->setActive();

function lists_pager($app) {
    $type = input('type', 'all');
    $term = input('term', '');
    $cat = input('cat', '');
    if ($term) {
        $type = 'admin-search';
    }

    if ($cat) {
        $type = 'cat';
        $term = $cat;
    }
    $app->setTitle(lang('event::manage-events'));
    return $app->render(view('event::lists', array('events' => get_events($type, $term, 10, true))));
}

function categories_pager($app) {
    get_menu("admin-menu", "events-manager")->findMenu('admin-events-categories')->setActive();
    $app->setTitle(lang('event::manage-categories'));
    return $app->render(view('event::category/lists', array('categories' => get_event_categories())));
}


function add_category_pager($app) {
    get_menu("admin-menu", "events-manager")->findMenu('admin-events-categories')->setActive();
    $app->setTitle(lang('event::add-category'));
    $val = input('val');
    $message  = null;
    if ($val) {
		CSRFProtection::validate();

        if (!$message) {
            event_add_category($val);
            return redirect_to_pager('admin-event-categories');
        }
        //redirect to category lists
    }

    return $app->render(view('event::category/add', array('message' => $message)));
}

function manage_category_pager($app) {
    get_menu("admin-menu", "events-manager")->findMenu('admin-events-categories')->setActive();
    $action = input('action', 'order');
    $id = input('id');
    switch($action) {
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_event_category_order($ids[$i], $i);
            }
            break;
        case 'edit':
            $message = null;
            $image = null;
            $val = input('val');
            $app->setTitle(lang('event::edit-category'));
            $category = get_event_category($id);
            if (!$category) return redirect_to_pager('admin-event-categories');
            if ($val) {
		CSRFProtection::validate();

                if (!$message) {
                    save_event_category($val, $category);
                    return redirect_to_pager('admin-event-categories');
                }
                //redirect to category lists
            }
            return $app->render(view('event::category/edit', array('message' => $message, 'category' => $category)));
            break;
        case 'delete':
            $category = get_event_category($id);
            if (!$category) return redirect_to_pager('admin-event-categories');
            delete_event_category($id, $category);
            return redirect_to_pager('admin-event-categories');
            break;
    }
    return $app->render();
}
 