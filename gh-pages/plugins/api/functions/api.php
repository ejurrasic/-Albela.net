<?php
/**
 * Created by PhpStorm.
 * User: Tiamiyu waliu kola
 * Date: 8/20/16
 * Time: 5:15 PM
 */

//load_functions("announcement::announcement");
function api_temporary_login_user($userid) {
    $user = find_user($userid);
    app()->user = $user;
}

function api_format_output_text($content, $limit = false, $limitType = "", $limitId = "", $emoticons = true) {
    $content = str_replace('\\r\\n', '<br>',$content);
    $content = str_replace('\\r', '<br>',$content);
    $content  = str_replace('\\n\\n', '<br>',$content);
    $content = str_replace('\\n', '<br>',$content);
    $content = str_replace('\\n', '<br>',$content);
    $content = stripslashes($content);
    $content = nl2br($content);
    $content = apiAutoLinkUrls($content);
    $content = apiParseHashtags($content);
    $content = apiParseMention($content);
    if ($emoticons) $content = apiParseEmoticons($content);
    $badWords = config('ban_filters_words', '');
    if ($badWords) {
        $badWords = explode(',', $badWords);
        foreach($badWords as $word) {
            $content = str_replace($word, '***', $content);
        }
    }
    if (is_rtl($content)) {
        $content = "<span style='direction: rtl;text-align: right;display: block'>{$content}</span>";
    }


    if (!$limit) return $content;
    $oContent = $content;
    $tContent = $content;

    $id = md5($tContent.time());
    $result = "<span id='{$id}' style='font-weight: normal !important'>";
    if (mb_strlen($tContent) > 500) {
        $result .= "<span class='text-full' style='display: none;font-weight: normal'>{$content}</span>";
        $tContent = str_limit($tContent, 500);
        $result .= "<span style='font-weight: normal !important'>".$tContent."</span>";
        $result .= '<a class="read_more" href="readMore:'.$limitType.':'.$limitId.'" >'.lang('read-more').'</a>';
    } else {
        $result .= $content;
    }

    $result .= "</span>";
    return $result;
}

function apiParseHashtags($content) {
    $hashtags = hashtag_parse($content);
    if ($hashtags) {
        //print_r($hashtags);
        foreach($hashtags as $hashtag) {

            if ($hashtag != "#039") {
                $color = config('hashtag-color', '#3498db');
                $link = " <a  style='color:{$color} !important' href='hashtag:".str_replace('#', '', $hashtag)."'>".$hashtag."</a> ";
                $content = str_replace($hashtag, $link, $content);
            }
        }
    }

    return $content;
}

function apiParseMention($content) {
    load_functions("mention::mention");
    $mentions = mention_parse($content);
    if ($mentions) {
        $done = array();
        foreach($mentions as $mention) {
            $username = str_replace('@', '', $mention);
            if (!in_array($username, $done)) {
                $done[] = $username;
                $query = db()->query("SELECT id,first_name,last_name,username,avatar FROM users WHERE `username`='{$username}'");
                $user = $query->fetch_assoc();
                if ($query and $user) {
                    $color = config('mention-color', '#3498db');
                    $title = (config('mention-title', 2) == '1') ? $user['username'] : get_user_name($user);
                    $link = " <a ajax='true' style='color:{$color} !important' href='mention:".$user['id']."'>".$title."</a> ";
                    $content = str_replace($mention, $link, $content);
                }
            }
        }
    }

    return $content;
}

function apiParseEmoticons($content) {
    $emoticons = get_emoticons(1);
    foreach($emoticons as $e => $d) {
        $img = "<img src='".url_img($d['path'])."' ";
        if ($d['width'])  $img .= " width='".$d['width']."'";
        if ($d['height'])  $img .= " height='".$d['height']."'";
        $img .="/>";
        $content = str_replace($e, $img, $content);
    }
    $emoticons = get_emoticons(2);
    foreach($emoticons as $e => $d) {
        $img = "<img src='".url_img($d['path'])."' ";
        if ($d['width'])  $img .= " width='".$d['width']."'";
        if ($d['height'])  $img .= " height='".$d['height']."'";
        $img .="/>";
        $content = str_replace($e, $img, $content);
    }
    return $content;
}

