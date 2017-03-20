<?php
function add_feed($val, $api = false) {
    $defined = array(
        'to_user_id' => '',
        'entity_id' => '',
        'entity_type' => 'user',
        'type' => '',
        'type_id' => '',
        'type_data' => '',
        'content' => '',
        'privacy' => '',
        'media_content' => '',
        'link_details' => '',
        'tags' => array(),
        'location' => '',
        'images' => '',
        'music'  => '',
        'video'  => '',
        'files' => '',
        'userid' => get_userid(),
        'can_share' => 1,
        'auto_post' => false,
        'feeling_type' => '',
        'feeling_text' => '',
        'feeling_data' => '',
        'poll' => '',
        'poll_options' => array(),
        'poll_multiple' => 0
    );

    /**
     * @var $to_user_id
     * @var $type
     * @var $type_id
     * @var $type_data
     * @var $privacy
     * @var $tags
     * @var $link_details
     * @var $content
     * @var $entity_id
     * @var $entity_type
     * @var $location
     * @var $images
     * @var $music
     * @var $video
     * @var $files
     * @var $userid
     * @var $can_share
     * @var $auto_post
     * @var $feeling_type
     * @var $feeling_text
     * @var $feeling_data
     * @var $poll
     * @var $poll_options
     * @var $poll_multiple
     */
    extract(array_merge($defined, $val));

    $result = array(
        'status' => 1,
        'message' => lang('feed::failed-to-post'),
        'feed' => ''
    );

    if (!can_post_to_feed($entity_type, $entity_id, $to_user_id)) return json_encode($result);
    //check for images and videos upload
    $imagesFile = input_file('image');
    $imageToUpdate = false;
    if (!$auto_post and $imagesFile and user_has_permission('can-upload-photo-feed')) {
        $images = array();
        $validate = new Uploader(null, 'image', $imagesFile);
        if ($validate->passed()) {
            foreach($imagesFile as $im) {
                $uploader = new Uploader($im);
                $path = get_userid().'/'.date('Y').'/photos/posts/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $image = $uploader->noThumbnails()->resize()->toDB($entity_type.'-posts', $entity_id, $privacy)->result();
                    $images[$uploader->insertedId] = $image;
                } else {
                    $result['status'] = 0;
                    $result['message'] = $uploader->getError();
                    return json_encode($result);
                }
            }
        } else {
            $result['status'] = 0;
            $result['message'] = $validate->getError();
            return json_encode($result);
        }
        if (!empty($images)) {
            if (count($images) == 1) {
                foreach($images as $imgId => $p) {
                    $imageToUpdate = $imgId;
                }
            }
            $images = perfectSerialize($images);
        }
    }

    if (input_file('video') and !$images and user_has_permission('can-upload-video-feed')) {
        $video = "";
        if (!plugin_loaded('video') or !config('video-upload', false) or config('video-encoder') == 'none') {
            app()->config['video-file-types'] = "mp4";
        }
        $uploader = new Uploader(input_file('video'), 'video');
        $path = get_userid().'/'.date('Y').'/videos/posts/';
        $uploader->setPath($path);
        if ($uploader->passed()) {
            if (plugin_loaded('video') && config('video-upload', false) && config('video-encoder') != 'none') {
                $uploader->disableCDN();
            }
            $video = $uploader->uploadVideo()->toDB($entity_type.'-posts', $entity_id, $privacy)->result();
            if (plugin_loaded('video') and config('video-upload', false) and config('video-encoder') != 'none') {
                $videoInsert = add_video(array(
                    'title' => '',
                    'description' => '',
                    'privacy' => $privacy,
                    'source' => 'upload',
                    'status' => 0,
                    'file_path' => $video,
                    'auto_posted' => 1
                ));
                $video = $videoInsert['id'];
            }
        } else {
            $result['status'] = 0;
            $result['message'] = $uploader->getError();
            return json_encode($result);
        }
    }

    $f = input_file('file');
    if ($f and user_has_permission('can-share-file-feed')) {
        $uploadedFiles = array();
        $validate = new Uploader(null, 'file', $f);
        if ($validate->passed()) {
            foreach($f as $file) {
                $uploader = new Uploader($file, 'file');
                $path = get_userid().'/'.date('Y').'/files/posts/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $file = $uploader->uploadFile()->toDB($entity_type.'-posts-files', $entity_id, $privacy)->result();
                    $uploadedFiles[$uploader->insertedId] = array(
                        'path' => $file,
                        'name' => $uploader->sourceName,
                        'extension' => $uploader->extension
                    );
                } else {
                    $result['status'] = 0;
                    $result['message'] = $uploader->getError();
                    return json_encode($result);
                }
            }
        } else {
            $result['status'] = 0;
            $result['message'] = $validate->getError();
            return json_encode($result);
        }
        if ($uploadedFiles) $files = perfectSerialize($uploadedFiles);

    }

    if (empty($content) and empty($images) and empty($files) and empty($music) and empty($video) and empty($type_data) and empty($feeling_text) and empty($feeling_data) and empty($location)) return ($api) ? false : json_encode($result);

    if (config('enable-feed-poll', true) and $poll and user_has_permission('can-create-poll')) {
        $pollOptions = array();
        foreach($poll_options as $option) {
            if ($option) $pollOptions[] = $option;
        }

        if (count($pollOptions) < 2) {
            $result['status'] = 0;
            $result['message'] = lang('feed::feed-poll-options-error');
            return json_encode($result);
        }

        if ($poll_multiple) $poll = 2;
    }
    $time = time();
    $tagsData = serialize($tags);
    $userid = get_userid();
    $content = sanitizeText($content);
    $location = sanitizeText($location);
    $entity_id = sanitizeText($entity_id);
    $entity_type = sanitizeText($entity_type);
    $feeling_text = sanitizeText($feeling_text);
    $feeling = "";
    if ($feeling_text or $feeling_data) {
        if ($feeling_text) {
            $feeling = array(
                'type' => $feeling_type,
                'text' => $feeling_text,
                'data' => $feeling_data
            );

            $feeling = perfectSerialize($feeling);
        }
    }
    $music_colum_sql = empty($music) ? '' : ',music';
    $music_value_sql = empty($music) ? '' : ",'{$music}'";
    $feed = db()->query("INSERT INTO `feeds` (is_poll,feeling_data,to_user_id,link_details,can_share,user_id,files,tags,entity_id,entity_type,type,type_id,type_data,photos{$music_colum_sql},video,feed_content,privacy,location,time) VALUES(
        '{$poll}','{$feeling}','{$to_user_id}','{$link_details}','{$can_share}','{$userid}','{$files}','{$tagsData}','{$entity_id}','{$entity_type}','{$type}','{$type_id}','{$type_data}','{$images}'{$music_value_sql},'{$video}', '{$content}', '{$privacy}', '{$location}', '{$time}'
    )");
    if ($feed) {
        session_put(md5($type.$type_id), time());
        if (!$auto_post) {
            session_put(md5('feed'), time());
            session_put(md5('timeline'.get_userid()), time());
        }
        $feed_id = db()->insert_id;
        if (!empty($images)) {
            $images = perfectUnserialize($images);
            if (count($images) == 1) {
                foreach($images as $imgId => $p) {
                    db()->query("UPDATE medias SET ref_id='{$feed_id}',ref_name='feed' WHERE id='{$imgId}'");
                }
            }
        }

        //lets add the poll options
        if ($poll) {
            $qs = "INSERT INTO poll_answers(poll_id,answer_text)VALUES";
            $a = "";
            foreach($pollOptions as $option) {
                $a .= ($a) ? ",('{$feed_id}','{$option}')" : "('{$feed_id}','{$option}')";
            }
            $qs .= $a;
            db()->query($qs);

        }

        if ($tags and user_has_permission('can-tag-users-feed')) {
            add_user_tags($tags, 'post', $feed_id);
        }

        if ($to_user_id and $to_user_id != get_userid()) {
            //send notification to this user
            send_notification($to_user_id,'post-on-timeline', $feed_id);

            $privacy = get_privacy('email-notification', 1, $to_user_id);
            if (config('enable-email-notification', true) and $privacy) {
                $mailer = mailer();
                $user = find_user($to_user_id);
                if (!user_is_online($user)) {
                    $mailer->setAddress($user['email_address'], get_user_name($user))->template("post-on-wall", array(
                        'link' => url('feed/'.$feed_id),
                        'fullname' => get_user_name(),
                    ));
                    $mailer->send();
                }
            }
        }
        add_subscriber($userid, 'feed', $feed_id);
        fire_hook("feed.added", null, array($feed_id, $val));
        if ($api) {
            return find_feed($feed_id);
        }
        return json_encode(array(
            'status' => 1,
            'message' => lang('feed::feed-successfully-posted'),
            'feed' => view('feed::feed', array('feed' => find_feed($feed_id)))
        ));
    }
}

