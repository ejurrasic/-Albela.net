<?php
load_functions('video::video');
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("video::css/video.css");
        register_asset("video::js/video.js");
    }
});
register_hook('admin-started', function() {
    get_menu("admin-menu", "plugins")->addMenu(lang("video::videos-manager"), '#', "videos-manager");
    get_menu("admin-menu", "plugins")->findMenu("videos-manager")->addMenu(lang("video::manage"), url('admincp/videos'), "manage");
    get_menu("admin-menu", "plugins")->findMenu("videos-manager")->addMenu(lang("categories"), url('admincp/videos/categories'), "categories");
});

register_filter('video-auth', function() {
    $videoId = segment(1);
    $video = get_video($videoId);
    app()->video = $video;
    app()->setTitle($video['title']);
    $id = $video['id'];
    db()->query("UPDATE videos SET view_count = 1 + view_count WHERE id='{$id}'");
    return true;
});

//register admincp pagers
register_pager("admincp/videos/categories", array('use' => 'video::admincp@categories_pager', 'as' => 'admincp-video-categories-pager', 'filter' => 'admin-auth'));
register_pager("admincp/videos", array('use' => 'video::admincp@videos_pager', 'as' => 'admincp-video-pager', 'filter' => 'admin-auth'));
register_pager("admincp/videos/manage", array('use' => 'video::admincp@videos_manage_pager', 'as' => 'admincp-video-manage-pager', 'filter' => 'admin-auth'));
register_pager("admincp/videos/manage/categories", array('use' => 'video::admincp@manage_categories_pager', 'as' => 'admin-video-manage-category', 'filter' => 'admin-auth'));
register_pager("admincp/video/categories/add", array('use' => 'video::admincp@add_categories_pager', 'as' => 'admincp-video-add-category-pager', 'filter' => 'admin-auth'));


register_pager("videos", array('use' => 'video::video@videos_pager', 'as' => 'videos'));
register_pager("video/edit", array('use' => 'video::video@video_edit_pager', 'as' => 'video-edit'));
register_pager("video/delete", array('use' => 'video::video@video_delete_pager', 'as' => 'video-delete'));
register_pager("videos/create", array('use' => 'video::video@create_pager', 'as' => 'video-create', 'filter' => 'auth'));
register_pager("video/{id}", array('use' => 'video::video@video_page_pager', 'as' => 'video-page', 'filter' => 'video-auth'))->where(array('id' => '[a-zA-Z0-9\-\_]+'));
register_hook('can.post.video', function($result, $type, $id) {
   if ($type == 'user' and $id != get_userid()) $result['result'] = false;
    return $result;
});

register_hook('video.added', function($video, $videoId) {
   if ($video['entity_type'] == 'user' and $video['status']) {
       add_activity(get_video_url($video), null, 'video', $videoId, $video['privacy']);
       add_feed(array(
           'entity_id' => $video['entity_id'],
           'entity_type' => $video['entity_type'],
           'type' => 'feed',
           'type_id' => 'upload-video',
           'type_data' => $video['id'],
           'video' => $video['id'],
           'privacy' => $video['privacy'],
           'images' => '',
           'auto_post' => true,
           'can_share' => 1
       ));
   }
});

register_hook('video.processed', function($video, $videoId) {
    if ($video['entity_type'] == 'user' and $video['auto_posted'] == 0) {
        add_activity(get_video_url($video), null, 'video', $videoId, $video['privacy']);
        add_feed(array(
            'entity_id' => $video['entity_id'],
            'entity_type' => $video['entity_type'],
            'type' => 'feed',
            'type_id' => 'upload-video',
            'type_data' => $video['id'],
            'video' => $video['id'],
            'privacy' => $video['privacy'],
            'images' => '',
            'auto_post' => true,
            'can_share' => 1
        ));
    }
});

register_hook('feed-title', function($feed) {
    if ($feed['type_id'] == "upload-video") {
        echo lang('video::shared-video');
    }
});

register_hook('profile.started', function($user) {
    add_menu('user-profile-more', array('title' => lang('video::videos'), 'as' => 'videos', 'link' => profile_url('videos', $user)));
});

