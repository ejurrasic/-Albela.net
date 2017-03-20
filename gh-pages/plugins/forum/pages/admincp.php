<?php
//get_menu("admin-menu", "forum-manager")->setActive();
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("admin-forum-manager")->setActive();


function categories_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-categories")->setActive();
    $app->setTitle(lang("forum::categories"));
    return $app->render(view('forum::admincp/category/list', array('categories' => forum_get_categories())));
}

function add_category_pager($app){
    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-categories")->setActive();
//    $app->setTitle(lang("forum::add-category"));
    $val = input('val');
    $messages = null;
    if ($val) {
		CSRFProtection::validate();
		forum_execute_form($val);
        return redirect_to_pager('admin-forum-categories-list');
    }
    return $app->render(view('forum::admincp/category/add', array('messages' => $messages)));
}

function edit_category_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-categories")->setActive();
    $app->setTitle(lang("forum::edit-category"));
    $id = input('id');
    $category_id = $id ? $id : 0;
    $val = input('val');
    $messages = null;
    if ($val) {
		CSRFProtection::validate();
		forum_execute_form($val);
		return redirect_to_pager('admin-forum-categories-list');
    }
    return $app->render(view('forum::admincp/category/edit', array('messages' => $messages, 'category_id' => $category_id, 'category' => forum_get_category($category_id)[0])));
}

function delete_category_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-categories")->setActive();
    $app->setTitle(lang("forum::delete-category"));
    $val = input('val');
    $id = input('id');
    $messages = null;
    $category_id = $id ? $id : 0;
	$category = forum_get_category($category_id)[0];
	$categories = forum_get_categories();
    if(!forum_is_category_exist($category_id)){
	$messages = lang(category-delete-not-exist);
    }
    if ($val) {
		CSRFProtection::validate();
        if(forum_is_category_exist($category_id)){
            forum_execute_form($val);
            return redirect_to_pager('admin-forum-categories-list');
        }
		else {
            $messages = 'The category you want to delete does not exist';
        }
    }
    return $app->render(view('forum::admincp/category/delete', array('messages' => $messages, 'category_id' => $category_id, 'category' => $category, 'categories' => $categories)));
}

function tags_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-tags")->setActive();
    $app->setTitle(lang("forum::tags"));
    return $app->render(view('forum::admincp/tag/list', array('tags' => forum_get_tags())));
}

function add_tag_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-tags")->setActive();
    $app->setTitle(lang("forum::add-tag"));
    $val = input('val');
    $messages = null;
    if ($val) {
		CSRFProtection::validate();
        $validate = validator($val, array(
            'color' => 'required|unique:forum_tags'
        ));
        if (validation_passes()) {
            forum_execute_form($val);
            return redirect_to_pager('admin-forum-tags-list');
        }
		else {
            $messages = validation_first();
        }
    }
    return $app->render(view('forum::admincp/tag/add', array('messages' => $messages)));
}

function edit_tag_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-tags")->setActive();
    $app->setTitle(lang("forum::edit-tag"));
    $id = input('id');
    $tag_id = $id ? $id : 0;
    $val = input('val');
    $messages = null;
    if ($val) {
		CSRFProtection::validate();
        if($val['color'] != forum_get_tag($tag_id)[0]['color']){
            $validate = validator($val, array(
                'color' => 'required|unique:forum_tags'
            ));
        }
        if (validation_passes()) {
            forum_execute_form($val);
            return redirect_to_pager('admin-forum-tags-list');
        }
		else {
            $messages = validation_first();
        }
    }
    return $app->render(view('forum::admincp/tag/edit', array('messages' => $messages, 'tag_id' => $tag_id, 'tag' => forum_get_tag($tag_id)[0])));
}

function delete_tag_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-tags")->setActive();
    $app->setTitle(lang("forum::delete-tag"));
    $tag_id = input('id') ? input('id') : null;
    $val = array('tag_id' => $tag_id, 'type' => 'delete_tag');
    if(forum_is_tag_exist($tag_id)){
        forum_execute_form($val);
    }
    return redirect_to_pager('admin-forum-tags-list');
}

function threads_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-threads")->setActive();
    $app->setTitle(lang("forum::manage-threads"));
    $category_id = input('c') ? input('c') : null;
    $tag_id = input('t') ? input('t') : null;
    $categories = forum_get_categories();
    $order = input('o') ? input('o') : null;
    $search = input('s') ? input('s') : null;
    $page = input('page') ? input('page') : null;
    $limit = 20;
    $category = $category_id ? forum_get_category($category_id)[0]['title'] : lang('forum::all-categories');
    $threads = forum_get_threads($category_id, $tag_id, $search, $order, $page, $limit);
    $tag = $tag_id ? forum_get_tag($tag_id)[0]['title'] : lang('forum::all-tags');
    $tags = forum_get_tags();
    $message = '';
    $url = (isset(parse_url($_SERVER['REQUEST_URI'])['query'])) ? url_to_pager("admin-forum-threads-list").'?'.parse_url($_SERVER['REQUEST_URI'])['query'] : url_to_pager("admin-forum-threads-list").'?';
    return $app->render(view('forum::admincp/thread/list', array('categories' => $categories, 'tags' => $tags, 'category' => $category, 'tag' => $tag, 'url' => $url, 'threads' => $threads, 'message' => $message)));
}

function edit_thread_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-threads")->setActive();
    $app->setTitle(lang("forum::edit-thread"));
    $val = input('val');
    $messages = null;
    $id = input('id');
    $thread_id = $id ? $id : 0;
    $thread = forum_get_thread($thread_id)[0];
    $category_id = forum_get_thread($thread_id)[0]['category_id'];
    $categories = forum_get_categories();
    $tags = array();
    foreach(explode(' ', trim($thread['tags'])) as  $tag) {
        if(forum_get_tag($tag)){
            $tags[] = lang(forum_get_tag($tag)[0]['title']);
        }
    }
    $thread['tags'] = implode(', ', $tags);
    if ($val) {
		CSRFProtection::validate();
        $validate = validator($val, array(
			'subject' => 'required',
		));
        if (validation_passes()) {
            forum_execute_form($val);
            return redirect_to_pager('admin-forum-threads-list');
        } else {
            $messages = validation_first();
        }
    }
    return $app->render(view('forum::admincp/thread/edit', array('messages' => $messages, 'thread_id' => $thread_id, 'thread' => $thread, 'category_id' => $category_id, 'categories' => $categories)));
}

function delete_thread_pager($app){
//    get_menu("admin-menu", "forum-manager")->findMenu("admin-forum-manager")->setActive();
    $app->setTitle(lang("forum::delete-thread"));
    $thread_id = input('id') ? input('id') : null;
    $val = array('thread_id' => $thread_id, 'type' => 'delete_thread');
    if(forum_is_thread_exist($thread_id)){
        forum_execute_form($val);
    }
    return redirect_to_pager('admin-forum-threads-list');
}