function can_post_to_feed($entity_type, $entity_id, $to_user_id) {
    //if ($to_user_id and $to_user_id == get_userid()) return false;
    $result = array('result' => true);

    $result = fire_hook('can.post.feed', $result, array($entity_type, $entity_id,$to_user_id));
    return $result['result'];
}

function get_feed_fields() {
    $sqlFields = "status,is_poll,poll_voters,feeling_data,user_id,feed_id,entity_id,entity_type,type_id,type,type_data,to_user_id,photos,video,files,feed_content,privacy,link_details,tags,location,can_share,shared,shared_id,shared_count,edited,time";
    $sqlFields = fire_hook("feeds.query.fields", $sqlFields, array($sqlFields));
    return $sqlFields;
}
/**
 * Method to get feeds
 * @param string $type
 * @param string $type_id
 * @param int $limit
 * @param int $offset
 * @return array
 */
function get_feeds($type = "feed", $type_id = null, $limit = null, $offset = 0, $update = false, $u = true,$sortby=null) {
    $limit = ($limit) ? $limit : config('feed-limit', 10);
    $sql = '';
    if (config('feed-realtime-update', 1)) {
        if (!$update) session_put(md5($type.$type_id), time());
        $updateTime = (session_get(md5($type.$type_id))) ? session_get(md5($type.$type_id)) : time();
        if ($u) session_put(md5($type.$type_id), time());

    }

    $sql = fire_hook("feeds.query", '', array($type, $type_id, $limit, $offset));
    $sqlFields = get_feed_fields();
    //exit($sql);
    if ($type == 'feed') {
        $sql = "SELECT {$sqlFields} FROM `feeds` WHERE  ((`type`='{$type}'";

        $userid = get_userid();
        $sql .= " AND ( (`entity_id`='{$userid}' AND `entity_type`='user') ";
        if (plugin_loaded('relationship')) {

            $users = array($userid);

            $followings = array_merge($users, get_following($userid));
            $followings = implode(',', $followings);
            $sql .= " OR (entity_type='user' AND `privacy`='1' AND `entity_id` IN ({$followings}))";

            $friends = array_merge($users, get_friends($userid));
            $friends = implode(',', $friends);
            $sql .= " OR (entity_type='user' AND (privacy ='1' or privacy='2') AND `entity_id` IN ({$friends}) AND entity_id IN ({$followings}))";

        }
        $sql .= " ))";
        $sql = fire_hook("user.feeds.query", $sql, array($type, $type_id, $limit, $offset));
        $sql .= ")";
    } elseif ($type == 'saved') {
        $saved = get_user_saved('feed');
        $saved[] = 0;
        $saved = implode(',', $saved);
        $sql = "SELECT {$sqlFields} FROM `feeds` WHERE feed_id IN ({$saved})";
    }
    elseif($type == 'timeline') {
        $userid = $type_id;
        if (is_loggedIn()) {

            if ($userid == get_userid()) {
                $privacy = "privacy='1' or privacy='2' or privacy='3'";
            } elseif(friend_status($userid) == 2) {
                $privacy = "privacy='1' or privacy='2'";
            } else {
                $privacy = "privacy='1'";
            }
        } else {
            $privacy = "privacy='1'";
        }
        $result = fire_hook("feed.exclude.type", array(NULL));
        unset($result[0]);
        $where_clause = !empty($result) ? ' type != \''.implode(' AND type != \'', $result).'\' AND ' : '';
        $sql = "SELECT {$sqlFields} FROM `feeds` WHERE {$where_clause} (((`entity_id`='{$userid}' AND `entity_type`='user' AND ({$privacy})) OR to_user_id='{$userid}')";
        //exit($sql);
        $tagId = array();
        $q = db()->query("SELECT tag_id FROM user_tags WHERE tagged_id='{$userid}' AND tag_type='post'");
        while($f = $q->fetch_assoc()) {
            $tagId[] = $f['tag_id'];
        }
        if ($tagId) {
            $tagId = implode(',', $tagId);
            $sql .= " OR feed_id IN ({$tagId}) ";
        }
        $sql.= ')';

        $pinnedPosts = get_pinned_feeds();
        $pinnedPosts[] = 0;
        $pinnedPosts = implode(',', $pinnedPosts);
        $sql .= " AND feed_id NOT IN ({$pinnedPosts})";
    } elseif($type == 'public') {
        $sql = "SELECT {$sqlFields} FROM `feeds` WHERE privacy = '1' ";
    }


    if (is_loggedIn()) {
        $hideFeeds = implode(',', get_privacy('hide-feeds', array()));

        if ($hideFeeds) $sql .= " AND feed_id NOT IN ({$hideFeeds}) ";
        //exit($sql);
        $mostIgnoreUsers = implode(',', mostIgnoredUsers());
        if ($mostIgnoreUsers) $sql .= " AND (entity_type != 'user' OR (entity_type='user' AND entity_id NOT IN ({$mostIgnoreUsers})))";
    }

    $sql = fire_hook('feed.sortby',$sql,array($sortby));
    if ($update) {
        //exit($updateTime.'why-now');
        $sql .= " AND time > {$updateTime} ORDER BY `time` DESC";
        //exit(date('d F Y', 1452580996));
        //exit($sql.time());
    } else {
        $sql .= " ORDER BY `time` DESC LIMIT {$offset},{$limit}";
    }

    $query = db()->query($sql);

    if ($query) {
        $results = array();
        while($fetch = $query->fetch_assoc()) {
            $feed = get_arranged_feed($fetch);
            if ($feed and $feed['status'] == 1) {
                $results[] = $feed;
            } else {
                //think we should delete this
            }
        }

        return $results;
    }
    return array();
}

