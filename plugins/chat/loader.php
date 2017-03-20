<?php
load_functions('chat::chat');
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset("chat::css/chat.css");
        register_asset("chat::js/chat.js");
    }
});

register_hook("user.profile.buttons", function($user) {
   //echo view('chat::button', array('user' => $user));
});

register_hook("ajax.push.result", function($pushes) {
    $count = count_unread_messages();
    $pushes['types']['unread'] = $count;
    $users = chat_get_onlines();
    $countOnline = count($users);
    $users = array_merge($users, get_few_offlines());
    $pushes['types']['onlines'] = view('chat::load-onlines', array('users' => $users));
    $pushes['types']['count-onlines'] = $countOnline;
    $pushes['types']['chat-opened'] = get_cache('user-chat-opened-'.get_userid(), array());
    $cacheName = "typing-".get_userid();
    $result = array();
    if (cache_exists($cacheName)) $result = get_cache($cacheName);
    $pushes['types']['chat-typing'] = array('now' => time(), 'cid' => $result, 'typing' => lang('chat::typing'), 'img' => img('images/typing.gif'));

    $userid = get_userid();
    $cacheName = "message-waiting-{$userid}";
    $result= array();
    if (cache_exists($cacheName)) $result = get_cache($cacheName);
    $seen = array();

    if (is_array($result)) {
        foreach($result as $cid => $detail) {

            if (has_read_message($detail[0], $detail[1]) and !isset($detail[2])) {
                $seen[$cid] = $detail[0];
                $result[$cid][2] = 'seen';
            }
        }
    }
    set_cacheForever($cacheName, $result);
    $pushes['types']['chat-seen'] = $seen;
    return $pushes;
});

register_hook("system.started", function($app) {
    if (is_loggedIn()) {
        update_user(array(
            'online_time' => time()
        ));
    }
});

register_hook('footer', function() {
   if(is_loggedIn() and !isMobile()) echo view('chat::footer');
});

register_hook("privacy-settings", function() {
    echo view('chat::privacy');
});
register_pager("messages", array('as' => 'messages', 'use' => 'chat::message@messages_pager', 'filter' => 'auth'));
register_pager("chat/send", array('as' => 'chat-send', 'use' => 'chat::message@chat_send_pager', 'filter' => 'auth'));
register_pager("chat/load/messages", array('use' => 'chat::message@chat_load_messages_pager', 'filter' => 'auth'));
register_pager("chat/load/groups", array('use' => 'chat::message@chat_load_groups_pager', 'filter' => 'auth'));
register_pager("chat/preload", array('use' => 'chat::message@chat_preload_pager', 'filter' => 'auth'));
register_pager("chat/typing", array('use' => 'chat::message@chat_typing_pager', 'filter' => 'auth'));
register_pager("chat/remove/typing", array('use' => 'chat::message@chat_remove_typing_pager', 'filter' => 'auth'));
register_pager("chat/mark/read", array('use' => 'chat::message@chat_mark_read_pager', 'filter' => 'auth'));
register_pager("chat/load/dropdown", array('use' => 'chat::message@chat_load_dropdown_pager', 'filter' => 'auth'));
register_pager("chat/register/open", array('use' => 'chat::message@chat_register_opened_pager', 'filter' => 'auth'));
register_pager("chat/get/conversations", array('use' => 'chat::message@chat_get_conversations_pager', 'filter' => 'auth'));
register_pager("chat/set/status", array('use' => 'chat::message@chat_set_status_pager', 'filter' => 'auth'));
register_pager("chat/paginate", array('use' => 'chat::message@chat_paginate_pager', 'filter' => 'auth'));
register_pager("chat/delete/conversation", array('use' => 'chat::message@chat_delete_conversation_pager', 'filter' => 'auth'));
register_pager("chat/delete/message", array('use' => 'chat::message@chat_delete_message_pager', 'filter' => 'auth'));
register_pager("chat/update/send/privacy", array('use' => 'chat::message@chat_update_send_privacy_pager', 'filter' => 'auth'));
register_pager("mobile/online", array('as' => 'mobile-online', 'use' => 'chat::message@mobile_online_pager', 'filter' => 'auth'));


register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM conversation_messages WHERE sender='{$userid}'");
    db()->query("DELETE FROM conversation_members WHERE user_id='{$userid}'");
});