function api_arrange_feed($feed) {
    $userid = get_userid();
    $dFeed = array(
        'id' => $feed['feed_id'],
        'name' => $feed['publisher']['name'],
        'avatar' => $feed['publisher']['avatar'],
        'message' => "",
        'full_message'=> "",
        'entity_id' => $feed['entity_id'],
        'entity_type' => $feed['entity_type'],
        'type' => $feed['type'],
        'type_id' => $feed['type_id'],
        'images' => array(),
        'has_like' => false,
        'has_dislike' => false,
        'has_react' => false,
        'like_count' => 0,
        'dislike_count' => 0,
        'ads' => array(),
        'comments' => count_comments('feed', $feed['feed_id']),
        'react_members' => array(),

        'has_subscribed' => (has_subscribed('feed', $feed['feed_id'])) ? true : false,
        'can_pin_post' => (can_pin_post($feed)) ? true : false,
        'is_pinned' => (is_feed_pinned($feed['feed_id'])) ? true : false,
        'can_edit_post' => (can_edit_feed($feed)) ? true : false,

        'has_feeling' => false,
        'feeling_image' => '',
        'feeling_type' => '',
        'feeling_text' => '',
        'video_embed' => '',
        'video_file' => false,
        'video_title' => '',
        'video_count' => '0',

        'files' => array(),
        'location' => '',
        'privacy' => $feed['privacy'],
        'time' => apiTimeAgo($feed['time']),
        'link' => array(),
        'musics' => array(),
        'poll_type' => 0,
        'answers' => array(),

        'shared' => false,
        'shared_id' => '',
        'shared_name' => '',
        'shared_avatar' => '',
        'shared_time' => '',
        'shared_entity_type' => '',
        'shared_entity_id' => '',


        'tag_users' => array(),

        'feed_title' => '',
        'shared_title'=> "",

        "album" => array(),
        'touser' => array()

    );

    //Get the feed title
    if($feed['type'] == 'feed' and $feed['type_id'] == 'change-avatar') {
        $dFeed['feed_title'] = "changed-profile-picture";
    } elseif($feed['type'] == 'feed' and $feed['type_id'] == 'change-cover') {
        $dFeed['feed_title'] = "changed-profile-cover";
    }elseif ($feed['type_id'] == "upload-album-photos" and isset($feed['album-details'])) {
        $dFeed['feed_title'] = "add-photo-to-album";
        $dFeed['album'] = array(
            count($feed['images']),
            $feed['album-details']['id'],
            $feed['album-details']['title']
        );
    }elseif($feed['type_id'] == "upload-video") {
        $dFeed['feed_title'] = "shared-a-video";
    }

    if ($feed['to_user_id']) {
        $toUser = find_user($feed['to_user_id']);
        $dFeed['touser'][] = array(
            'id' => $toUser['id'],
            'name' => get_user_name($toUser)
        );
    }

    if (isset($feed['tags-users'])) {
        foreach($feed['tags-users'] as $user) {
            $dFeed['tag_users'][] = array(
                'id' => $user['id'],
                'name' => get_user_name($user),
                'avatar' => get_avatar(75, $user)
            );
        }
    }

    if (config('feed-like-type', 'regular') == 'regular') {
        if (has_liked("feed", $feed['feed_id'], 1, $userid)) {
            $dFeed['has_like'] = true;
        }
        $count = count_likes('feed', $feed['feed_id']);
        $dFeed['like_count'] = ($count) ? $count : 0;
        if (config('enable-dislike', false)) {
            if (has_disliked("feed", $feed['feed_id'], 1, $userid)) {
                $dFeed['has_dislike'] = true;
            }
            $count = count_dislikes('feed', $feed['feed_id']);
            $dFeed['dislike_count'] = ($count) ? $count : 0;
        }

    } else {
        if (has_reacted("feed", $feed['feed_id'], 1, $userid)) {
            $dFeed['has_react'] = true;
        }
        $people = get_reactors("feed", $feed['feed_id'], 5);
        foreach($people as $user) {
            $dFeed['react_members'][] = array(
                get_avatar(75, $user),
                $user['like_type'],
                get_user_name($user),
                $user['id']
            );
        }
    }

    if ($feed['shared']) {
        $dFeed['shared'] = true;
        $dFeed['shared_id'] = $feed['shared-feed']['feed_id'];
        $dFeed['shared_name'] = $feed['shared-feed']['publisher']['name'];
        $dFeed['shared_avatar'] = $feed['shared-feed']['publisher']['avatar'];
        $dFeed['shared_entity_id'] = $feed['shared-feed']['entity_id'];
        $dFeed['shared_entity_type'] = $feed['shared-feed']['entity_type'];
        $dFeed['shared_time'] = apiTimeAgo($feed['shared-feed']['time']);

        $shareTitleType = "post";
        if ($feed['shared-feed']['photos']) $shareTitleType = "photos";
        if ($feed['shared-feed']['video']) $shareTitleType = "video";
        if ($feed['shared-feed']['files']) $shareTitleType = "file";

        $dFeed['shared_title'] = $shareTitleType;
        $feed = array_merge($feed, $feed['shared-feed']);
    }
    $dFeed['message'] = api_format_output_text($feed['feed_content'], true, 'feed', $feed['feed_id']);
    $dFeed['full_message'] = api_format_output_text($feed['feed_content']);
    $dFeed['poll_type'] = $feed['is_poll'];
    $dFeed['location'] = $feed['location'];
    if ($feed['is_poll']) {
        $answers = get_poll_answers($feed['feed_id']);
        foreach($answers as $answer) {
            $users = get_poll_answers_user($answer['answer_id'], 4, 1);
            $aUser = array();
            foreach($users as $user) {
                $aUser[] = array(
                    'name' => get_user_name($user),
                    'avatar' => get_avatar(75, $user),
                    'id' => $user['id']
                );
            }

            $dFeed['answers'][] = array(
                'percent' => @floor(($answer['voters'] * 100)/$feed['poll_voters']),
                'text' => $answer['answer_text'],
                'answered' => (in_poll_answer($answer['answer_id'])) ? true : false,
                'id' => $answer['answer_id'],
                'users' => $aUser,
                'poll_id' => $feed['feed_id']
            );
        }
    }
    if ($feed['link_details']) {
        $details = perfectUnserialize($feed['link_details']);
        if ($details) {
            $dFeed['link'][0]['type']  = $details['type'];
            $dFeed['link'][0]['image']  = $details['image'];
            $dFeed['link'][0]['link']  = $details['link'];
            $dFeed['link'][0]['title']  = sanitizeText($details['title']);
            $dFeed['link'][0]['description']  = sanitizeText(str_limit($details['description'], 200));
            $dFeed['link'][0]['code']  = str_replace('width="480"', "style='width:100%'", $details['code']);
            $dFeed['link'][0]['provider_url']  = (isset($details['provider_url'])) ? $details['provider_url'] : "";
        }
    }

    if ($feed['feeling_data']) {
        $dFeed['has_feeling'] = true;
        $dFeed['feeling_image'] = img("images/status/{$feed['feeling_data']['type']}.png");
        $dFeed['feeling_type'] = $feed['feeling_data']['type'];
        $dFeed['feeling_text'] = $feed['feeling_data']['text'];
    }

    if ($feed['video']) {
        if (is_numeric($feed['video'])) {
            $video = (isset($feed['videoDetails'])) ? $feed['videoDetails'] : null;
            if ($video) {
                if ($video['source'] == 'upload') {
                    $dFeed['video_title'] = $video['title'];
                    $dFeed['video_count'] = $video['view_count'];
                    $dFeed['video_embed'] = '<iframe allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" src="'.url_to_pager("play-video").'?link='.$video['file_path'].'&height=350" style="width:100%;border:none;padding:0" height="350"></iframe>';
                } else {
                    $dFeed['video_embed'] = str_replace(array('width="480"', '270'), array("style='width:100%'", "345"), $video['code']);
                    $dFeed['video_title'] = $video['title'];
                }
            }
        } else {
            $dFeed['video_embed'] = '<iframe allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" src="'.url_to_pager("play-video").'?link='.$feed['video'].'&height=350" style="width:100%;border:none;padding:0" height="350"></iframe>';
        }
    }

    if ($feed['files']) {
        foreach($feed['files'] as $id => $file) {
            $dFeed['files'][] = array(
                'extension_icon' => img('images/file-icons/'.$file['extension'].'.png'),
                'extension' => $file['extension'],
                'name' => $file['name'],
                'download' => url_to_pager('feed-download')."?file=".$file['path']."&name=".$file['name']
            );
        }
    }


    if (isset($feed['images'])) {
        $images = array();
        foreach($feed['images'] as $id => $imagePath) {
            $images[] = url_img($imagePath, 920);
        }
        $dFeed['images'] = $images;
    }

    if (isset($feed['music']) and $feed['music']) {
        $music = (isset($feed['musicDetails'])) ? $feed['musicDetails'] : get_music($feed['music']);
        $dFeed['musics'][] = api_arrange_songs($music);
    }

    return $dFeed;
}