function get_all_feeds() {
    return paginate("SELECT * FROM feeds ORDER BY time DESC");
}

function get_arranged_feed($fetch) {
    $feed = $fetch;
    $feed['editor'] = array(
        'avatar' => get_avatar(75),
        'id' => get_userid(),
        'type' => 'user'
    );
    if ($fetch['entity_type'] == 'user') {
        $user = find_user($fetch['entity_id'], false);
        if ($user) {
            if (config('feed-user-title', 2) == 1) $user['name'] = $user['username'];
            $feed['publisher'] = $user;

            $feed['publisher']['avatar'] = get_avatar(75, $user);
            $feed['publisher']['url'] = profile_url(null, $user);
        }
    } else {
        $feed['publisher'] = fire_hook('feed.get.publisher', null, array($feed));
    }
    if (!isset($feed['publisher']) or  !$feed['publisher']) return false;

    if ($feed['to_user_id']) {
        $user = find_user($fetch['to_user_id'], false);
        $feed['targetUser'] = $user;
    }
    $tags = @unserialize($feed['tags']);
    if ($tags) {
        $tagUsers = array();
        $tags = implode(',', $tags);

        $query = db()->query("SELECT id,`username`,`first_name`,`last_name`,avatar FROM `users` WHERE `id` IN({$tags})");
        while($fetch = $query->fetch_assoc()) {
            $tagUsers[] = $fetch;
        }

        if ($tagUsers) {
            $feed['tags-users'] = $tagUsers;
            $feed['tagsCount'] = count($tagUsers);
        }
    }
    if ($feed['photos']) {
        $photos = @perfectUnserialize($feed['photos']);
        $images = array();
        if ($photos) {
            foreach($photos as $id => $pPath) {
                try{
                    if (stripos(get_headers(url_img($pPath, 920))[0], "200 OK")) $images[$id] = $pPath;
                } catch(Exception $e) {
                    $images[$id] = $pPath;
                }
            }
            $feed['images'] = $images;
            if(empty($feed['link_details']) && empty($feed['feed_content']) && empty($feed['images']) && empty($feed['video']) && empty($feed['files'])) $feed['empty'] = true;
        }
    }

    if ($feed['files']) {
        $files = @perfectUnserialize($feed['files']);
        $feed['files'] = ($files) ? $files : '';
    }

    if ($feed['shared']) {
        $feed['shared-feed'] = find_feed($feed['shared_id']);
        if (!$feed['shared-feed']) return false;
        $feed['shared_title'] = lang('feed::shared-post', array('name' => "<span data-type='".$feed['shared-feed']['entity_type']."' data-id='".$feed['shared-feed']['entity_id']."' class='preview-card'><a  ajax='true' href='".$feed['shared-feed']['publisher']['url']."'>".$feed['shared-feed']['publisher']['name']."</a></span>"));
        if ($feed['shared-feed']['photos']) $feed['shared_title'] = lang('feed::shared-photo-post', array('name' => "<span data-type='".$feed['shared-feed']['entity_type']."' data-id='".$feed['shared-feed']['entity_id']."' class='preview-card'><a  ajax='true' href='".$feed['shared-feed']['publisher']['url']."'>".$feed['shared-feed']['publisher']['name']."</a></span>"));
        if ($feed['shared-feed']['video']) $feed['shared_title'] = lang('feed::shared-video-post', array('name' => "<span data-type='".$feed['shared-feed']['entity_type']."' data-id='".$feed['shared-feed']['entity_id']."' class='preview-card'><a  ajax='true' href='".$feed['shared-feed']['publisher']['url']."'>".$feed['shared-feed']['publisher']['name']."</a></span>"));
        if ($feed['shared-feed']['files']) $feed['shared_title'] = lang('feed::shared-file-post', array('name' => "<span data-type='".$feed['shared-feed']['entity_type']."' data-id='".$feed['shared-feed']['entity_id']."' class='preview-card'><a  ajax='true' href='".$feed['shared-feed']['publisher']['url']."'>".$feed['shared-feed']['publisher']['name']."</a></span>"));
    }

    if ($feed['feeling_data']) {
        $feed['feeling_data'] = perfectUnserialize($feed['feeling_data']);
    }

    $feed = fire_hook("feed.arrange", $feed);
    return $feed;
}

