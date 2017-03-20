<?php
function is_following($userid, $loggedinUser = null) {
    $loggedinUser = ($loggedinUser) ? $loggedinUser : get_userid();
    $followings = get_following($loggedinUser);

    if (in_array($userid, $followings)) return true;
    return false;
}

function get_following($userid = null) {
    $userid =   ($userid) ? $userid : get_userid();
    $cacheName = "user-following-".$userid;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {

        $users = array();
        $query = db()->query("SELECT `to_userid` FROM `relationship` WHERE `type`='1' AND `from_userid`='{$userid}'");
        if ($query) {
            foreach(fetch_all($query) as $result) {
                $users[] = $result['to_userid'];
            }
            return $users;
        }
        set_cacheForever($cacheName, $users);
        return $users;
    }
}

function get_followers($userid = null) {
    $userid =   ($userid) ? $userid : get_userid();
    $cacheName = "user-followers-".$userid;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {

        $users = array();
        $query = db()->query("SELECT `from_userid` FROM `relationship` WHERE `type`='1' AND `to_userid`='{$userid}'");
        if ($query) {
            foreach(fetch_all($query) as $result) {
                $users[] = $result['from_userid'];
            }
            return $users;
        }
        set_cacheForever($cacheName, $users);
        return $users;
    }
}

/**
 * process follow
 * @param string $type
 * @param int $userid
 * @return boolean
 */
function process_follow($type, $userid, $notify = true, $fromUserid = null) {
    $fromUserid = ($fromUserid) ? $fromUserid : get_userid();
    if ($type == 'unfollow') {
        db()->query("DELETE FROM `relationship` WHERE type='1' AND `from_userid`='{$fromUserid}' AND `to_userid`='{$userid}'");
        fire_hook("user.unfollow", null, array($fromUserid, $userid));
    } else {
        $time = time();
        db()->query("INSERT INTO `relationship` (from_userid,to_userid,type,confirm,time)VALUES(
            '{$fromUserid}','{$userid}','1','1','{$time}'
        )");
        fire_hook("user.follow", null, array($fromUserid, $userid));
        if ($notify and plugin_loaded('notification') and user_privacy("receive-follow-notification", true, find_user($userid))) {
            load_functions("notification::notification");
            send_notification_privacy('notify-following-you', $userid, 'relationship.follow', $userid, array(), null, null, $fromUserid);
        }
    }
    forget_cache("user-following-".$fromUserid);
    forget_cache("user-followers-".$userid);
}

function friend_status($userid) {
    if (!is_loggedIn()) return false;
    $loggedUser = get_userid();
    $friends = get_friends($loggedUser);
    if (in_array($userid, $friends)) return 2;
    $inRequestFriends = get_requested_friends($loggedUser);
    if (in_array($userid, $inRequestFriends)) return 1;
    //let check if this user already sent a request to this loggedin user too
    $toRequestFriends = get_requested_friends($userid);
    if (in_array($loggedUser, $toRequestFriends)) return 3;
    return '0';
}

function get_friends($userid = null) {
    $userid =   ($userid) ? $userid : get_userid();
    if (is_array($userid)) $userid = $userid['id'];
    $cacheName = "user-friends-".$userid;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    }

    $query = db()->query("SELECT from_userid,to_userid FROM relationship WHERE type='2' AND confirm='1' AND (from_userid='{$userid}' OR to_userid='{$userid}') ORDER BY relationship_id DESC");
    if ($query) {
        $users = array();
        while($fetch = $query->fetch_assoc()) {
            $users[] = ($fetch['from_userid'] == $userid) ? $fetch['to_userid'] : $fetch['from_userid'];
        }
        set_cacheForever($cacheName, $users);
        return $users;
    }
    return array();
}

function get_requested_friends($userid) {
    $cacheName = "user-request-friends-".$userid;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    }

    $query = db()->query("SELECT from_userid,to_userid FROM relationship WHERE type='2' AND confirm='0' AND (from_userid='{$userid}')");
    if ($query) {
        $users = array();
        foreach(fetch_all($query) as $result) {
            $users[] = ($result['from_userid'] == $userid) ? $result['to_userid'] : $result['from_userid'];
        }
        set_cacheForever($cacheName, $users);
        return $users;
    }
    return array();
}