function api_arrange_blog($blog) {
    $type = "blog";
    $typeId = $blog['id'];
    $user = find_user($blog['user_id']);
    $result =  array(
        'time' => date('M j , y  h:i A', $blog['time']),
        'id' => $blog['id'],
        'title' => $blog['title'],
        'cover' => ($blog['image']) ? url_img($blog['image'], 700) : get_avatar(200, $user),
        'image' => ($blog['image']) ? url_img($blog['image'], 700) : "",
        'tags' => $blog['tags'],
        'slug' => $blog['slug'],
        'featured' => $blog['featured'],
        'content' => api_format_output_text($blog['content']),
        'has_like' => false,
        'has_dislike' => false,
        'has_react' => false,
        'like_count' => 0,
        'dislike_count' => 0,
        'comments' => count_comments($type, $typeId),
        'react_members' => array(),
    );
    $userid = get_userid();
    if (config('feed-like-type', 'regular') == 'regular') {
        if (has_liked($type, $typeId, 1, $userid)) {
            $result['has_like'] = true;
        }
        $result['like_count'] = count_likes($type, $typeId);
        if (config('enable-dislike', false)) {
            if (has_disliked($type, $typeId, 1, $userid)) {
                $result['has_dislike'] = true;
            }
            $result['dislike_count'] = count_dislikes('feed', $typeId);
        }

    } else {
        if (has_reacted($type, $typeId, 1, $userid)) {
            $result['has_react'] = true;
        }
        $people = get_reactors($type, $typeId, 5);
        foreach($people as $user) {
            $result['react_members'][] = array(
                get_avatar(75, $user),
                $user['like_type'],
                get_user_name($user),
                $user['id']
            );
        }
    }

    return $result;
}

