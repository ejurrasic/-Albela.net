<?php
load_functions("api::api");
add_menu_location('app-mobile-menu', "App Mobile Menu");
register_hook("system.started", function($app) {
    $webView = input("webview", false);
    if ($webView) {
        $app->onHeader = true;
        $app->onHeaderContent = true;
        $app->hideFooterContent  = true;
        $app->isMobile = true;
        $userid = input('api_userid');
        //auto login the user
        app()->userid = $userid;
        api_temporary_login_user($userid);
        register_hook("before-render-js", function($html) {
            $html .= "<script>var loadAjax = false;</script>";
            return $html;
        });

        register_hook("after-render-css", function($html) {
            $html .= "<style>
                #main-wrapper{
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }
                #header{display: none}
                #explore-menu{display:none}
                #footer{display:none}
                .site-reset{display:none}
                #cc-notification{display:none !important}</style>";
            return $html;
        });
    }
});
register_filter("api", function() {
    $key = segment(1);
    if (strtolower($key) != strtolower(config("api-key", "normalkey"))) return false;
    $userid = input('userid');
    if ($userid) {
        //auto login the user
        app()->userid = $userid;
        api_temporary_login_user($userid);
        $time = time();
        db()->query("UPDATE users SET last_active_time='{$time}',online_time='{$time}' WHERE id='{$userid}'");
    }
    return true;
});

register_hook("incomming-call", function($userid, $connectionId, $enableVideo) {
    $user = find_user($userid);
    if ($user['gcm_token']) {
        $message = json_encode(array(
            'type' => 'new-call',
            'userid' => get_userid(),
            'connection_id' => $connectionId,
            'enable_video' => ($enableVideo) ? true : false
        ));

        $msg = array
        (
            'message' 	=> $message
        );

        //Creating a new array fileds and adding the msg array and registration token array here
        $fields = array
        (
            'registration_ids' 	=> array($user['gcm_token']),
            'data'			=> $msg
        );

        $headers = array
        (
            'Authorization: key=' .config("google-fcm-api-key"),
            'Content-Type: application/json'
        );

        //Using curl to perform http request
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );

        //Getting the result
        $result = curl_exec($ch );
        curl_close( $ch );
        //print_r($result);
        $res = json_decode($result);
    }

});
register_hook("ajax.push.notification", function($userid) {
    $user = find_user($userid);
    if (isset($user['gcm_token']) and $user['gcm_token']) {
        //send notification
        $message = json_encode(array(
            'type' => 'new-event'
        ));
        //exit($user['gcm_token']);
        $msg = array
        (
             'message' 	=> $message
        );

        //Creating a new array fileds and adding the msg array and registration token array here
        $fields = array
        (
            'registration_ids' 	=> array($user['gcm_token']),
            'data'			=> $msg
        );

        $headers = array
        (
            'Authorization: key=' .config("google-fcm-api-key"),
            'Content-Type: application/json'
        );

        //Using curl to perform http request
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );

        //Getting the result
        $result = curl_exec($ch );
        curl_close( $ch );
        //print_r($result);
        $res = json_decode($result);
        //exit($res);
    }

});

register_hook("admin-started", function() {
    get_menu("admin-menu", "admin-users")->addMenu(lang("Send Push Notification"), url("admincp/push/notification"), 'push-notification');
});

