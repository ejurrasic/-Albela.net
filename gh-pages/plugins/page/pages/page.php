<?php
load_functions("page::page");
function create_page_pager($app) {
    $app->setTitle(lang('page::create-a-page'));
    $message = null;
    $val = input('val');
    if(!user_has_permission('can-create-page')) redirect_to_pager('page-all');
    if ($val) {
		CSRFProtection::validate();
        $title = input('val.name');
        $slug = toAscii($title);
        if (empty($slug)) $slug = md5(time());
        $val['page_url'] = $slug;
        $rules = array(
            'name' => 'required|min:2',
            'category' => 'required',
            'page_url' => 'required|min:2|username'
        );

        $fieldRules = array();
        foreach(get_form_custom_fields('page') as $field) {
            if ($field['required']) {
                $fieldRules[$field['title']] = 'required';
            }
        }

        $validator = validator($val, $rules);
        if ($fieldRules) $validator = validator(input('val.fields'), $fieldRules);

        if (validation_passes()) {
            $pageId = page_add($val);
            $page = find_page($pageId);
            return redirect(page_url(null, $page));
        } else {
            $message = validation_first();
        }
    }
    return page_render(view('page::create', array('message' => $message)), 'create', true);
}

function my_pages_pager($app) {
    $app->setTitle(lang('page::my-pages'));
    return page_render(view('page::mine', array('pages' => get_pages('mine'))), 'mine');
}

function pages_pager($app) {
    $app->setTitle(lang('page::pages'));
    $type = input('type', 'browse');
    $term = input('term');
    $category = input('category', 'all');
    $filter = input('filter', 'all');
    $app->pageType = $type;
    $_SESSION['page_list_type'] = isset($_SESSION['page_list_type']) ? $_SESSION['page_list_type'] : config('default-page-list-type', 'list');
    $list_type = $_SESSION['page_list_type'];
    return page_render(view('page::mine', array('pages' => get_pages($type, $term, 10, $category, $filter), 'list_type' => $list_type)));
}

function page_more_invite_pager() {
    CSRFProtection::validate(false);
    $offset = input('offset');
    $pageId = input('id');
    $limit = 20;
    $newOffset = $offset + $limit;
    $users = '';
    foreach(get_invite_friends(null, $limit, $newOffset) as $user) {
        $users .= view('page::block/invite/display', array('user' => $user, 'pageId' => $pageId));
    }

    return json_encode(array(
        'offset' => $newOffset,
        'users' => $users
    ));
}

function page_invite_pager() {
    CSRFProtection::validate(false);
    $user = input('user');
    $page = input('page');
    if ($user != get_userid() and !has_liked('page', $page, 1, $user) and !has_page_invited($page, $user)) {
        send_notification($user, 'page.invite', $page);
        add_page_invite($page, $user);
    }
}

function page_search_invite_pager() {
    CSRFProtection::validate(false);
    $page = input('page');
    $term = input('term');

    $users = '';
    foreach(get_invite_friends($term) as $user) {
        $users .= view('page::block/invite/display', array('user' => $user, 'pageId' => $page));
    }

    return $users;
}

function delete_page_pager($app) {
    $id = input('id');
    delete_page($id);
    if (input('admin')) redirect_back();
    redirect_to_pager('page-mine');
}

/**
 * Help function to render page with its layout
 */
function page_render($content, $type = "all", $fullWidth = false) {

    return app()->render(view("page::layout", array('content' => $content, 'type' => $type, 'fullWidth' => $fullWidth)));
}