function api_arrange_listing($listing) {
    $type = "listing";
    $typeId = $listing['id'];
    $result = array(
        'id' => $typeId,
        'title' => $listing['title'],
        'description' => $listing['description'],
        'price' => $listing['price'],
        'slug' => $listing['slug'],
        'image' => $listing['image'] ? url_img($listing['image'], 200) : img('marketplace::images/no_image.jpg', 75),
        'short_description' => str_limit($listing['description'], 50),
        'time' => date('d/m/Y', strtotime($listing['date'])),
        'link' => $listing['link'],
        'tags' => $listing['tags'],
        'address' => $listing['address'],
        'comments' => count_comments($type, $typeId),
        'can_edit' => ($listing['lister_id'] == get_userid()) ? true : false
    );

    return $result;
}

function api_arrange_event($event) {
    $event = arrange_event($event);
    $result = array(
        'id' => $event['event_id'],
        'title' => $event['event_title'],
        'description' => $event['event_desc'],
        'location' => $event['location'],
        'address' => $event['address'],
        'start_time' => $event['start_time'],
        'end_time' => $event['end_time'],
        'image' => get_event_logo($event),
        'cover' => get_event_cover($event, false),
        'is_admin' => (is_event_admin($event)) ? true : false,
        'count_going' => count_event_going($event['event_id']),
        'count_maybe' =>  count_event_maybe($event['event_id']),
        'count_invited' => count_event_invited($event['event_id']),
        'rsvp' => 0
    );


    if ($event['user_id'] != get_userid()) {
        $result['rsvp'] = get_event_my_rsvp($event['event_id']);
    }
    $eventTime = get_event_date($event, 'day', 'd');
    if(get_event_date($event, 'year', 'Y') != date('Y')) {
        $eventTime .= " " .get_event_date($event, 'year', 'Y');
    }
    $eventTime .= get_event_date($event)." ".lang('event::at').' '.get_event_date($event, 'time', 'g : i A');
    $result['event_time'] = $eventTime;
    return $result;
}