function get_feed_publisher($id) {
    $fetch = find_feed($id, false);
    $feed = $fetch;

    if ($fetch['entity_type'] == 'user') {
        $user = find_user($fetch['entity_id'], false);
        if ($user) $feed['publisher'] = $user;
    } else {
        $feed['publisher'] = fire_hook('feed.get.publisher', null, array($feed));
    }
    if (!$feed['publisher']) return false;
    return $feed;
}

/**
 * function to find feed
 *
 * @param int $feedId
 * @return array
 */
function find_feed($feedId, $all = true) {
    $query = db()->query("SELECT * FROM  `feeds`  WHERE `feed_id`= ".$feedId);
    if(!$query) return false;
    $fetch = $query->fetch_assoc();
    $privacy = $fetch['privacy'];
    if ($fetch['type'] != 'page') {
        $myBlockedUsers = array_merge(get_blockedIds(), get_blockerIds());
        if ($myBlockedUsers and in_array($fetch['user_id'], $myBlockedUsers)) return false;
        $ownerBlockedUsers = array_merge(get_blockedIds($fetch['user_id']), get_blockerIds($fetch['user_id']));
        if ($ownerBlockedUsers and in_array(get_userid(), $ownerBlockedUsers)) return false;
    }
    if ($privacy == 1) {
        return get_arranged_feed($fetch);
    } elseif( $privacy == 2 ) {
        if (!is_loggedIn()) return false;
        $userid = $fetch['user_id'];
        $users = array($userid);
        $followings = get_following($userid);
        $friends = get_friends($userid);
        $users = array_merge($users, $followings, $friends);
        if (in_array(get_userid(), $users)) return get_arranged_feed($fetch);
        return false;
    } elseif ($privacy == 3) {
        if (get_userid() != $fetch['user_id']) return false;
        return get_arranged_feed($fetch);
    } else {
        $result = array('status' => true, 'feed' => $fetch);
        $result = fire_hook("find.feed", $result);
        if ($result['status']) return get_arranged_feed($fetch);
        return false;
    }
}