register_pager("{id}/videos", array("use" => "video::user-profile@videos_pager", "as" => "profile-videos", 'filter' => 'profile'))
    ->where(array('id' => '[a-zA-Z0-9\_\-]+'));

register_hook('feed.arrange', function($feed) {
    if(is_numeric($feed['video'])) {
        $video = get_video($feed['video']);

        if ($video) {
            if ($video['status'] == 0 and ($feed['user_id'] != get_userid())) $feed['status'] = 0;
            $feed['videoDetails'] = $video;
        }
    }
    return $feed;
});

register_hook("activity.title", function($title, $activity, $user) {
   switch($activity['type']) {
       case 'video':
           $videoId = $activity['type_id'];
           $video = get_video($videoId);
           if (!$video) return "invalid";
           $link = get_video_url($video);
           $owner = get_video_owner($video);

           return activity_form_link($owner['link'], $owner['name'], true)." ". lang("activity::added-new")." ".activity_form_link($activity['link'], lang('video::video'), true, true);
           break;
   }

    return $title;
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'video.processing') {
        return view("video::notifications/processing", array('notification' => $notification, 'video' => get_video($notification['type_id'])));
    } elseif ($notification['type'] == 'video.processed') {
        return view("video::notifications/processed", array('notification' => $notification, 'video' => get_video($notification['type_id'])));
    } elseif ($notification['type'] == 'video.like') {
        return view("video::notifications/like", array('notification' => $notification, 'type' => 'like'));
    } elseif($notification['type'] == 'video.like.react') {
        return view("video::notifications/react", array('notification' => $notification));
    }
    elseif($notification['type'] == 'video.dislike') {
        return view("f::notifications/like", array('notification' => $notification, 'type' => 'dislike'));
    }elseif($notification['type'] == 'video.like.comment') {
        return view("video::notifications/like-comment", array('notification' => $notification, 'type' => 'like'));
    }elseif($notification['type'] == 'video.dislike.comment') {
        return view("video::notifications/like-comment", array('notification' => $notification, 'type' => 'dislike'));
    }
    elseif($notification['type'] == 'video.comment') {
        $video = get_video($notification['type_id']);
        if ($video) {

            return view("video::notifications/comment", array('notification' => $notification, 'video' => $video));
        } else {
            delete_notification($notification['notification_id']);
        }

    } elseif($notification['type'] == 'video.comment.reply') {
        return view("video::notifications/reply", array('notification' => $notification));
    }
});

register_hook("like.item", function($type, $typeId, $userid) {
    if ($type == 'video') {
        $video = get_video($typeId);
        if ($video['user_id'] and $video['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $video['user_id'], 'video.like', $typeId, $video);
        }
    } elseif($type == 'comment') {
        $comment = find_comment($typeId, false);
        if ($comment and $comment['user_id'] != get_userid()) {
            if ($comment['type'] == 'video') {
                $video = get_video($comment['type_id']);
                send_notification_privacy('notify-site-like', $comment['user_id'], 'video.like.comment', $comment['type_id'], $video);
            }
        }
    }
});

register_hook("like.react", function($type, $typeId, $code, $userid) {
    if ($type == 'video') {
        $video = get_video($typeId);
        if ($video['user_id'] and $video['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $video['user_id'], 'video.like.react', $typeId, $video, $code);
        }
    }
});

register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'video') {
        $video = get_video($typeId);
        $subscribers = get_subscribers($type, $typeId);
        foreach($subscribers as $userid) {
            if ($userid != get_userid()) {
                send_notification_privacy('notify-site-comment',$userid, 'video.comment', $typeId, $video, null, $text);
            }
        }

    }
});

