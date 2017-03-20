<?php
get_menu("admin-menu", "page-manager")->setActive();
app()->setTitle(lang('page::page-manager'));
load_functions("page::page");

function lists_pager($app) {
    $type = 'all';
    $term = input('term');
    if ($term) $type = 'search';
    return $app->render(view('page::lists', array('pages' => get_pages($type, $term))));
}

function edit_pager($app) {
    $page = find_page(input('id'));
    if (!$page) redirect_back();
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        update_page_details($val, $page['page_id']);

        redirect_to_pager('admin-page-lists');
    }
    return $app->render(view('page::edit', array('page' => $page)));
}

function add_category_pager($app) {
    get_menu("admin-menu", "page-manager")->findMenu("admin-page-categories")->setActive();
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        page_add_category($val);
        //redirect to category lists
        return redirect_to_pager('admin-page-categories');
    }
    return $app->render(view("page::category/add"));
}

function categories_pager($app) {
    get_menu("admin-menu", "page-manager")->findMenu("admin-page-categories")->setActive();
    return $app->render(view('page::category/lists', array('categories' => get_page_categories())));
}

function manage_category_pager($app) {
    get_menu("admin-menu", "page-manager")->findMenu("admin-page-categories")->setActive();
    $action = input('action');
    $id = input('id');
    switch($action) {
        case 'delete':
            $category = get_page_category($id);
            if (!$category) return redirect_to_pager('admin-page-categories');
            delete_page_category($id, $category);
            return redirect_to_pager('admin-page-categories');
            break;
        case 'order':
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_page_category_order($ids[$i], $i);
            }
            break;
        case 'edit':
            $category = get_page_category($id);
            if (!$category) return redirect_to_pager('admin-page-categories');

            $val = input('val');
            if ($val) {
	        	CSRFProtection::validate();
                save_page_category($val, $category);
                return redirect_to_pager('admin-page-categories');
            }
            return $app->render(view("page::category/edit", array('category' => $category)));
            break;
    }
}