function feed_update_privacy($id, $privacy) {
    $feed = find_feed($id, false);
    if (!can_edit_feed($feed)) return false;
    db()->query("UPDATE feeds SET privacy='{$privacy}' WHERE feed_id='{$id}'");

}

function hide_feed($feed) {
    $hideFeeds = get_privacy("hide-feeds", array());
    if (!in_array($feed, $hideFeeds)) $hideFeeds[] = $feed;
    remove_user_saving('feed', $feed);
    save_privacy_settings(array('hide-feeds' => $hideFeeds));
}

function unhide_feed($feed) {
    $hideFeeds = get_privacy("hide-feeds", array());
    if (in_array($feed, $hideFeeds)) {
        $a = array();
        foreach($hideFeeds as $f) {
            if ($f != $feed) $a[] = $f;
        }

        save_privacy_settings(array('hide-feeds' => $a));
    };

}
/**
 * function to know if a user can edit feed
 * @param array $feed
 * @return boolean
 */
function can_edit_feed($feed) {
    $user = get_user();
    if (!is_loggedIn()) return false;
    if ($user['id'] == $feed['user_id']) return true;
    if (is_admin() or is_moderator()) return true;

    $result = array('edit' => false);
    $result = fire_hook('feed.edit.check', $result, array($feed));
    return $result['edit'];
}

/**
 * function to know if a user can edit feed
 * @param array $feed
 * @return boolean
 */
