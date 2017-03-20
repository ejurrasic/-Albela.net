<?php
function reply_thread_pager($app){
    $app->setTitle(lang("forum::reply-thread"));
	$messages = null;
	$thread_id = input('thread_id');
	$replied_id = input('id');
	$message = null;
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
        return redirect_to_pager('forum-thread-slug', array('appends' => '/'.$page_info['thread_id'].'/'.forum_slugger(forum_get_subject($page_info['thread_id'])).'?page='.$page_info['super_page'].'&srp='.$post_id.'-'.$page_info['sub_page'].'#forum-reply-wrapper-'.$post_id));
	}
    return $app->render(view('forum::reply_thread', array('thread_id' => $thread_id, 'id' => $replied_id, 'message' => $message)));
}