<?php
function list_pager($app) {
    delete_old_notifications();
    $limit = input("limit", 10);
    $page = input('page', 1);
    $result = array();
    $notifications = get_notifications($limit);

    foreach($notifications->results() as $notification) {
        mark_notification_seen($notification['notification_id']);
        $dNotify =  api_arrange_notification($notification);
        if ($dNotify) $result[] = $dNotify;
    }

    return json_encode($result);
}

function unread_pager($app) {
    $limit = input("limit", 10);
    $page = input('page', 1);
    $result = array();
    $notifications = getUnreadNotifications($limit);

    foreach($notifications->results() as $notification) {
        //mark_notification_seen($notification['notification_id']);
        $dNotify =  api_arrange_notification($notification);
        if ($dNotify) $result[] = $dNotify;
    }

    return json_encode($result);
}

function getUnreadNotifications($limit = 10) {
    $limit = ($limit) ? $limit  : config('notification-list-limit', 10);
    $userid = get_userid();
    $fields = "notification_id,from_userid,to_userid,type,type_id,title,content,data,seen,mark_read,time,id,avatar,first_name,last_name,username,gender";
    return paginate("SELECT {$fields} FROM notifications INNER JOIN `users` ON notifications.from_userid=users.id WHERE `to_userid`='{$userid}' AND seen='0' ORDER BY `time` DESC", $limit);
}

function delete_pager($app) {
    delete_notification(input('id'));
    return json_encode(array('status' => 1));
}