function can_delete_feed($feed) {
    $user = get_user();
    if (!is_loggedIn()) return false;
    if ($user['id'] == $feed['user_id'] or $user['id'] == $feed['to_user_id']) return true;
    if (is_admin() or is_moderator()) return true;

    $result = array('delete' => false);
    $result = fire_hook('feed.delete.check', $result, array($feed));
    return $result['delete'];
}

function can_edit_feed_privacy($feed) {
    $result = array('edit' => true);
    $result = fire_hook('feed.edit.privacy.check', $result, array($feed));
    return $result['edit'];
}

function feed_is_owner($feed) {
    if (!is_loggedIn()) return false;
    if (get_userid() != $feed['user_id']) return false;
    return true;
}

function can_share_feed($feed) {
    if (!$feed['can_share']) return false;
    if ($feed['shared'] or $feed['privacy'] == 1 or $feed['privacy'] == 4) return true;
    return false;
}

function can_pin_post($feed) {
    $user = get_user();
    if (!is_loggedIn()) return false;

    if ($feed['type'] == 'feed'  and $user['id'] == $feed['user_id']) return true;
    //if (is_admin() or is_moderator()) return true;


    $result = array('edit' => false);
    $result = fire_hook('feed.pin.check', $result, array($feed));
    return $result['edit'];
}

function feed_subscribed($feed, $userid = null) {
    return false;
}

function remove_feed($id, $feed = null) {
    $feed = ($feed) ? $feed : find_feed($id);
    if (!can_delete_feed($feed)) return false;
    if (plugin_loaded('comment')) delete_comments('feed', $id);
    if (plugin_loaded('like')) delete_likes('feed', $id);

    //delete where the post is tagged as well
    db()->query("DELETE FROM user_tags WHERE tag_id='{$id}' AND tag_type='post'");
    if (isset($feed['files']) && is_array($feed['files'])) {
        foreach($feed['files'] as $file) {
            delete_file(path($file['path']));
        }
    }
    fire_hook('feed.delete', null, array($id, $feed));
    db()->query("DELETE FROM `feeds` WHERE `feed_id`='{$id}' OR shared_id='{$id}'");
    db()->query("DELETE FROM feed_pinned WHERE feed_id='{$id}'");
    forget_cache('feed-pinned');
    return true;
}

function delete_posts($type, $id) {
    $db = db()->query("SELECT * FROM feeds WHERE type='{$type}' AND type_id='{$id}'");
    while($feed = $db->fetch_assoc()) {
        remove_feed($feed['feed_id'], $feed);
    }
    return true;
}

function save_feed($id, $text) {
    $feed = find_feed($id);
    if (!can_edit_feed($feed)) return false;
    $text = sanitizeText($text);
    db()->query("UPDATE `feeds` SET `feed_content`='{$text}', `edited`='1' WHERE `feed_id`='{$id}'");
    return true;
}

function share_feed($id) {
    $feed = find_feed($id, false);
    if (!$feed) return 0;
    $count = $feed['shared_count'] + 1;
    if ($feed['shared']) {
        $id = $feed['shared_id'];
        $sharedFeed = find_feed($id, false);
        $count = $sharedFeed['shared_count'] + 1;
    }
    //update this feed shared count
    db()->query("UPDATE `feeds` SET `shared_count`='{$count}' WHERE `feed_id`='{$id}'");

    //insert new record
    $userid = get_userid();
    $time = time();
    db()->query("INSERT INTO `feeds` (user_id,entity_id,entity_type,type,shared,shared_id,time,privacy)VALUES(
        '{$userid}','{$userid}','user','feed','1','{$id}','{$time}','1'
    )");
    fire_hook('share.feed', null, array($feed));
    return ($feed['shared']) ? '' : $count;
}

function format_feed_content($content) {
    return output_text($content);
}