function api_arrange_group($group) {
    $result = array(
        'id' => $group['group_id'],
        'title' => utf8_encode($group['group_title']),
        'description' => utf8_encode($group['group_description']),
        'logo' => get_group_logo(600, $group),
        'cover' => get_group_cover($group, false),
        'can_post' => (group_can_post($group)) ? true : false,
        'is_member' => (is_group_member($group['group_id'])) ? true : false,
        'is_admin' => (get_userid() == $group['user_id']) ? true : false
    );
    return $result;
}

function api_arrange_page($page) {
    load_functions('like::like');
    $result = array(
        'id' => $page['page_id'],
        'title' => $page['page_title'],
        'description' => $page['page_desc'],
        'verified' => ($page['verified']) ? true : false,
        'logo' => get_page_logo(600, $page),
        'cover' => get_page_cover($page, false),
        'is_admin' => (is_page_admin($page)) ? true : false,
        'has_like' => (has_liked('page', $page['page_id'])) ? true : false,
    );

    return $result;
}

function api_arrange_video($video) {
    $type = "video";
    $typeId = $video['id'];
    $result = array(
        'title' => $video['title'],
        'description' => $video['description'],
        'photo' => ($video['photo_path']) ? url_img($video['photo_path']) : img('video::images/preview.png'),
        'source' => $video['source'],
        'code' => ($video['source'] == "upload") ? '<iframe class="" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" style="border: none;padding: 0 !important;margin:0 10px;border-radius: 3px;overflow: hidden;width: 96%;" src="'.url_to_pager("play-video").'?link='.$video['file_path'].'&height=430" width="100%" height="300"></iframe>' : str_replace(array('width="480"', '270'), array("style='width:100%'", "300"), $video['code']),
        'file' => $video['file_path'],
        'view_count' => $video['view_count'],
        'featured' => $video['featured'],
        'slug' => $video['slug'],
        'id' => $video['id'],
        'time' => $video['time'],
        'comments' => count_comments($type, $typeId),
        'has_like' => false,
        'has_dislike' => false,
        'has_react' => false,
        'like_count' => 0,
        'dislike_count' => 0,
        'react_members' => array(),
    );
    $userid = get_userid();
    if (config('feed-like-type', 'regular') == 'regular') {
        if (has_liked($type, $typeId, 1, $userid)) {
            $result['has_like'] = true;
        }
        $result['like_count'] = count_likes($type, $typeId);
        if (config('enable-dislike', false)) {
            if (has_disliked($type, $typeId, 1, $userid)) {
                $result['has_dislike'] = true;
            }
            $result['dislike_count'] = count_dislikes('feed', $typeId);
        }

    } else {
        if (has_reacted($type, $typeId, 1, $userid)) {
            $result['has_react'] = true;
        }
        $people = get_reactors($type, $typeId, 5);
        foreach($people as $user) {
            $result['react_members'][] = array(
                get_avatar(75, $user),
                $user['like_type'],
                get_user_name($user),
                $user['id']
            );
        }
    }


    return $result;
}

