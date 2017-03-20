<?php
load_functions('forum::forum');

register_asset("forum::css/forum.css");

register_asset("forum::js/forum.js");

register_pager("admincp/forum/categories", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-categories-list',
    'use' => 'forum::admincp@categories_pager'));

register_pager("admincp/forum/category/add", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-category-add',
    'use' => 'forum::admincp@add_category_pager'));

register_pager("admincp/forum/category/edit", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-category-edit',
    'use' => 'forum::admincp@edit_category_pager'));

register_pager("admincp/forum/category/delete", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-category-delete',
    'use' => 'forum::admincp@delete_category_pager'));

register_pager("admincp/forum/tags", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-tags-list',
    'use' => 'forum::admincp@tags_pager'));

register_pager("admincp/forum/tag/add", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-tag-add',
    'use' => 'forum::admincp@add_tag_pager'));

register_pager("admincp/forum/tag/edit", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-tag-edit',
    'use' => 'forum::admincp@edit_tag_pager'));

register_pager("admincp/forum/tag/delete", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-tag-delete',
    'use' => 'forum::admincp@delete_tag_pager'));

register_pager("admincp/forum/thread/list", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-threads-list',
    'use' => 'forum::admincp@threads_pager'));

register_pager("admincp/forum/thread/edit", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-thread-edit',
    'use' => 'forum::admincp@edit_thread_pager'));

register_pager("admincp/forum/thread/delete", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-forum-thread-delete',
    'use' => 'forum::admincp@delete_thread_pager'));

register_pager("forum/thread{appends}", array(
    'as' => 'forum-thread-slug',
    'use' => 'forum::thread@thread_slug_pager'))->where(array('appends' => '.*'));

register_pager("forum/ajax", array(
    'as' => 'forum-thread-ajax',
    'use' => 'forum::ajax@ajax_pager'));

register_pager("forum/create-thread", array(
    'as' => 'forum-create-thread',
    'use' => 'forum::create_thread@create_thread_pager'));

register_pager("forum/edit-thread", array(
    'as' => 'forum-edit-thread',
    'use' => 'forum::edit_thread@modify_thread_pager'));

register_pager("forum/reply-thread", array(
    'as' => 'forum-reply-thread',
    'use' => 'forum::reply_thread@reply_thread_pager'));

register_pager("forum/edit-post", array(
    'as' => 'forum-edit-post',
    'use' => 'forum::edit_post@edit_post_pager'));

register_pager("forum{appends}", array(
    'as' => 'forum-slug',
    'use' => 'forum::forum@forum_slug_pager'))->where(array('appends' => '.*'));

register_hook("admin-started", function() {
    get_menu("admin-menu", "plugins")->addMenu(lang("forum::forum-manager"), "#", "admin-forum-manager");
    get_menu("admin-menu", "plugins")->findMenu("admin-forum-manager")->addMenu(lang("forum::categories"), url_to_pager("admin-forum-categories-list"), 'categories');
    get_menu("admin-menu", "plugins")->findMenu("admin-forum-manager")->addMenu(lang("forum::add-category"), url_to_pager("admin-forum-category-add"), 'add-category');
    get_menu("admin-menu", "plugins")->findMenu("admin-forum-manager")->addMenu(lang("forum::tags"), url_to_pager("admin-forum-tags-list"), 'tags');
    get_menu("admin-menu", "plugins")->findMenu("admin-forum-manager")->addMenu(lang("forum::add-tag"), url_to_pager("admin-forum-tag-add"), 'add-tag');
    get_menu("admin-menu", "plugins")->findMenu("admin-forum-manager")->addMenu(lang("forum::threads"), url_to_pager("admin-forum-threads-list"), 'threads');
});

register_hook("forum.reply", function($type, $type_id, $text) {
	if($type == 'forum.reply.thread'){
		$data = forum_get_post_page_info($type_id);
		$thread_id = $data['thread_id'];
		$thread_followers = forum_get_thread_followers($thread_id);
		foreach($thread_followers as $thread_follower){
			if(get_userid() != $thread_follower['follower_id']){
			$poster_id = forum_get_poster_id($data['replied_id']);
			send_notification($thread_follower['follower_id'], $type, $type_id, $data, lang('forum::forum-followed-note'), $text);
			}
		}
	}
	elseif($type == 'forum.reply.post'){
		$data = forum_get_post_page_info($type_id);
		$poster_id = forum_get_poster_id($data['replied_id']);
		if(get_userid() != $poster_id){
			send_notification($poster_id, $type, $type_id, $data, lang('forum::forum-post-note'), $text);
		}
	}
});

register_hook("forum.like", function($type, $type_id, $text) {
    if($type == 'forum.like.post'){
        $data = forum_get_post_page_info($type_id);
        $poster_id = $data['replied_id'] == 0 ? forum_get_op_id($data['thread_id']) : forum_get_poster_id($data['replied_id']);
        if(get_userid() != $poster_id){
            send_notification($poster_id, $type, $type_id, $data, lang('forum::forum-like-note'), $text);
        }
    }
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'forum.reply.thread') {
        if(isset(unserialize($notification['data'])['thread_id'])) return view("forum::notifications/thread_reply", array('notification' => $notification, 'data' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'forum.reply.post') {
        if(isset(unserialize($notification['data'])['thread_id'])) return view("forum::notifications/post_reply", array('notification' => $notification, 'data' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'forum.like.post') {
        if(isset(unserialize($notification['data'])['thread_id'])) return view("forum::notifications/post_like", array('notification' => $notification, 'data' => unserialize($notification['data'])));
    }
});

register_hook('user.delete', function($userid) {
    $obsolete_threads = db()->query("SELECT id FROM forum_threads WHERE op_id = ".$userid);
    while($obsolete_thread = $obsolete_threads->fetch_assoc()) {
        forum_delete_thread($obsolete_thread['id']);
    }
	$obsolete_replies =  db()->query("SELECT id FROM forum_replies WHERE replier_id = ".$userid);
    while($obsolete_reply = $obsolete_replies->fetch_assoc()) {
        forum_delete_reply($obsolete_reply['id']);
    }
});

register_hook('admin.statistics', function($stats) {
    $stats['forum'] = array(
        'count' => forum_num_threads(),
        'title' => lang('forum::forum'),
        'icon' => 'ion-chatboxes',
        'link' => url_to_pager('admin-forum-threads-list'),
    );
    return $stats;
});

add_available_menu('forum::forum', 'forum', 'ion-chatboxes');