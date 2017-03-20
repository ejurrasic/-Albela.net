<?php
load_functions('music::music');
register_asset('music::css/music.css');
register_asset('music::css/music.css', 'backend');
register_asset('music::js/music.js');
register_asset('music::js/music.js', 'backend');
register_hook('admin-started', function() {
    get_menu('admin-menu', 'plugins')->addMenu(lang('music::musics-manager'), '#', 'musics-manager');
    get_menu('admin-menu', 'plugins')->findMenu('musics-manager')->addMenu(lang('music::categories'), url_to_pager('admin-music-categories-pager'), 'categories');
    get_menu('admin-menu', 'plugins')->findMenu('musics-manager')->addMenu(lang('music::musics'), url_to_pager('admin-musics-pager'), 'musics');
    get_menu('admin-menu', 'plugins')->findMenu('musics-manager')->addMenu(lang('music::playlists'), url_to_pager('admin-playlists-pager'), 'playlists');
});
register_hook('feeds.query.fields', function($sqlFields) {
    return $sqlFields.', music';
});
register_hook('music.played', function($id) {
    $played_musics = session_get('played_music', array());
    if(!in_array($id, $played_musics)) {
        $db = db();
        $db->query("UPDATE musics SET play_count = play_count + 1 WHERE slug = '{$id}'");
        $played_musics[] = $id;
        session_put('played_music', $played_musics);
    }
    return true;
});
register_filter('music-playlist-auth', function() {
    $playlistId = segment(2);
    $playlist = get_playlist($playlistId);
    app()->playlist = $playlist;
    $id = $playlist['id'];
    $played_playlists = session_get('played_playlists', array());
    if(!in_array($id, $played_playlists)) {
        $db = db();
        $db->query("UPDATE music_playlists SET play_count = play_count + 1 WHERE id = '{$id}'");
        $played_playlists[] = $id;
        session_put('played_playlists', $played_playlists);
    }
    return true;
});
register_pager("admincp/musics/categories", array('use' => 'music::admincp@categories_pager', 'as' => 'admin-music-categories-pager', 'filter' => 'admin-auth'));
register_pager("admincp/musics", array('use' => 'music::admincp@musics_pager', 'as' => 'admin-musics-pager', 'filter' => 'admin-auth'));
register_pager("admincp/music/manage", array('use' => 'music::admincp@music_manage_pager', 'as' => 'admin-music-manage-pager', 'filter' => 'admin-auth'));
register_pager("admincp/playlists", array('use' => 'music::admincp@playlists_pager', 'as' => 'admin-playlists-pager', 'filter' => 'admin-auth'));
register_pager("admincp/playlist/manage", array('use' => 'music::admincp@playlist_manage_pager', 'as' => 'admin-playlist-manage-pager', 'filter' => 'admin-auth'));
register_pager("admincp/music/manage/categories", array('use' => 'music::admincp@manage_categories_pager', 'as' => 'admin-music-manage-category', 'filter' => 'admin-auth'));
register_pager("admincp/music/categories/add", array('use' => 'music::admincp@add_categories_pager', 'as' => 'admin-music-add-category-pager', 'filter' => 'admin-auth'));
register_pager("music/playlists", array('use' => 'music::playlist@playlists_pager', 'as' => 'music-playlists'));
register_pager("music/playlist/edit", array('use' => 'music::playlist@playlist_edit_pager', 'as' => 'music-playlist-edit'));
register_pager("music/playlist/delete", array('use' => 'music::playlist@playlist_delete_pager', 'as' => 'music-playlist-delete'));
register_pager("music/playlist/create", array('use' => 'music::playlist@playlist_create_pager', 'as' => 'music-playlist-create', 'filter' => 'auth'));
register_pager("music/playlist/{id}", array('use' => 'music::playlist@playlist_page_pager', 'as' => 'music-playlist-page', 'filter' => 'music-playlist-auth'))->where(array('id' => '[a-zA-Z0-9\-\_]+'));
register_pager("music/playlist/editor/search", array('use' => 'music::playlist@playlist_editor_search_result_pager', 'as' => 'music-playlist-editor-search-result'));
register_hook('can.create.playlist', function($result, $type, $id) {
    if ($type == 'user' and $id != get_userid()) $result['result'] = false;
    return $result;
});
register_pager("music/ajax", array('as' => 'music-ajax', 'use' => 'music::ajax@ajax_pager'));
register_pager("music/download/{id}", array('as' => 'music-download', 'use' => 'music::download@download_pager'))->where(array('id' => '[a-zA-Z0-9\-\_]+'));
register_pager("musics", array('use' => 'music::music@musics_pager', 'as' => 'musics'));
register_pager("music/edit", array('use' => 'music::music@music_edit_pager', 'as' => 'music-edit'));
register_pager("music/delete", array('use' => 'music::music@music_delete_pager', 'as' => 'music-delete'));
register_pager("music/create", array('use' => 'music::music@create_pager', 'as' => 'music-create', 'filter' => 'auth'));
register_pager("music/{id}", array('use' => 'music::music@music_page_pager', 'as' => 'music-page'))->where(array('id' => '[a-zA-Z0-9\-\_]+'));
register_hook('can.post.music', function($result, $type, $id) {
   if ($type == 'user' and $id != get_userid()) $result['result'] = false;
    return $result;
});
register_hook('music.added', function($music, $musicId) {
   if ($music['entity_type'] == 'user' and $music['status']) {
       add_activity(get_music_url($music), null, 'music', $musicId, $music['privacy']);
       add_feed(array(
           'entity_id' => $music['entity_id'],
           'entity_type' => $music['entity_type'],
           'type' => 'feed',
           'type_id' => 'upload-music',
           'type_data' => $music['id'],
           'music' => $music['id'],
           'privacy' => $music['privacy'],
           'images' => '',
           'auto_post' => true,
           'can_share' => 1
       ));
   }
});
register_hook('feed-title', function($feed) {
    if ($feed['type_id'] == "upload-music") {
        echo lang('music::shared-music');
    }
});
register_hook('profile.started', function($user) {
    add_menu('user-profile-more', array('title' => lang('music::musics'), 'as' => 'musics', 'link' => profile_url('musics', $user)));
});
register_pager("{id}/musics", array("use" => "music::user-profile@musics_pager", "as" => "profile-musics", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
register_hook('feed.arrange', function($feed) {
    if(is_numeric($feed['music'])) {
        $music = get_music($feed['music']);

        if ($music) {
            if ($music['status'] == 0 and ($feed['user_id'] != get_userid())) $feed['status'] = 0;
            $feed['musicDetails'] = $music;
        }
    }
    return $feed;
});
register_hook("activity.title", function($title, $activity, $user) {
   switch($activity['type']) {
       case 'music':
           $musicId = $activity['type_id'];
           $music = get_music($musicId);
           if (!$music) return "invalid";
           $link = get_music_url($music);
           $owner = get_music_owner($music);

           return activity_form_link($owner['link'], $owner['name'], true)." ". lang("activity::added-new")." ".activity_form_link($activity['link'], lang('music::music'), true, true);
           break;
   }

    return $title;
});
register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'music.like') {
        return view("music::notifications/like", array('notification' => $notification, 'type' => 'like'));
    } elseif($notification['type'] == 'music.like.react') {
        return view("music::notifications/react", array('notification' => $notification));
    }
    elseif($notification['type'] == 'music.dislike') {
        return view("f::notifications/like", array('notification' => $notification, 'type' => 'dislike'));
    }elseif($notification['type'] == 'music.like.comment') {
        return view("music::notifications/like-comment", array('notification' => $notification, 'type' => 'like'));
    }elseif($notification['type'] == 'music.dislike.comment') {
        return view("music::notifications/like-comment", array('notification' => $notification, 'type' => 'dislike'));
    }
    elseif($notification['type'] == 'music.comment') {
        $music = get_music($notification['type_id']);
        if ($music) {
            return view("music::notifications/comment", array('notification' => $notification, 'music' => $music));
        } else {
            delete_notification($notification['notification_id']);
        }
    } elseif($notification['type'] == 'music.comment.reply') {
        return view("music::notifications/reply", array('notification' => $notification));
    }
});
register_hook("like.item", function($type, $typeId, $userid) {
    if ($type == 'music') {
        $music = get_music($typeId);
        if ($music['user_id'] and $music['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $music['user_id'], 'music.like', $typeId, $music);
        }
    } elseif($type == 'comment') {
        $comment = find_comment($typeId, false);
        if ($comment and $comment['user_id'] != get_userid()) {
            if ($comment['type'] == 'music') {
                $music = get_music($comment['type_id']);
                send_notification_privacy('notify-site-like', $comment['user_id'], 'music.like.comment', $comment['type_id'], $music);
            }
        }
    }
});
register_hook("like.react", function($type, $typeId, $code, $userid) {
    if ($type == 'music') {
        $music = get_music($typeId);
        if ($music['user_id'] and $music['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $music['user_id'], 'music.like.react', $typeId, $music, $code);
        }
    }
});
register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'music') {
        $music = get_music($typeId);
        $subscribers = get_subscribers($type, $typeId);
        if(!in_array($music['user_id'], $subscribers)) {
            $subscribers[] = $music['user_id'];
        }
        foreach($subscribers as $userid) {
            if ($userid != get_userid()) {
                send_notification_privacy('notify-site-comment',$userid, 'music.comment', $typeId, $music, null, $text);
            }
        }

    }
});
register_hook("reply.add", function($commentId, $type, $typeId, $text) {
    if ($type == 'music') {
        $music = get_music($typeId);
        $subscribers = get_subscribers('comment', $commentId);
        if(!in_array($music['user_id'], $subscribers)) {
            $subscribers[] = $music['user_id'];
        }
        foreach($subscribers as $userid) {
            if ($userid != get_userid()) {
                send_notification_privacy('notify-site-reply-comment', $userid, 'music.comment.reply', $typeId, $music, null, $text);
            }
        }
    }
});
register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'music.like') {
        if(isset($notification['data']['user_id'])) return view("music::notifications/like", array('notification' => $notification, 'music' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'music.like.react') {
        if(isset($notification['data']['user_id'])) return view("music::notifications/react", array('notification' => $notification, 'music' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'music.like.comment') {
        if(isset($notification['data']['user_id'])) return view("music::notifications/like-comment", array('notification' => $notification, 'music' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'music.comment') {
        if(isset($notification['data']['user_id'])) return view("music::notifications/comment", array('notification' => $notification, 'music' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'music.comment.reply') {
        if(isset($notification['data']['user_id'])) return view("music::notifications/reply", array('notification' => $notification, 'music' => unserialize($notification['data'])));
    }
});
add_menu_location('musics-menu', 'music::musics-menu');
add_available_menu('music::musics', 'musics', 'ion-music-note');
add_available_menu('music::all-musics', 'musics', 'ion-music-note', 'musics-menu');
register_hook('user.delete', function($userid) {
    $query = db()->query("SELECT id FROM musics WHERE user_id='{$userid}'");
    while($fetch = $query->fetch_assoc()) {
        delete_music($fetch['id']);
    }
});
register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('music::music-permissions'),
        'description' => '',
        'roles' => array(
            'can-add-music' => array('title' => lang('music::can-add-music'), 'value' => 1),
            'can-add-playlist' => array('title' => lang('music::can-add-playlist'), 'value' => 1)
        )
    );
    return $roles;
});
register_hook('admin.statistics', function($stats) {
    $stats['forum'] = array(
        'count' => count_musics(),
        'title' => lang('music::musics'),
        'icon' => 'ion-music-note',
        'link' => url_to_pager('admin-musics-pager'),
    );
    return $stats;
});
register_hook("after-render-js", function ($html) {
    $html .= view('music::player');
    return $html;
});