function api_arrange_user($user, $all = false) {

    $dUser = array(
        'status' => 1,
        'userid' => $user['id'],
        'name' => get_user_name($user),
        'avatar' => get_avatar(75, $user),
        'id' => $user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name']
    );

    if ($all) {
        $dUser = array_merge($dUser, array(
            'cover' => get_user_cover($user, false),
            'password' => $user['password'],
            'bio' => $user['bio'],
            'city' => $user['city'],
            'state' => $user['state'],
            'activated' => ($user['activated']) ? true : false,
            'active' => ($user['active']) ? true : false
        ));
    }

    return $dUser;
}

function  api_arrange_notification($notification) {
    $dNotify = array(
        'id' => $notification['notification_id'],
        'time' => apiTimeAgo($notification['time']),
        'avatar' => get_avatar(75, $notification),
        'title' => $notification['title'],
        'content' => $notification['content'],
        'name' => get_user_name($notification),
        'type' => $notification['type'],
        'type_id' => $notification['type_id'],
        'userid' => $notification['from_userid'],
        'data_one' => '',
        'data_two' => '',
        'data_three' => '',
    );
    $type = $notification['type'];
    switch(true) {
        case in_array($type, array('blog.like', 'blog.like.comment',  'blog.comment')):
            $blog = unserialize($notification['data']);
            $dNotify['title'] = $type;
            $dNotify['blog'] = array(api_arrange_blog(get_blog($notification['type_id'])));
            $dNotify['data_one'] = isset($blog['slug']) ? $blog['slug'] : $notification['type_id'];
            break;
        case in_array($type , array('event.rsvp', 'event.events' , 'event.invite',  'event.post')):
            $event = find_event($notification['type_id']);
            if ($event) {
                $dNotify['event'] = array(api_arrange_event($event));
                $data = ($type != 'event.post') ? unserialize($notification['data']) : array();
                $dNotify['avatar'] = get_event_logo($event);
                $dNotify['title'] = $type;
                $dNotify['name'] = $event['event_title'];
                $dNotify['data_one'] = $event['event_title'];
                $dNotify['data_two'] = ($type == 'event.rsvp') ? $data['rsvp'] : '';
                $dNotify['data_three'] = ($type == 'event.events') ? $data['when'] : '';
            } else {
                delete_notification($notification['notification_id']);
            }
            break;
        case in_array($type, array('group.role' , 'group.add.member')):
            $group = find_group($notification['type_id']);
            if (!$group) {
                $dNotify['group'] = array(api_arrange_group($group));
                delete_notification($notification['notification_id']);
            } else{
                $dNotify['title'] = $group['group_title'];
            }
            break;
        case in_array($type, array('page.new.role' , 'page.invite')):
            $page = find_page($notification['type_id']);
            $dNotify['title'] = $page['page_title'];
            $dNotify['page'] = array(api_arrange_page($page));
            if ($type == 'page.invite') {
                $data = unserialize($notification['data']);
                $dNotify['data_one'] = $data['role'];
            }
            break;
        case in_array($type, array('music.like' , 'music.like.react' , 'music.dislike' , 'music.like.comment' , 'music.dislike.comment' , 'music.comment' , 'music.comment.reply')):
            $dNotify['music'] = array(api_arrange_songs(get_music($notification['type_id'])));
            break;
        case in_array($type, array('listing.comment')):
            $dNotify['listing'] = array(api_arrange_listing(marketplace_get_listing($notification['type_id'])[0]));
            break;
        case in_array($type, array('video.processing' , 'video.processed' , 'video.like' , 'video.like.react' , 'video.dislike' , 'video.like.commen' , 'video.dislike.comment' , 'video.comment' , 'video.comment.reply')):
            $video = get_video($notification['type_id']);
            $dNotify['video'] = array(api_arrange_video($video));
            break;
        case in_array($type, array('feed.like' , 'feed.like.react' , 'feed.dislike' , 'feed.like.comment' , 'feed.dislike.comment' , 'feed.comment' , 'feed.comment.reply' , 'feed.shared' , 'feed.tag' , 'post-on-timeline')):
            $feed = find_feed($notification['type_id']);
            $dNotify['feed'] = array(api_arrange_feed($feed));
            break;
    }

    $dNotify = fire_hook("api.notification", $dNotify, array($notification));
    //print_r($dNotify);
    //exit;
    //if (empty($dNotify['title'])) return false;
    return $dNotify;
}

