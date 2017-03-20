<?php
function create_thread_pager($app){
    $app->setTitle(lang("forum::create-thread"));
	$messages = null;
	$page = input('page') ? input('page') : 1;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
		$val = array();
		$val['category_id'] = input('category_id');
		$val['subject'] = input('subject');
		$val['postbox'] = input('postbox', null, false);
		$val['tags'] = input('tags');
		$val['type'] = input('type');
        $post_id = forum_execute_form($val);
        return redirect_to_pager('forum-thread-slug', array('appends' => '/'.$post_id.'/'.forum_slugger($val['subject'])));
    }
	$categories = forum_get_categories();
    return $app->render(view('forum::create_thread', array('categories' => $categories, 'page' => $page, 'messages' => $messages)));
}