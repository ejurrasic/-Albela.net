<?php
function thread_pager($app){
    if (!input('id')){
        return false;
    }
    $thread_id = input('id');
    $app->setTitle(forum_get_subject($thread_id));
    $site_name = get_setting("site_title", "crea8socialPRO");
    $title = $site_name.' - '.forum_get_subject(input('id'));;
    $logo = config('site-logo');
    $logo = (!$logo) ? img("images/logo.png") : url_img($logo);
    $description = $site_name.' > Forum > Topic > '.forum_get_subject(input('id'));
    set_meta_tags(array('name' => $site_name, 'title' => $title, 'description' => $description, 'image' => $logo));
    $srp = input('srp') ? input('srp') : null;
	if($srp){
		$sr = isset(explode('-', $srp)[0]) ? forum_get_post_page_info(explode('-', $srp)[0])['replied_id'] : null;
		$p = isset(explode('-', $srp)[1]) ? explode('-', $srp)[1] : 1;
	}
	else{
		$sr = null;
		$p = 1;
	}
	$l = config('pagination-length-sub-replies', 4);
	$messages = null;
	if($thread_id && forum_is_thread_exist($thread_id)){
		if(forum_is_thread_exist($thread_id)){
			$page = input('page') ? input('page') : 1;
			$limit = config('pagination-length-thread', 20);
			$appends = $_GET;
			unset($appends['page']);
			$replies = forum_get_replies($thread_id, $page, $limit)->append($appends);
			$thread = forum_get_thread($thread_id)[0];
			$category = forum_get_category($thread['category_id'])[0];
			forum_view_thread($thread_id);
			return $app->render(view('forum::thread', array('category' => $category, 'thread' => $thread, 'replies' => $replies, 'page' => $page, 'sr' => $sr, 'p' => $p, 'l' => $l, 'messages' => $messages)));
		}
	}
}

function thread_slug_pager(){
    $_GET['id'] = segment(2);
    $_GET['page'] = isset($_GET['page']) ? $_GET['page'] : segment(4);
    $_GET['page'] = is_numeric($_GET['page']) ? $_GET['page'] : 1;
    return thread_pager(app());
}