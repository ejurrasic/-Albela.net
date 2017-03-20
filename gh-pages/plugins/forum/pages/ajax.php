<?php
function ajax_pager($app){
    CSRFProtection::validate(false);
	$action = input('action') ? input('action') : null;
	switch($action){
		case 'like':
			$reply_id = input('reply_id') ? input('reply_id') : null;
			if($reply_id){
				forum_like($reply_id);
			}
			return $app->view('forum::requests/like', array('reply_id' => $reply_id));
		break;
		
		case 'unlike':
			$reply_id = input('reply_id') ? input('reply_id') : null;
			if($reply_id){
				forum_unlike($reply_id);
			}
			return $app->view('forum::requests/like', array('reply_id' => $reply_id));
		break;

		case 'follow':
			$thread_id = input('thread_id') ? input('thread_id') : null;
			if($thread_id){
				forum_thread_follow($thread_id);
			}
			return $app->view('forum::requests/follow', array('thread_id' => $thread_id));
		break;

		case 'unfollow':
			$thread_id = input('thread_id') ? input('thread_id') : null;
			if($thread_id){
				forum_thread_unfollow($thread_id);
			}
			return $app->view('forum::requests/follow', array('thread_id' => $thread_id));
		break;
		
		case 'post':
			$thread_id = input('thread_id') ? input('thread_id') : null;
			$reply_id = input('id') ? input('id') : null;
			$messages = null;
			$val = input('val');
			if ($val) {
	        	CSRFProtection::validate();
				$val = array();
				$val['thread_id'] = input('thread_id');
				$val['id'] = input('id');
				$val['postbox'] = input('postbox', null, false);
				$val['type'] = input('type');
				$post_id = forum_execute_form($val);
				$page_info = forum_get_post_page_info($post_id);
				forum_view_thread($thread_id);
                if($val['id'] == 0){
                    $page = $page_info['super_page'];
                    $_GET['page'] = $page_info['super_page'];
                    $limit = config('pagination-length-thread', 20);
                    $sr = $post_id;
                    $p = $page_info['sub_page'];
                    $l = config('pagination-length-sub-replies', 4);
                    $thread = forum_get_thread($thread_id)[0];
                    $replies = forum_get_replies($thread_id, $page, $limit);
                    return $app->view('forum::requests/replies', array('thread' => $thread, 'replies' => $replies, 'page' => $page, 'sr' => $sr, 'p' => $p, 'l' => $l));
                }
                else if ($val['type'] == 'reply_thread'){
                    $page = $page_info['sub_page'];
                    $_GET['page'] = $page_info['sub_page'];
                    $limit = config('pagination-length-sub-replies', 4);
                    $sub_replies = forum_get_sub_replies($thread_id, $reply_id, $page, $limit);
                    return $app->view('forum::requests/sub_replies', array('thread_id' => $thread_id, 'reply_id' => $reply_id, 'sub_replies' => $sub_replies, 'page' => $page, 'limit' => $limit));
                }
                else if ($val['type'] == 'edit_post'){
                    return $app->view('forum::requests/post', array('post_id' => $post_id,));
                }
                else if ($val['type'] == 'delete_post'){
                    return false;
                }
			}
		break;
		
		case 'get_sub_replies':
			$thread_id = input('id') ? input('id') : null;
			$thread_id = input('thread_id') ? input('thread_id') : $thread_id;
			$reply_id = input('reply_id') ? input('reply_id') : null;
			if($thread_id && $reply_id){
				if(forum_is_reply_exist($reply_id)){
					$page = input('page') ? input('page') : 1;
					$limit = config('pagination-length-sub-replies', 4);
					$appends = $_GET;
					unset($appends['page']);
					$sub_replies = forum_get_sub_replies($thread_id, $reply_id, $page, $limit)->append($appends);
					return $app->view('forum::requests/sub_replies', array('thread_id' => $thread_id, 'reply_id' => $reply_id, 'sub_replies' => $sub_replies, 'page' => $page, 'limit' => $limit));
				}
			}
		break;
		
		default:
		break;
	}
}