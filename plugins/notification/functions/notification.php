<?php
function count_notifications() {
    $userid = get_userid();
    $query = db()->query("SELECT notification_id FROM notifications WHERE `to_userid`='{$userid}' AND `seen`='0'");
    if ($query) return $query->num_rows;
    return 0;
}

function send_notification($userid, $type, $type_id = null, $data = array(), $title = null, $content = null, $fromUserid = null) {
    $fromUserid = ($fromUserid) ? $fromUserid : get_userid();
    $data = serialize($data);
    $time = time();
    $query = db()->query("INSERT INTO `notifications` (from_userid,to_userid,type,type_id,title,content,data,time)VALUES(
    '{$fromUserid}','{$userid}','{$type}','{$type_id}','{$title}','{$content}','{$data}','{$time}'
    )");
    fire_hook("notification.send", null, array($userid, $fromUserid, $type, $type_id, $data, $title, $content, $fromUserid,));
    pusher()->sendMessage($userid, 'notification', array(db()->insert_id));
    return true;
}

function send_notification_privacy($p,$userid, $type, $type_id = null, $data = array(), $title = null, $content = null, $fromUserid = null ){
    $privacy = get_privacy($p, 1, $userid);
    if ($privacy) {
        return send_notification($userid,$type, $type_id, $data, $title, $content, $fromUserid);
    }
    return false;
}

function get_notifications($limit = null) {
    $limit = ($limit) ? $limit  : config('notification-list-limit', 10);
    $userid = get_userid();
    $fields = "notification_id,from_userid,to_userid,type,type_id,title,content,data,seen,mark_read,time,id,avatar,first_name,last_name,username,gender";
    return paginate("SELECT {$fields} FROM notifications INNER JOIN `users` ON notifications.from_userid=users.id WHERE `to_userid`='{$userid}' ORDER BY `time` DESC", $limit);
}

function mark_notification_seen($id) {
    db()->query("UPDATE `notifications` SET `seen`='1' WHERE `notification_id`='{$id}'");
    fire_hook("notification.seen", null, array($id));
    return true;
}

function mark_notification_read($id, $type) {
    db()->query("UPDATE `notifications` SET `mark_read`='{$type}' WHERE `notification_id`='{$id}'");
    fire_hook("notification.seen", null, array($id, $type));
    return true;
}

function delete_notification($id) {
    db()->query("DELETE FROM  `notifications`  WHERE `notification_id`='{$id}'");
    fire_hook("notification.deleted", null, array($id));
    return true;
}

function preload_notifications($ids) {
    $fields = "notification_id,from_userid,to_userid,type,type_id,title,content,data,seen,mark_read,time,id,avatar,first_name,last_name,username,gender";
    $query = db()->query("SELECT {$fields} FROM notifications INNER JOIN `users` ON notifications.from_userid=users.id WHERE notification_id IN ({$ids}) ORDER BY `time` DESC");
    return fetch_all($query);
}

function delete_old_notifications() {
    db()->query("DELETE FROM notifications WHERE  time<" . (time() - (60 * 60 * 24 * 5)) . " AND seen>0");
}