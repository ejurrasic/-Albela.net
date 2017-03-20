<?php

function modify_thread_pager($app){
    $app->setTitle(lang("forum::edit-thread"));
    $val = input('val');
    $messages = null;
    $id = input('id') ? input('id') : null;
    $thread = forum_get_thread($id)[0];
    $tags = array();
    foreach(explode(' ', trim($thread['tags'])) as  $tag) {
        if(forum_get_tag($tag)){
            $tags[] = lang(forum_get_tag($tag)[0]['title']);
        }
    }
    $thread['tags'] = implode(', ', $tags);
    $categories = forum_get_categories();
    if ($val) {
		CSRFProtection::validate();
        $validate = validator($val, array(
        'subject' => 'required',
        ));
        if (validation_passes()) {
            forum_execute_form($val);
            return redirect_to_pager('forum-thread-slug', array('appends' => '/'.$val['id'].'/'.forum_slugger($val['subject'])));
        } else {
            $messages = validation_first();
        }
    }
    return $app->render(view('forum::edit_thread', array('messages' => $messages, 'id' => $id, 'thread' => $thread, 'categories' => $categories)));
}