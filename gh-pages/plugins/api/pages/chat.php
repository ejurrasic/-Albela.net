<?php
function load_conversations_pager($app) {
    $chats = array();
    $conversations = get_user_conversations(100);
    foreach($conversations as $conversation) {
        $chat = array(
            'avatar' => ($conversation['type'] == 'single') ? $conversation['avatar'] : $conversation['avatars'][0],
            'cid' => $conversation['cid'],
            'title' => $conversation['title'],
            'userid' => '',
            'time' => apiTimeAgo($conversation['last_update_time']),
            'message' => str_limit($conversation['last_message'], 25),
            'unread' => $conversation['unread']
        );
        $chats[] = $chat;
    }
    return json_encode($chats);
}

function get_messages_pager($app) {
    $result = array();
    $cid = input("cid");
    $theUserid = input("theuserid");
    if (!$cid) {
        //try find there cid
        $theirCid = get_conversation_id(array($theUserid));
        $cid = ($theirCid) ? $theirCid : null;
    }
    if ($cid) {
        $messages = get_chat_messages($cid, 50);
        foreach($messages as $message) {
            $m = array(
                'id' => $message['message_id'],
                'from_me' => ($message['sender'] == get_userid()) ? true : false,
                'text' => api_format_output_text($message['message'], false,false,false, false),
                'time' => apiTimeAgo($message['time']),
                'image' => ($message['image']) ? url_img($message['image'], 920) : "",
                'avatar' => get_avatar(75, $message)
            );
           $result[] = $m;
            mark_message_read($message['message_id']);
        }

    }
    return json_encode($result);
}

function send_message_pager($app) {
    $cid = input("cid");
    $theUserid = input("theuserid");
    $text = input("text");
    $val = array(
        'user' => $theUserid,
        'cid' => $cid,
        'message' => $text
    );
    /**
     * @var $user
     * @var $cid
     * @var $message
     */
    extract($val);
    $result = array(
        'status' => 0,
        'error' => 'Failed to send message',
    );
    if(empty($cid) and !isset($user)) return json_encode($result);
    $image = null;
    $user = (!is_array($user)) ? array($user) : $user;
    $imageFile = input_file('image');
    if ($imageFile) {
        $uploader = new Uploader($imageFile);
        $path = get_userid().'/'.date('Y').'/photos/messages/';
        $uploader->setPath($path);
        if ($uploader->passed()) {
            $image = $uploader->noThumbnails()->resize()->result();
        } else {
            $result['status'] = 0;
            $result['error'] = $uploader->getError();
            return json_encode($result);
        }
    }

    if (!$message and !$image) return json_encode($result);
    $new = false;
    if (!$cid) {
        if (count($user) == 1) {
            //lets check if the user has not block each other
            if (is_blocked($user[0])) return json_encode($result);
        }
        $cid = get_conversation_id($user);
        $new = true;
    }

    if (!is_conversation_member($cid)) return json_encode($result);
    //send the message to the cid now
    $con = get_conversation($cid, false);
    if ($con['type'] == 'single') {
        $theUser = ($con['user1'] == get_userid()) ? $con['user2'] : $con['user1'];
        if (is_blocked($theUser)) return json_encode($result);
    }
    $messageId = send_chat_message($cid, $message, $image, "");
    $result['cid'] = $cid;
    $result['id'] = $messageId;
    $result['status'] = 1;
    $m = array(
        'from_me' => true,
        'text' => api_format_output_text($message, false,false,false, false),
        'time' => apiTimeAgo(time()),
        'image' => ($image) ? url_img($image, 920) : "",
        'avatar' => ""
    );
    $result = array_merge($result, $m);
    return json_encode($result);
}