function get_friend_requests($dropdown = false) {
    $userid = get_userid();
    if ($dropdown) {
        $sql = "SELECT * FROM `relationship` INNER JOIN `users` ON relationship.from_userid=users.id WHERE `to_userid`='{$userid}' ";
        $sql .= " AND confirm='0' AND (type='2' ";
        //$sql = fire_hook("get.friend.requests", $sql, array());
        $sql .= ")";

        $query = db()->query($sql." ORDER BY time desc LIMIT 5 ");
        return fetch_all($query);
    } else {
        $sql = "SELECT * FROM `relationship` INNER JOIN `users` ON relationship.from_userid=users.id WHERE `to_userid`='{$userid}' ";
        $sql .= " AND confirm='0' AND (type='2' ";
        $sql = fire_hook("get.friend.requests", $sql, array());
        $sql .= ")";

        return paginate($sql." ORDER BY time desc ", 10);
    }
}

function preload_friend_requests($ids) {
    $query = db()->query("SELECT * FROM `relationship` INNER JOIN `users` ON relationship.from_userid=users.id WHERE relationship_id IN ({$ids}) ORDER BY time desc ");
    return fetch_all($query);
}

function count_friend_requests() {
    $userid = get_userid();
    $query = db()->query("SELECT `relationship_id` FROM `relationship` WHERE `to_userid`='{$userid}' AND confirm='0'");
    if ($query) return $query->num_rows;
    return 0;
}

function confirm_friend_request($userid) {
    $loginUserid = get_userid();
    $status = friend_status($userid);
    if ($status != 2) {
        db()->query("UPDATE `relationship` SET `confirm`='1' WHERE to_userid='{$loginUserid}' AND from_userid='{$userid}'");
        fire_hook("user.confirm-friend", null, array($loginUserid, $userid));
        forget_cache("user-friends-".$userid);
        forget_cache("user-friends-".$loginUserid);
        forget_cache("user-request-friends-".$loginUserid);
        forget_cache("user-request-friends-".$userid);

        send_notification($userid, 'relationship.confirm');

        $privacy = get_privacy('email-notification', 1, $userid);
        if (config('enable-email-notification', true) and $privacy) {
            $mailer = mailer();
            $user = find_user($userid);
            if (!user_is_online($user)) {
                $mailer->setAddress($user['email_address'], get_user_name($user))->template("friend-acceptance", array(
                    'link' => url('friend/requests'),
                    'fullname' => get_user_name(),
                ));
            }
            $mailer->send();
        }
        //we nned to follow automatically if the method is 3
        if (!is_following($userid)) {
            process_follow('follow', $userid, false);
        }
    }

    return 1;
}

function add_friend($userid) {
    if (!is_loggedIn() and friend_status($userid) != 0) return false;
    $time = time();
    $loggedUser = get_userid();

    $query = db()->query("SELECT * FROM relationship WHERE type='2'  AND ((`from_userid`='{$userid}' AND `to_userid`='{$loggedUser}') OR (`from_userid`='{$loggedUser}' AND `to_userid`='{$userid}'))");

    if ($query->num_rows > 0) return true;

    db()->query("INSERT INTO `relationship` (from_userid,to_userid,type,confirm,time)VALUES(
            '{$loggedUser}','{$userid}','2','0','{$time}'
        )");

    //we nned to follow automatically if the method is 3
    if (!is_following($userid)) {
        process_follow('follow', $userid, false);
    }
    //send push notification
    pusher()->sendMessage($userid, 'friend-request', array(db()->insert_id));

    //let send mail if notification is enabled and the user can receive notification
    $privacy = get_privacy('email-notification', 1, $userid);
    if (config('enable-email-notification', true) and $privacy) {
        $mailer = mailer();
        $user = find_user($userid);
        if (!user_is_online($user)) {
            $mailer->setAddress($user['email_address'], get_user_name($user))->template("friend-request", array(
                'link' => url('friend/requests'),
                'fullname' => get_user_name(),
            ));
        }
        $mailer->send();
    }


    fire_hook("user.add-friend", null, array($loggedUser, $userid));
    forget_cache("user-friends-".$userid);
    forget_cache("user-friends-".$loggedUser);
    forget_cache("user-request-friends-".$loggedUser);
    return 1;
}