function api_count_notifications() {
    $userid = get_userid();
    $query = db()->query("SELECT notification_id FROM notifications INNER JOIN `users` ON notifications.from_userid=users.id WHERE `to_userid`='{$userid}' AND `seen`='0'");
    if ($query) return $query->num_rows;
    return 0;
}

function api_arrange_songs($music) {
    $type = "music";
    $typeId = $music['id'];
    $result =  array(
        'id' => $music['id'],
        'title' => $music['title'],
        'artist' => $music['artist'],
        'album' => $music['album'],
        'file' => url($music['file_path']),
        'cover' => ($music['cover_art']) ? url_img($music['cover_art'], 920) : img('music::images/preview.png'),
        'play_count' => $music['play_count'],
        'featured' => $music['featured'],
        'user' => array(),
        'has_like' => false,
        'has_dislike' => false,
        'has_react' => false,
        'like_count' => 0,
        'dislike_count' => 0,
        'slug' => $music['slug'],
        'comments' => count_comments($type, $typeId),
        'react_members' => array(),
        'privacy' => $music['privacy'],
        'can_edit' => (is_music_owner($music)) ? true : false
    );
    $userid = get_userid();
    if (config('feed-like-type', 'regular') == 'regular') {
        if (has_liked($type, $typeId, 1, $userid)) {
            $result['has_like'] = true;
        }
        $result['like_count'] = count_likes($type, $typeId);
        if (config('enable-dislike', false)) {
            if (has_disliked($type, $typeId, 1, $userid)) {
                $result['has_dislike'] = true;
            }
            $result['dislike_count'] = count_dislikes('feed', $typeId);
        }

    } else {
        if (has_reacted($type, $typeId, 1, $userid)) {
            $result['has_react'] = true;
        }
        $people = get_reactors($type, $typeId, 5);
        foreach($people as $user) {
            $result['react_members'][] = array(
                get_avatar(75, $user),
                $user['like_type'],
                get_user_name($user),
                $user['id']
            );
        }
    }
    $owner = get_music_owner($music);
    $user = array(
        'id' => $owner['id'],
        'name' => $owner['name'],
        'avatar' => $owner['image']
    );
    $result['user'] = $user;
    return $result;
}