function feed_process_link($link) {
    $result = false;


    //first make use of embera
    require_once(path("includes/libraries/embed/1x/autoloader.php"));
    try{
        $headers = array(
            'Referer: https://www.google.com.ng/_/chrome/newtab-serviceworker.js',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.59 Safari/537.36'
        );
        $embed = Embed\Embed::create($link, array(
            'minImageWidth' => 50,
            'minImageHeight' => 50,
            "resolver" => array(
                "options" => array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_REFERER => url(),
                )
            )
        ));
    }
    catch (Exception $e) {
        return false;
    }
    //exit(var_dump($embed));
    if ($embed) {
        $images = $embed->getImages();
        if($images) {
            foreach($embed->images as $key => $image) {
                $link_photos_dir = url().'storage/uploads/link/photos/';
                if(preg_match('/^'.preg_quote($link_photos_dir, '/').'/i', $image)) {
                    $image = $embed->images[$key];
                    break;
                } else {
                    $image = $images[0];
                }
            }
        } else {
            $image = $embed->getImage();
        }
        //$image = $embed->getImage();
        if ($image) {
            $uploader = new Uploader($image, 'image', false, true, true);
            if ($uploader->passed()) {
                $uploader->setPath('link/photos/');
                $image = $uploader->resize(600)->result();
                $image = url($image);
            }
        }
        $code = $embed->code;
        if (isSecure()) {
            $code = (preg_match("#https://#", $link)) ? $code : '';
        }
        $result = array(
            'type' => $embed->type,
            'title' => $embed->title,
            'description' => $embed->description,
            'link' => $embed->url,
            'image' => $image,
            'code' => $code,
            'provider' => $embed->providerName,
            'provider-url' => $embed->providerUrl,
            'imageWidth' => $embed->imageWidth
        );

        //print_r($result);
    }
    return $result;
}

function pin_feed($feed) {
    $type = $feed['type'];
    $typeId = $feed['type_id'];
    $feedId = $feed['feed_id'];
    if ($type == 'feed') {
        $type = 'user';
        $typeId = $feed['entity_id'];
    }

    $check = db()->query("SELECT feed_id FROM feed_pinned WHERE type='{$type}' AND type_id='{$typeId}' AND feed_id='{$feedId}'");
    if ($check->num_rows) {
        //we are removing this pin
        db()->query("DELETE FROM feed_pinned WHERE type='{$type}' AND type_id='{$typeId}'");
    } else {
        db()->query("DELETE FROM feed_pinned WHERE type='{$type}' AND type_id='{$typeId}'");
        db()->query("INSERT INTO feed_pinned (type,type_id,feed_id)VALUES('{$type}','{$typeId}','{$feedId}')");
    }
    forget_cache('feed-pinned');
    return true;
}

function is_feed_pinned($id) {
    $feeds = get_pinned_feeds();
    if (in_array($id, $feeds)) return true;
    return false;
}

function get_pinned_feed($type, $typeId) {
    $db = db()->query("SELECT feed_id FROM feed_pinned WHERE type='{$type}' AND type_id='{$typeId}' LIMIT 1");
    if ($db->num_rows) {
        $f = $db->fetch_assoc();
        return find_feed($f['feed_id']);
    }
    return false;
}
function get_pinned_feeds() {
    $cacheName = "feed-pinned";
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT feed_id FROM feed_pinned");
        $a = array();
        while($fetch = $query->fetch_assoc()) {
            $a[] = $fetch['feed_id'];
        }
        set_cacheForever($cacheName, $a);
        return $a;
    }
}
function count_total_feeds() {
    $q = db()->query("SELECT feed_id FROM feeds");
    return $q->num_rows;
}

function count_posts_in_month($n, $year) {
    $q = db()->query("SELECT * FROM feeds WHERE YEAR(timestamp)={$year} AND MONTH(timestamp)={$n}");
    return $q->num_rows;
}

function get_feelings_list() {
    return array(
        'listening-to',
        'watching',
        'feeling',
        'thinking-about',
        'reading',
        'eating',
        'drinking',
        'celebrating',
        'traveling-to',
        'exercising',
        'meeting',
        'playing',
        'looking-for',
    );
}

function get_poll_answers($id) {
    $cacheName = "poll-answers-{$id}";
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT answer_id,answer_text,voters FROM poll_answers WHERE poll_id='{$id}'");
        $results =  fetch_all($query);
        set_cacheForever($cacheName, $results);
        return $results;
    }

}

function has_submitted_poll($pollId) {
    $userid = get_userid();
    $submits = poll_submitters($pollId);
    if (in_array($userid, $submits)) return true;
    return false;
}
function poll_submitters($pollId) {
    $cacheName = "poll-submit-".$pollId;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT user_id FROM poll_results WHERE poll_id='{$pollId}'");
        $a = array();
        while($fetch = $query->fetch_assoc()) {
            $a[] = $fetch['user_id'];
        }
        set_cacheForever($cacheName, $a);
        return $a;
    }
}