function remove_friend($userid) {
    if (!is_loggedIn() and friend_status($userid) == 0) return false;
    $lUserid = get_userid();
    db()->query("DELETE FROM `relationship` WHERE (`from_userid`='{$userid}' AND `to_userid`='{$lUserid}') OR (`from_userid`='{$lUserid}' AND `to_userid`='{$userid}') ");

    fire_hook("user.remove-friend", null, array($lUserid, $userid));
    forget_cache("user-friends-".$userid);
    forget_cache("user-friends-".$lUserid);
    forget_cache("user-request-friends-".$lUserid);
    forget_cache("user-request-friends-".$userid);
    if (is_following($userid)) {
        process_follow('unfollow', $userid, false);
    }

    if (is_following($lUserid, $userid)) {
        process_follow('unfollow', $lUserid, false, $userid);
    }
    return 1;
}

function relationship_valid($userid, $type, $toUserid = null) {
    if (!is_loggedIn()) return false;
    $toUserid = ($toUserid) ? $toUserid : get_userid();
    if ($type == 1) {
        $followers = get_followers($userid);
        if (isset($followers[$toUserid]) or in_array($toUserid, $followers)) return true;
    } else {
        $friends = get_friends($userid);
        if (isset($friends[$toUserid]) or in_array($toUserid, $friends)) return true;
    }

    return false;
}

function get_friends_of_friend($userid) {
    $friends = get_friends($userid);
    $users = array();
    foreach($friends as $id) {
        $users = array_merge($users, get_friends($id));
    }
    return $users;
}

function get_following_following($refId) {
    $following = get_following($refId);
    $users = array();
    foreach($following as $id) {
        $users = array_merge($users, get_following($id));
    }
    return $users;
}

function relationship_suggest($limit, $refId = null) {
    $ignoredUsers = mostIgnoredUsers();
    $refId = ($refId) ? $refId : get_userid();

    $whereClause = "";

    $ignoredUsers = array_merge($ignoredUsers, get_friends($refId));
    $ignoredUsers = array_merge($ignoredUsers, get_requested_friends($refId));
    $friendsFriends = get_friends_of_friend($refId);
    if ($friendsFriends) {
        $friendsFriends = implode(',', $friendsFriends);
        $whereClause .= "id IN({$friendsFriends}) ";
    }

    //$followersFollowing = get_following_following($refId);
    $ignoredUsers = array_merge($ignoredUsers, get_following($refId));

    $userCountry = get_user_data('country');
    $userCity = get_user_data('city');
    $userState = get_user_data('state');
    $whereClause .= ($whereClause) ? " OR `country`='{$userCountry}' OR `city`='{$userCity}' OR `state`='{$userState}' OR avatar !=''": "`country`='{$userCountry}' OR `city`='{$userCity}' OR `state`='{$userState}' OR avatar !=''";
    $whereClause = fire_hook('users.suggestion.sql', $whereClause);
    $ignoredUsers = implode(',', array_merge(array($refId), $ignoredUsers));
    $fields = get_users_fields();

    $query = "SELECT {$fields} FROM `users` WHERE `id`NOT IN({$refId}) AND ({$whereClause}) AND id NOT IN ({$ignoredUsers}) AND activated=1 ORDER BY rand()";
    //exit($query);
    return paginate($query, $limit);
}

function list_connections($users, $limit = 10) {
    $users = implode(',', $users);
    $fields = get_users_fields();
    if (!$users) return false;
    $query = "SELECT {$fields} FROM users WHERE id IN ({$users})";
    return paginate($query, $limit);
}

function get_mutual_friends($userid) {
    $loggedInFriends = get_friends();
    $thisUserFriends = get_friends($userid);
    $mutual = array();
    if (is_array($thisUserFriends)) {
        foreach($thisUserFriends as $f) {
            if (in_array($f, $loggedInFriends) and $f != get_userid()) $mutual[] = $f;
        }
    }
    return $mutual;
}
 