register_hook("reply.add", function($commentId, $type, $typeId, $text) {
    if ($type == 'video') {
        $video = get_video($typeId);
        $subscribers = get_subscribers('comment', $commentId);
        foreach($subscribers as $userid) {
            if ($userid != get_userid()) {
                send_notification_privacy('notify-site-reply-comment', $userid, 'video.comment.reply', $typeId, $video, null, $text);
            }
        }
    }
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'video.like') {
        if(isset($notification['data']['user_id'])) return view("video::notifications/like", array('notification' => $notification, 'video' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'video.like.react') {
        if(isset($notification['data']['user_id'])) return view("video::notifications/react", array('notification' => $notification, 'video' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'video.like.comment') {
        if(isset($notification['data']['user_id'])) return view("video::notifications/like-comment", array('notification' => $notification, 'video' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'video.comment') {
        if(isset($notification['data']['user_id'])) return view("video::notifications/comment", array('notification' => $notification, 'video' => unserialize($notification['data'])));
    }
    elseif($notification['type'] == 'video.comment.reply') {
        if(isset($notification['data']['user_id'])) return view("video::notifications/reply", array('notification' => $notification, 'video' => unserialize($notification['data'])));
    }
});

TaskManager::add('video-processing', function() {
    if (config('video-encoder') == 'ffmpeg') {
        //get all unprocessed videos
        $query = db()->query("SELECT * FROM videos WHERE status='0' AND source='upload'");
        while($fetch = $query->fetch_assoc()) {
            $file_path = $fetch['file_path'];
            if ($file_path and file_exists(path($file_path))) {
                try {
                    $photoPath = 'storage/uploads/video/photos/';
                    $photoName = $fetch['slug'].'.jpg';
                    @mkdir(path($photoPath), 0777, true);
                    $fullPhotoPath = $photoPath.$photoName;
					$size = '480x320';
                    $start = 1;
                    $frames = 1;
                    if(file_exists(config('video-ffmpeg-path'))) {
                        $video_attributes = get_video_attributes(path($file_path), config('video-ffmpeg-path'));
                        $maxwidth = 480;
                        $width = $video_attributes['width'];
                        $height = $video_attributes['height'];
						if($width && $height) {
							$ratio =	$width/$height;
							$modwidth = $width/($width/$maxwidth);
							$modheight = round($modwidth/$ratio);
							$size = $modwidth.'x'.$modheight;
							$start = $video_attributes['length'] / 2;
						}
					}
					exec(config('video-ffmpeg-path').' -y  -i '.path($file_path).' -an  -s '.$size.' -ss '.$start.' -vframes '.$frames.' -r '.$frames.' '.path($fullPhotoPath).' 2<&1');
                    $details = pathinfo($file_path);
                    $ext = strtolower($details['extension']);
                    $output_file = str_replace($ext, 'mp4', $file_path);
                    exec(config('video-ffmpeg-path').' -i '.path($file_path).' -vcodec h264 -acodec aac -strict -2 '.path($output_file));
                    $videoId = $fetch['id'];
                    if(file_exists($output_file)) db()->query("UPDATE videos SET status='1',file_path='{$output_file}' WHERE id='{$videoId}'");
                    if(file_exists($fullPhotoPath)) db()->query("UPDATE videos SET photo_path='{$fullPhotoPath}' WHERE id='{$videoId}'");
                    //send notification to owner
                    send_notification($fetch['user_id'], 'video.processed', $videoId);
                    fire_hook('video.processed', null, array($fetch, $videoId));
                } catch(Exception $e) {
					exit($e->getMessage());
				}
            }
        }
    }
});

//menu location
add_menu_location('videos-menu', 'video::videos-menu');
//add available menus
add_available_menu('video::videos', 'videos', 'ion-ios-videocam');
add_available_menu('video::all-videos', 'videos', 'ion-ios-videocam', 'videos-menu');

register_hook('user.delete', function($userid) {
    //db()->query("DELETE FROM videos WHERE user_id='{$userid}'");
    $query = db()->query("SELECT id FROM videos WHERE user_id='{$userid}'");
    while($fetch = $query->fetch_assoc()) {
        delete_video($fetch['id']);
    }
});

register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('video::video-permissions'),
        'description' => '',
        'roles' => array(
            'can-add-video' => array('title' => lang('marketplace::can-add-video'), 'value' => 1),
        )
    );
    return $roles;
});