function feed_submit_poll($val, $feed) {
    $userid = get_userid();
    /**
     * @var $poll_id
     * @var $answers
     * @var $answer
     */
    extract($val);

    if ($feed['is_poll'] == 1) {
        //single choice poll
        $option = $answer;


        $d = db()->query("SELECT * FROM poll_results WHERE user_id='{$userid}' AND poll_id='{$poll_id}' LIMIT 1");
        if ($d->num_rows < 1) {
            db()->query("INSERT INTO poll_results(user_id,poll_id,answer_id)VALUES('{$userid}','{$poll_id}','{$option}')");

            db()->query("UPDATE feeds SET poll_voters = poll_voters + 1 WHERE feed_id='{$poll_id}'");
            db()->query("UPDATE poll_answers SET voters = voters + 1 WHERE answer_id='{$option}'");

            forget_cache("poll-submit-".$poll_id);
            forget_cache("poll-answers-{$poll_id}");
            forget_cache("poll-answer-users-{$option}");in_poll_answer($option);//for quick refresh
        } else {
            $result = $d->fetch_assoc();
            $rId = $result['answer_id'];

            db()->query("UPDATE poll_answers SET voters = voters - 1 WHERE answer_id='{$rId}'");
            db()->query("DELETE FROM poll_results WHERE user_id='{$userid}' AND poll_id='{$poll_id}'");

            db()->query("INSERT INTO poll_results(user_id,poll_id,answer_id)VALUES('{$userid}','{$poll_id}','{$option}')");
            db()->query("UPDATE poll_answers SET voters = voters + 1 WHERE answer_id='{$option}'");

            forget_cache("poll-submit-".$poll_id);
            forget_cache("poll-answers-{$poll_id}");
            forget_cache("poll-answer-users-{$rId}");in_poll_answer($rId);
            forget_cache("poll-answer-users-{$option}");in_poll_answer($option);//for quick refresh
        }
    } else {
        //multiple choice poll
        $pollAnswers = get_poll_answers($feed['feed_id']);
        foreach($pollAnswers as $pollAnswer) {
            $option = $pollAnswer['answer_id'];
            if (isset($answers[$pollAnswer['answer_id']])) {
                //ok answer is selected
                if (!in_poll_answer($option)) {
                    //we act now to add the answer and the user
                    db()->query("INSERT INTO poll_results(user_id,poll_id,answer_id)VALUES('{$userid}','{$poll_id}','{$option}')");

                    db()->query("UPDATE feeds SET poll_voters = poll_voters + 1 WHERE feed_id='{$poll_id}'");
                    db()->query("UPDATE poll_answers SET voters = voters + 1 WHERE answer_id='{$option}'");

                    forget_cache("poll-submit-".$poll_id);
                    forget_cache("poll-answers-{$poll_id}");
                    forget_cache("poll-answer-users-{$option}");in_poll_answer($option);//for quick refresh
                }
            } else {
                //its not selected
                if (in_poll_answer($option)) {
                    //we act now to remove user
                    db()->query("UPDATE feeds SET poll_voters = poll_voters - 1 WHERE feed_id='{$poll_id}'");
                    db()->query("UPDATE poll_answers SET voters = voters - 1 WHERE answer_id='{$option}'");
                    db()->query("DELETE FROM poll_results WHERE user_id='{$userid}' AND poll_id='{$poll_id}' AND answer_id='{$option}'");
                    forget_cache("poll-answers-{$poll_id}");
                    forget_cache("poll-answer-users-{$option}");in_poll_answer($option);//for quick refresh
                }
            }
        }
    }




    return true;
}

function get_num_poll_voter_pages($id, $limit) {
    $query = db()->query("SELECT COUNT(poll_id) FROM poll_results WHERE answer_id = {$id}");
    $row = $query->fetch_row();
    $total_records = $row[0];
    $total_pages = ceil($total_records / $limit);
    return $total_pages;
}

function get_poll_answers_user($id, $limit = 3, $page = 1) {
    $start_from = ($page - 1) * $limit;
    $voters = db()->query("SELECT id, first_name, last_name, username, avatar FROM poll_results INNER JOIN users ON poll_results.user_id = users.id WHERE answer_id = '{$id}' LIMIT {$start_from}, {$limit}");
    return fetch_all($voters);
}

function get_answer_users($id) {
    $cacheName = "poll-answer-users-{$id}";
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $a = array();
        $db = db()->query("SELECT user_id FROM poll_results WHERE answer_id='{$id}'");
        while($fetch = $db->fetch_assoc()) {
            $a[] = $fetch['user_id'];
        }
        set_cacheForever($cacheName, $a);
        return $a;
    }
}

function in_poll_answer($id) {
    $ids = get_answer_users($id);
    $userid = get_userid();
    if (in_array($userid, $ids)) return true;
    return false;
}