register_pager("api/{key}/login", array('use' => "api::api@login_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/set/fcm/token", array('use' => "api::api@set_fcm_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/get/menu", array('use' => "api::api@get_menu_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/register/social", array('use' => "api::api@social_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/check/login", array('use' => "api::api@check_login_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/check/events", array('use' => "api::api@check_event_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/send/notifications", array('use' => "api::api@send_notification_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/settings/save", array('use' => "api::api@settings_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/settings/password", array('use' => "api::api@settings_password_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/signup", array('use' => "api::api@signup_user", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/feeds", array('use' => "api::api@get_feeds_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/react/item", array('use' => "api::like@react_item_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/react/load", array('use' => "api::like@react_load_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/like/item", array('use' => "api::like@like_item_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/dislike/item", array('use' => "api::like@dislike_item_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/comment/load", array('use' => "api::comment@comment_load_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/comment/add", array('use' => "api::comment@comment_add_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/comment/remove", array('use' => "api::comment@comment_remove_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));


register_pager("api/{key}/getstarted/upload", array('use' => "api::getstarted@upload_avatar_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/getstarted/suggestion", array('use' => "api::getstarted@suggestion_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/feed/add", array('use' => "api::feed@add_feed_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/feed/submit/poll", array('use' => "api::feed@submit_poll_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/feed/action", array('use' => "api::feed@action_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/notifications", array('use' => "api::notification@list_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/notifications/unread", array('use' => "api::notification@unread_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/notification/delete", array('use' => "api::notification@delete_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/friend/requests", array('use' => "api::relationship@list_requests_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/friend/add", array('use' => "api::relationship@add_friend_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/friend/follow", array('use' => "api::relationship@process_follow_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/friend/remove", array('use' => "api::relationship@remove_friend_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/friend/suggestions", array('use' => "api::relationship@friend_suggestion_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/friend/online", array('use' => "api::relationship@friend_online_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/friend/confirm", array('use' => "api::relationship@confirm_friend_request_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/profile/details", array('use' => "api::profile@load_details_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/profile/change/avatar", array('use' => "api::profile@change_avatar_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/profile/change/cover", array('use' => "api::profile@change_cover_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/profile/friends", array('use' => "api::profile@friends_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/profile/photos", array('use' => "api::profile@photos_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/profile/albums", array('use' => "api::profile@albums_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/photo/album/add", array('use' => "api::photo@albums_create_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/photo/album/photos", array('use' => "api::photo@albums_photos_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/photo/album/details", array('use' => "api::photo@albums_details_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/photo/album/upload", array('use' => "api::photo@albums_upload_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/photo/album/edit", array('use' => "api::photo@albums_edit_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/photo/album/delete", array('use' => "api::photo@albums_delete_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/photo/details", array('use' => "api::photo@photo_details_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

//chat system
register_pager("api/{key}/chat/conversations", array('use' => "api::chat@load_conversations_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/chat/get/messages", array('use' => "api::chat@get_messages_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/chat/send/message", array('use' => "api::chat@send_message_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/hashtag/get", array('use' => "api::hashtag@find_hashtags_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

//music plugin
register_pager("api/{key}/music/browse", array('use' => "api::music@browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/music/get/categories", array('use' => "api::music@get_categories_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/music/create", array('use' => "api::music@music_create_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/music/edit", array('use' => "api::music@music_edit_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/music/delete", array('use' => "api::music@music_delete_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/music/page", array('use' => "api::music@music_page_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
//music playlists
//register_pager("api/{key}/music/playlist/browse", array('use' => "api::music@playlist_browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
//register_pager("api/{key}/music/playlist/browse", array('use' => "api::music@playlist_browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/blog/browse", array('use' => "api::blog@browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/blog/view", array('use' => "api::blog@view_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));


//marketplace
register_pager("api/{key}/marketplace/browse", array('use' => "api::marketplace@browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/marketplace/get/categories", array('use' => "api::marketplace@get_categories_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/marketplace/create", array('use' => "api::marketplace@marketplace_create_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/marketplace/edit", array('use' => "api::marketplace@marketplace_edit_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/marketplace/delete", array('use' => "api::marketplace@marketplace_delete_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/marketplace/page", array('use' => "api::marketplace@marketplace_page_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

//videos
register_pager("api/{key}/videos/browse", array('use' => "api::video@browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/videos/get/categories", array('use' => "api::video@get_categories_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/video/create", array('use' => "api::video@create_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/video/edit", array('use' => "api::video@edit_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/video/delete", array('use' => "api::video@delete_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/event/browse", array('use' => "api::event@browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/event/birthdays", array('use' => "api::event@birthdays_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/event/get/categories", array('use' => "api::event@get_categories_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/event/create", array('use' => "api::event@create_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/event/edit", array('use' => "api::event@edit_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/event/delete", array('use' => "api::event@delete_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/event/rsvp", array('use' => "api::event@rsvp_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/event/cover", array('use' => "api::event@cover_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

//group
register_pager("api/{key}/group/browse", array('use' => "api::group@browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/group/get/categories", array('use' => "api::group@get_categories_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/group/create", array('use' => "api::group@create_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/group/edit", array('use' => "api::group@edit_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/group/delete", array('use' => "api::group@delete_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/group/join", array('use' => "api::group@join_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/group/leave", array('use' => "api::group@leave_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/group/cover", array('use' => "api::group@cover_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/group/logo", array('use' => "api::group@logo_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/page/browse", array('use' => "api::page@browse_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/page/get/categories", array('use' => "api::page@get_categories_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/page/create", array('use' => "api::page@create_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/page/edit", array('use' => "api::page@edit_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/page/delete", array('use' => "api::page@delete_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/page/like", array('use' => "api::page@like_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/page/cover", array('use' => "api::page@cover_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}page/logo", array('use' => "api::page@logo_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));

register_pager("api/{key}/call/init", array('use' => "api::call@init_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/call/get/identity", array('use' => "api::call@get_identity_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));
register_pager("api/{key}/call/get/pending", array('use' => "api::call@get_pending_pager", "filter" => "api"))->where(array("key" => "[a-zA-Z0-9_\-]+"));


//admincp
register_pager("admincp/push/notification", array('use' => "api::admincp@push_pager", 'filter' => 'admin-auth'));



