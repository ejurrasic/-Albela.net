<?php
function add_announcement($val) {
    /**
     * @var $type
     * @var $gender
     * @var $location
     * @var $role
     * @var $closed
     * @var $active
     * @var $content
     * @var $start_hour
     * @var $start_minute
     * @var $start_month
     * @var $start_day
     * @var $start_year
     */
    extract($val);

    $contentSlug = "announcemnt_".time().'_content';

    foreach($content as $langId => $t) {
        add_language_phrase($contentSlug, $t, $langId, 'announcement');
    }

    $time = time();
    $start_time = mktime(0, 0, 0, $start_month, $start_day, $start_year);
    db()->query("INSERT INTO announcements(content,type,user_group,location,gender,can_close,time,start_date,active)VALUES(
    '{$contentSlug}','{$type}','{$role}','{$location}','{$gender}','{$closed}','{$time}','{$start_time}','{$active}'
    )");
    $id = db()->insert_id;
    fire_hook("announcement.created", null, array($id));
    return true;
}

function save_announcement($val, $announcement) {
    /**
     * @var $type
     * @var $gender
     * @var $location
     * @var $role
     * @var $closed
     * @var $active
     * @var $content
     * @var $start_hour
     * @var $start_minute
     * @var $start_month
     * @var $start_day
     * @var $start_year
     */
    extract($val);

    $contentSlug = $announcement['content'];

    foreach($content as $langId => $t) {
        (phrase_exists($langId, $contentSlug)) ? update_language_phrase($contentSlug, $t, $langId, 'announcement') : add_language_phrase($contentSlug, $t, $langId, 'announcement');
    }

    $id = $announcement['id'];
    db()->query("UPDATE announcements SET type='{$type}', active='{$active}',user_group='{$role}',location='{$location}',gender='{$gender}',can_close='{$closed}' WHERE id='{$id}'");;

    return true;

}

function get_admin_announcements() {
    return paginate("SELECT * FROM announcements ORDER BY time DESC", 15);
}

function get_announcement($id) {
    $query = db()->query("SELECT * FROM announcements WHERE id='{$id}' LIMIT 1");
    return $query->fetch_assoc();
}

function get_user_announcements($limit = 10, $top = false, $all = false) {
    $time = time();
    if($all) {
        $sql = "SELECT content, time, type, id, time, can_close FROM announcements WHERE active = 1 AND start_date < ".$time." AND ((user_group = 0) ";
    } else {
        $ids = implode(',', get_hide_announcements());
        $sql = "SELECT content, time, type, id, time, can_close FROM announcements WHERE active = 1 AND start_date < ".$time." AND id NOT IN (".$ids.") AND ((user_group = 0) ";
    }
    if (is_loggedin()) {
        $user = get_user();
        $role = $user['role'];
        $location = $user['country'];
        $gender = $user['gender'];
        $sql .= "OR ((user_group = ".$role." OR user_group = 0) AND ((location = 'any' OR location = '".$location."') OR (gender = 'any' OR gender = '".$gender."'))) ";
    } else {
        $sql .= "OR (user_group='100') ";
    }
    $sql .=") ORDER BY start_date DESC";

    if ($top) {
        $query = db()->query($sql.' LIMIT 1');
        return $query->fetch_assoc();
    }
    return paginate($sql, $limit);
}

function close_announcement($id) {
    $userid = get_userid();
    $q = db()->query("SELECT userid FROM announcement_hide WHERE userid='{$userid}' AND announcement_id='{$id}' LIMIT 1");
    if ($q->num_rows < 1) {
        db()->query("INSERT INTO announcement_hide (announcement_id,userid)VALUES('{$id}','{$userid}')");
        forget_cache("announcements-{$userid}");
    }
}

function get_hide_announcements() {
    $userid = get_userid();
    $cacheName = "announcements-{$userid}";
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $q = db()->query("SELECT announcement_id FROM announcement_hide WHERE userid='{$userid}'");
        $ids = array(0);
        while($fetch = $q->fetch_assoc()) {
            $ids[] = $fetch['announcement_id'];
        }

        return $ids;
    }
}