function apiTimeAgo($time_ago){
    $cur_time 	= time();
    $time_elapsed 	= $cur_time - $time_ago;
    $seconds 	= $time_elapsed ;
    $minutes 	= round($time_elapsed / 60 );
    $hours 		= round($time_elapsed / 3600);
    $days 		= round($time_elapsed / 86400 );
    $weeks 		= round($time_elapsed / 604800);
    $months 	= round($time_elapsed / 2600640 );
    $years 		= round($time_elapsed / 31207680 );
    $result = array(
        'number' => '',
        'format' => ''
    );
    if($seconds <= 60){
        $result['number'] = $seconds;
        $result['format'] = "seconds-ago";
    }
//Minutes
    else if($minutes <=60){
        if($minutes==1){
            $result['number'] = 1;
            $result['format'] = "minutes-ago";

        }
        else{
            $result['number'] = $minutes;
            $result['format'] = "minutes-ago";
        }
    }
//Hours
    else if($hours <=24){
        if($hours==1){
            $result['number'] = 1;
            $result['format'] = "hours-ago";
        }else{
            $result['number'] = $hours;
            $result['format'] = "hours-ago";
        }
    }
//Days
    else if($days <= 7){
        if($days==1){
            $result['number'] = 1;
            $result['format'] = "days-ago";
        }else{
            $result['number'] = $days;
            $result['format'] = "days-ago";
        }
    }
//Weeks
    else if($weeks <= 4.3){
        if($weeks==1){
            $result['number'] = 1;
            $result['format'] = "weeks-ago";
        }else{
            $result['number'] = $weeks;
            $result['format'] = "weeks-ago";
        }
    }
//Months
    else if($months <=12){
        if($months==1){
            $result['number'] = 1;
            $result['format'] = "months-ago";
        }else{
            $result['number'] = $months;
            $result['format'] = "months-ago";
        }
    }
//Years
    else{
        if($years==1){
            $result['number'] = 1;
            $result['format'] = "years-ago";
        }else{
            $result['number'] = $years;
            $result['format'] = "years-ago";
        }
    }

    return $result['number'].":".$result['format'];
}

function apiAutoLinkUrls($text,$popup = true){
    $target = false;
    $str = $text;
    if ($target)
    {
        $target = ' target="'.$target.'"';
    }
    else
    {
        $target = '';
    }
    // find and replace link
    $str = preg_replace('@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@',"<a onclick=\"return window.open('http://$1')\" nofollow='nofollow' href='javascript::void(0)' {$target}>$1</a>", $str);
    // add "http://" if not set
    $str = preg_replace('/<a\s[^>]*href\s*=\s*"((?!https?:\/\/)[^"]*)"[^>]*>/i', "<a onclick=\"return window.open('$1')\" nofollow='nofollow' href='javascript::void(0)' {$target}>$1</a>", $str);
    //return $str;
    $regexB = '(?:[^-\\/"\':!=a-z0-9_@＠]|^|\\:)';
    $regexUrl = '(?:[^\\p{P}\\p{Lo}\\s][\\.-](?=[^\\p{P}\\p{Lo}\\s])|[^\\p{P}\\p{Lo}\\s])+\\.[a-z]{2,}(?::[0-9]+)?';
    $regexUrlChars = '(?:(?:\\([a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_,~]+\\))|@[a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_,~]+\\/|[\\.\\,]?(?:[a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_~]|,(?!\s)))';
    $regexURLPath = '[a-z0-9=#\\/]';
    $regexQuery = '[a-z0-9!\\*\'\\(\\);:&=\\+\\$\\/%#\\[\\]\\-_\\.,~]';
    $regexQueryEnd = '[a-z0-9_&=#\\/]';

    $regex = '/(?:'             # $1 Complete match (preg_match already matches everything.)
        . '('.$regexB.')'    # $2 Preceding character
        . '('                                     # $3 Complete URL
        . '((?:https?:\\/\\/|www\\.)?)'           # $4 Protocol (or www)
        . '('.$regexUrl.')'          # $5 Domain(s) (and port)
        . '(\\/'.$regexUrlChars.'*'   # $6 URL Path
        . $regexURLPath.'?)?'
        . '(\\?'.$regexQuery.'*'  # $7 Query String
        . $regexQueryEnd.')?'
        . ')'
        . ')/iux';
//    return $text;
    return preg_replace_callback($regex, function($matches) {

        list($all, $before, $url, $protocol, $domain, $path, $query) = array_pad($matches, 7, '');
        $href = ((!$protocol || strtolower($protocol) === 'www.') ? 'http://'.$url : $url);
        //if (!$protocol && !preg_match('/\\.(?:com|net|org|gov|edu)$/iu' , $domain)) return $all;
        return $before."<a href='{$href}' >".$url."</a>";
    } , $text);
}//end AutoLinkUrls