<?php
function add_activity($link = '', $title = '', $type = '', $typeId = '', $privacy = 1, $titleA = '', $userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    $check = db()->query("SELECT id FROM user_activities WHERE user_id='{$userid}'
    AND link='{$link}' AND title='{$title}' AND title_addition='{$titleA}' AND
    type='{$type}' AND type_id='{$typeId}' AND privacy='{$privacy}'
    ");
    if ($check->num_rows) {
        $fetch = $check->fetch_assoc();
        $id = $fetch['id'];
        $time = time();
        db()->query("UPDATE user_activities SET time='{$time}' WHERE id='{$id}'");
        return true;
    }
    $time = time();
    db()->query("INSERT INTO user_activities (user_id,link,title,title_addition,type,type_id,privacy,time)VALUES(
    '{$userid}','{$link}','{$title}','{$titleA}','{$type}','{$typeId}','{$privacy}','{$time}'
    )");

    fire_hook("user.activity", array($link, $title,$type, $typeId, $privacy,$titleA,$userid));
    return true;
}

function getActivities($limit = 10) {
    $userid = get_userid();
    $sql = "SELECT id,user_id,link,title,title_addition,type,type_id,privacy,time FROM user_activities WHERE user_id='{$userid}' ";
    $users = array(0);
    $followings = array_merge($users, get_following($userid));
    $followings = implode(',', $followings);
    $sql .= " OR (privacy='1' AND user_id IN ({$followings})) ";

    $friends = array_merge($users, get_friends($userid));
    $friends = implode(',', $friends);
    $sql .= " OR ((privacy='1' OR privacy='2') AND user_id IN ({$friends})) ";
    $mostIgnoreUsers = implode(',', mostIgnoredUsers());
    $sql .= " AND user_id NOT IN ({$mostIgnoreUsers})";
    $sql .= " ORDER BY time DESC";

    return paginate($sql, $limit);
}