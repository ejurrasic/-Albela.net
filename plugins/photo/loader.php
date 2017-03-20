<?php
load_functions('photo::photo');
register_asset('photo::css/photos.css');
register_asset('photo::js/photos.js');
register_pager('photos', array('as' => 'photo', 'use' => 'photo::photo@photo_pager'));
register_pager('photo/myphotos', array('as' => 'photo-myphotos', 'filter' => 'user-auth', 'use' => 'photo::photo@myphotos_pager'));
register_pager('photo/album_photos', array('as' => 'photo-album-photos', 'filter' => 'user-auth', 'use' => 'photo::photo@album_photos_pager'));
register_pager('photo/upload', array('as' => 'photo-upload-photo', 'use' => 'photo::photo@photo_upload_pager', 'filter' => 'user-auth'));
register_pager('photo/albums', array('as' => 'photo-albums', 'filter' => 'user-auth', 'use' => 'photo::photo@albums_pager'));
register_pager('photo/myalbums', array('as' => 'photo-myalbums', 'filter' => 'user-auth', 'use' => 'photo::photo@myalbums_pager'));
register_pager('photo/create_album', array('as' => 'photo-create-album', 'use' => 'photo::photo@create_album_pager', 'filter' => 'user-auth'));
register_pager('photo/edit_album', array('use' => 'photo::photo@photo_edit_album_pager', 'as' => 'photo-album-edit', 'filter' => 'user-auth'));
add_available_menu('photo::photos', 'photos', 'ion-images');
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("photo::css/photo.css");
        register_asset("photo::js/photo.js");
    }
});
register_hook("admin-started", function() {
    //get_menu("admin-menu", "cms")->addMenu(lang("photo::photo-manager"), "#", "photo-manager");
    // get_menu("admin-menu", "cms")->findMenu('photo-manager')->addMenu(lang("photo::albums"), url_to_pager("admincp-photo-albums"), "albums");
    //get_menu("admin-menu", "cms")->findMenu('photo-manager')->addMenu(lang("photo::photos"), url_to_pager("admincp-photo-lists"), "lists");
});
//add the top menu
if (is_loggedIn()) {
    add_menu("dashboard-menu", array("icon" => "<i class='ion-images'></i>", "id" => "photos", "title" => lang("photo::my-photos"), "link" => profile_url('photos')));
}
register_hook('profile.started', function($user) {
    add_menu("user-profile", array('title' => lang('photo::photos'), 'link' => profile_url('photos', $user), 'id' => 'photos'));
});
register_hook('system.started', function() {
    register_pager("{id}/photos", array("use" => "photo::profile@profile_photos_pager", "as" => "profile-photos", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{id}/photos/albums", array("use" => "photo::profile@profile_photos_albums_pager", "as" => "profile-photos-albums", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{id}/photos/album/create", array("use" => "photo::profile@profile_photos_album_create_pager", "as" => "profile-photo-album-create", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{id}/photos/profile", array("use" => "photo::profile@profile_photos_user_pager", "as" => "profile-photo-user-profile", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{id}/photos/timeline", array("use" => "photo::profile@profile_photos_user_pager", "as" => "profile-photo-user-time", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{id}/photos/cover", array("use" => "photo::profile@profile_photos_user_pager", "as" => "profile-photo-user-cover", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{id}/photos/album/{albumId}", array("use" => "photo::profile@profile_album_photos_pager", "as" => "profile-photo-album-view", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+', 'albumId' => '[0-9]+'));
    register_pager("{id}/photos/album/edit/{albumId}", array("use" => "photo::profile@profile_photos_album_edit_pager", "as" => "profile-photo-album-edit", 'filter' => 'profile'))->where(array('id' => '[a-zA-Z0-9\_\-]+', 'albumId' => '[0-9]+'));
});
register_pager("photo/view/{id}", array("use" => "photo::photo@photo_view_pager", "as" => "photo-view-pager"))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
register_pager("photo/delete/{id}", array("use" => "photo::photo@photo_delete_pager", "as" => "photo-delete"))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
register_pager("photo/dp/{id}", array("use" => "photo::photo@photo_dp_pager", "as" => "photo-dp"))->where(array('id' => '[a-zA-Z0-9\_\-]+'));
/**Registering admincp pagers***/
register_pager("admincp/photo/albums", array('use' => "photo::admincp@albums_pager", 'filter' => 'admin-auth', 'as' => 'admincp-photo-albums'));
register_pager("admincp/photo/lists", array('use' => "photo::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-photo-lists'));
/**
 * Register frontend pagers
 */
//register_pager("photos", array("use" => 'photo::photo@photos_pager', 'as' => 'photos'));
//register_pager("photos/me", array("use" => 'photo::photo@my_photos_pager', 'as' => 'my-photos', 'filter' => 'auth'));
register_pager("photo/album/delete/{id}", array("use" => 'photo::photo@album_delete_pager', 'as' => 'photo-album-delete', 'filter' => 'auth'))->where(array('id' => '[0-9]+'));
register_pager("photo/album/upload", array("use" => 'photo::photo@album_upload_pager', 'as' => 'photo-upload-album', 'filter' => 'auth'));
register_pager("photo/album/photos/paginate", array("use" => 'photo::photo@paginate_album_photos_pager'));
register_pager("photo/album/paginate", array("use" => 'photo::photo@paginate_albums_pager'));
register_pager("photo/load", array("use" => 'photo::photo@load_photo_pager'));
register_hook("footer", function() {echo view('photo::viewer');});
register_hook("feed.arrange" , function($feed) {
    if ($feed and $feed['type_id'] == 'upload-album-photos') {
        $album = get_photo_album($feed['type_data'], false);
        if ($album) {
            $feed['album-details'] = $album;
        } else {
            return false;
        }
    }
    return $feed;
});
register_hook('feed-title', function($feed) {
    if ($feed['type_id'] == "upload-album-photos" and isset($feed['album-details'])) {
        $albumId = $feed['album-details']['id'];
        echo lang('photo::add-photo-to-album', array('count' => count($feed['images']))).' <a ajax="true" href="'.profile_url('photos/album/'.$albumId, $feed['publisher']).'">'.$feed['album-details']['title'].'</a>';
    }
});
register_hook("like.item", function($type, $typeId, $userid) {
    if ($type == 'photo') {
        $photo = find_photo($typeId, false);
        if ($photo and $photo['user_id'] and $photo['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $photo['user_id'], 'photo.like', $typeId, array('image' => $photo['path']));
        }
    } elseif($type == 'comment') {
        $comment = find_comment($typeId, false);
        if ($comment and $comment['user_id'] != get_userid()) {
            if ($comment['type'] == 'photo') {
                $photo = find_photo($comment['type_id'], false);
                if ($photo and $photo['user_id'] and $photo['user_id'] != get_userid()) {
                    send_notification_privacy('notify-site-like', $comment['user_id'], 'photo.like.comment', $typeId, array('image' => $photo['path']));
                }

            }
        }
    }
});
register_hook("like.react", function($type, $typeId, $code, $userid) {
    if ($type == 'photo') {
        $photo = find_photo($typeId, false);
        if ($photo and $photo['user_id'] and $photo['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $photo['user_id'], 'photo.like.react', $typeId, array('image' => $photo['path'], 'code' => $code));
        }
    }
});
if (config('dislike-notification', false)) {
    register_hook("dislike.item", function($type, $typeId, $userid) {
        if ($type == 'photo') {
            $photo = find_photo($typeId, false);
            if ($photo and $photo['user_id'] and $photo['user_id'] != get_userid()) {
                send_notification_privacy('notify-site-dislike-item', $photo['user_id'], 'photo.dislike', $typeId, array('image' => $photo['path']));
            }
        }
    });
}
register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'photo') {
        $subscribers = get_subscribers($type, $typeId);
        $photo = find_photo($typeId, false);
        if ($photo) {
            foreach($subscribers as $userid) {
                if ($userid != get_userid() and $userid != $photo['user_id']) {
                    send_notification_privacy('notify-site-comment', $userid, 'photo.comment', $typeId, array('image' => $photo['path'], 'owned' => false), null, $text);
                }
            }
            if ($photo and $photo['user_id'] != get_userid()) {
                send_notification_privacy('notify-site-comment', $photo['user_id'], 'photo.comment', $typeId, array('image' => $photo['path'], 'owned' => true), null, $text);
                $to_user_id = $photo['user_id'];
                $privacy = get_privacy('email-notification', 1, $to_user_id);
                if (config('enable-email-notification', true) and $privacy) {
                    $mailer = mailer();
                    $user = find_user($to_user_id);
                    if (!user_is_online($user)) {
                        $mailer->setAddress($user['email_address'], get_user_name($user))->template("comment-photo", array(
                            'link' => url('photo/view/'.$typeId),
                            'fullname' => get_user_name(),
                        ));
                        $mailer->send();
                    }
                }
            }
        }

    }
});
register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'photo.like') {
        return view("photo::notifications/like", array('notification' => $notification, 'type' => 'like', 'data' => unserialize($notification['data'])));
    } elseif($notification['type'] == 'photo.like.react') {
        return view("photo::notifications/react", array('notification' => $notification, 'type' => 'like', 'data' => unserialize($notification['data'])));
    } elseif($notification['type'] == 'photo.dislike') {
        return view("photo::notifications/like", array('notification' => $notification, 'type' => 'dislike', 'data' => unserialize($notification['data'])));
    }elseif($notification['type'] == 'photo.like.comment') {
        return view("photo::notifications/like-comment", array('notification' => $notification, 'type' => 'like', 'data' => unserialize($notification['data'])));
    } elseif($notification['type'] == 'photo.dislike.comment') {
        return view("photo::notifications/like-comment", array('notification' => $notification, 'type' => 'dislike', 'data' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'photo.comment') {
        return view("photo::notifications/comment", array('notification' => $notification, 'data' => unserialize($notification['data'])));

    } elseif($notification['type'] == 'feed.comment.reply') {
        return view("feed::notifications/reply", array('notification' => $notification));
    }
});
register_hook('user.delete', function($userid) {
    $d = db()->query("SELECT * FROM medias WHERE user_id='{$userid}'");
    while($photo = $d->fetch_assoc()) {
        delete_photo($photo['id'], $photo);
    }
});
register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('photo::photo-permissions'),
        'description' => '',
        'roles' => array(
            'can-add-photo' => array('title' => lang('photo::can-add-photo'), 'value' => 1),
        )
    );
    return $roles;
});
register_hook('photo.added', function($photo, $photoId) {
    if ($photo['entity_type'] == 'user' and $photo['status']) {
        add_feed(array(
            'entity_id' => $photo['entity_id'],
            'entity_type' => $photo['entity_type'],
            'type' => 'feed',
            'type_id' => 'upload-photo',
            'type_data' => $photo['id'],
            'photo' => $photo['id'],
            'privacy' => $photo['privacy'],
            'images' => '',
            'auto_post' => true,
            'can_share' => 1
        ));
    }
});