<?php
function create_event($val) {
    /**
     * @var $category
     * @var $title
     * @var $description
     * @var $privacy
     * @var $location
     * @var $address
     * @var $start_day
     * @var $start_month
     * @var $start_year
     * @var $start_hour
     * @var $start_minute
     * @var $start_time_type
     * @var $end_day
     * @var $end_month
     * @var $end_year
     * @var $end_hour
     * @var $end_minute
     * @var $end_time_type
     */
    extract($val);
    $start_year = ($start_year) ? $start_year : date('Y');
    $end_year = ($end_year) ? $end_year : date('Y');
    $category = sanitizeText($category);
    $title  =  sanitizeText($title);
    $description = sanitizeText($description);
    $privacy = sanitizeText($privacy);
    $location = sanitizeText($location);
    $address = sanitizeText($address);
    $start_day = sanitizeText($start_day);
    $start_month = sanitizeText($start_month);
    $start_year = sanitizeText($start_year);
    $start_hour = sanitizeText($start_hour);
    $start_minute = sanitizeText($start_minute);
    $start_time_type  = sanitizeText($start_time_type);
    $end_day = sanitizeText($end_day);
    $end_month = sanitizeText($end_month);
    $end_year = sanitizeText($end_year);
    $end_hour  = sanitizeText($end_hour);
    $end_minute = sanitizeText($end_minute);
    $end_time_type = sanitizeText($end_time_type);

    $start_time = mktime($start_hour, $start_minute, 0, $start_month, $start_day, $start_year);
    $end_time = @mktime($end_hour, $end_minute, 0, $end_month, $end_day, $end_year);
    $userid = get_userid();
    $time = time();

    db()->query("INSERT INTO events(event_day,event_month,event_year,user_id,event_title,event_desc,category_id,privacy,location,address,start_time,end_time,start_time_type,end_time_type,time)VALUES(
    '{$start_day}','{$start_month}','{$start_year}','{$userid}','{$title}','{$description}','{$category}','{$privacy}','{$location}','{$address}','{$start_time}','{$end_time}','{$start_time_type}','{$end_time_type}','{$time}'
    )");
    //exit(db()->error);
    $insertId = db()->insert_id;
    fire_hook('event.create', null, $insertId);
    return $insertId;
}

function save_event($val, $eventId) {
    /**
     * @var $category
     * @var $title
     * @var $description
     * @var $location
     * @var $address
     * @var $start_day
     * @var $start_month
     * @var $start_year
     * @var $start_hour
     * @var $start_minute
     * @var $start_time_type
     * @var $end_day
     * @var $end_month
     * @var $end_year
     * @var $end_hour
     * @var $end_minute
     * @var $end_time_type
     */
    extract($val);

    $category = sanitizeText($category);
    $title  =  sanitizeText($title);
    $description = sanitizeText($description);
    $location = sanitizeText($location);
    $address = sanitizeText($address);
    $start_day = sanitizeText($start_day);
    $start_month = sanitizeText($start_month);
    $start_year = sanitizeText($start_year);
    $start_hour = sanitizeText($start_hour);
    $start_minute = sanitizeText($start_minute);
    $start_time_type  = sanitizeText($start_time_type);
    $end_day = sanitizeText($end_day);
    $end_month = sanitizeText($end_month);
    $end_year = sanitizeText($end_year);
    $end_hour  = sanitizeText($end_hour);
    $end_minute = sanitizeText($end_minute);
    $end_time_type = sanitizeText($end_time_type);

    $start_time = mktime($start_hour, $start_minute, 0, $start_month, $start_day, $start_year);
    $end_time = @mktime($end_hour, $end_minute, 0, $end_month, $end_day, $end_year);

    db()->query("UPDATE events SET category_id='{$category}',event_title='{$title}',event_desc='{$description}',location='{$location}',address='{$address}'
    ,start_time='{$start_time}',end_time='{$end_time}',start_time_type='{$start_time_type}',end_time_type='{$end_time_type}'
     ,event_day='{$start_day}',event_month='{$start_month}',event_year='{$start_year}'
     WHERE event_id='{$eventId}'");

    fire_hook('event.updated', null, array($eventId));
    return true;
}

function count_total_events() {
    $q = db()->query("SELECT event_id FROM events");
    return $q->num_rows;
}

function get_events($type, $term = null, $limit = 10, $admin = false, $category = null) {
    $sql = "SELECT * FROM events ";
    $friends = (is_loggedIn()) ? get_friends() : array();
    $friends[] = 0;
    $friends = implode(',', $friends);
    $userid = get_userid();


    switch($type) {
        case 'saved' :
            $saved = get_user_saved('event');
            $saved[] = 0;
            $saved = implode(',', $saved);
            $sql .= " WHERE event_id IN ({$saved})";
            break;
        case 'upcoming':
            $myInvites = implode(',', get_my_event_invites());
            $time = time();
            $sql .= "WHERE (user_id='{$userid}' OR (user_id IN ({$friends}) AND privacy = '0') OR event_id IN ({$myInvites}) OR privacy='1')  AND start_time > {$time}  ";
            if ($category and $category != 'all') {
                $sql .= " AND category_id='{$category}' ";
            }
            if ($term) {
                $sql .= " AND (event_title LIKE '%{$term}%' OR event_desc LIKE '%{$term}%' )";
            }
            $sql .= " ORDER BY start_time ";
            break;
        case 'invite':
            $myInvites = implode(',', get_my_event_invites());
            $sql .= "WHERE event_id IN ({$myInvites}) ORDER BY start_time ";
            break;
        case 'me':
            $sql .= "WHERE user_id='{$userid}' ";
            if ($category and $category != 'all') {
                $sql .= " AND category_id='{$category}' ";
            }
            if ($term) {
                $sql .= " AND (event_title LIKE '%{$term}%' OR event_desc LIKE '%{$term}%') ";
            }
            $sql .= " ORDER BY start_time ";
            break;
        case 'past':
            $myInvites = implode(',', get_my_event_invites());
            $time = time();
            $sql .= "WHERE (user_id='{$userid}' OR (user_id IN ({$friends}) AND privacy = '0') OR event_id IN ({$myInvites})) AND start_time < {$time}  ";
            if ($category and $category != 'all') {
                $sql .= " AND category_id='{$category}' ";
            }
            if ($term) {
                $sql .= " AND (event_title LIKE '%{$term}%' OR event_desc LIKE '%{$term}%') ";
            }
            $sql .= " ORDER BY start_time ";
            break;
        case 'search':
            $myInvites = implode(',', get_my_event_invites());
            $sql .= "WHERE (user_id='{$userid}' OR (user_id IN ({$friends}) AND privacy = '0') OR event_id IN ({$myInvites})) AND event_title LIKE '%{$term}%' ORDER BY start_time ";
            break;
        case 'admin-search':
            $sql .= "WHERE  event_title LIKE '%{$term}%' ORDER BY start_time ";
            break;
        default:
            if (!$admin) return false;
            break;
    }
    //exit($sql);
    return paginate($sql);
}

function event_get_today_birthdays() {
    $friends = get_friends();
    $friends[] = 0;
    $friends = implode(',', $friends);
    $cMonth = strtolower(date('F'));
    $cDay  = date('j');
    $q = db()->query("SELECT id,username,first_name,last_name,birth_day,birth_month,avatar,gender FROM users WHERE (id IN ({$friends}) AND birth_month='{$cMonth}' AND birth_day = '{$cDay}' AND activated='1' AND active='1')");
    $users = fetch_all($q);
    return $users;
}
function event_get_month_birthdays() {
    $days = implode(',', event_get_days());
    $friends = get_friends();
    $friends[] = 0;
    $friends = implode(',', $friends);
    $cMonth = strtolower(date('F'));
    $today = date('j');

    $q = db()->query("SELECT id,username,first_name,last_name,birth_day,birth_month,avatar,gender FROM users WHERE (id IN ({$friends}) AND birth_month='{$cMonth}' AND birth_day IN ({$days}) AND birth_day > {$today} AND activated='1' AND active='1')");
    $users = fetch_all($q);
    return $users;
}

function event_get_comingup_birthdays() {
    $results = array(

    );

    $days = array();
    $n = 1;
    for($i=1;$i<=7;$i++) {
        $day = date('j') + $i;
        $month = date('n');
        if (($day == 31 and !monthReach31($month)) or $day > 31) {
            $day = $n;
            $month = $month+1;
            if ($month > 12) {
                $month = 1;
            }
            $n++;
        }
        $days[$day] = $month;
    }
    $friends = get_friends();
    $friends[] = 0;
    $friends = implode(',', $friends);
    $sql = "SELECT id,username,first_name,last_name,birth_day,birth_month,avatar,gender FROM users WHERE (id IN ({$friends}) AND activated='1' AND active='1')";

    $sql .= "AND (";
    $ad = "";
    foreach($days as $day => $month) {
        $month = event_get_month_name($month);
        $ad .= ($ad) ? " OR (birth_day='{$day}' AND birth_month='{$month}')" : "(birth_day='{$day}' AND birth_month='{$month}')";
    }

    $sql .= $ad .')';
    //print_r($sql);
    $q = db()->query($sql);
    return fetch_all($q);
}

function monthReach31($month) {
    $months = array(1,3,5,7,8,10, 12);
    if (in_array($month, $months)) return true;
    return false;
}

function event_get_user_months_birthdays() {
    $results = array();
    $months = array();
    $currentMonth = date('n') + 1;

    for($i=1;$i<=12;$i++) {
        if ($currentMonth > 12) {
            $c = date('n') - 1;
            $y = 1;
            while($y <= $c) {
                $months[] = $y;
                $y++;
            }
            break;
        } else {
            $months[] = $currentMonth;
        }
        $currentMonth++;
    }
    $days = implode(',', event_get_days());
    $friends = get_friends();
    $friends[] = 0;
    $friends = implode(',', $friends);
    foreach($months as $month) {
        $monthName = event_get_month_name($month);
        $q = db()->query("SELECT id,username,first_name,last_name,birth_day,birth_month,avatar FROM users WHERE id IN ({$friends}) AND birth_month='{$monthName}' AND birth_day IN ({$days})  AND activated='1' AND active='1'");
        $users = fetch_all($q);
        if ($users) $results[$month] = $users;
    }

    return $results;
}

function event_get_month_name($month) {
    $months = array(
        1 => 'january',
        2 => 'february',
        3 => 'march',
        4 => 'april',
        5 => 'may',
        6 => 'june',
        7 => 'july',
        8 => 'august',
        9 => 'september',
        10 => 'october',
        11 => 'november',
        12 => 'december'
    );
    return $months[$month];
}

function event_get_days()
{
    $days = array();
    for($i=1;$i<=31;$i++) {
        $days[] = $i;
    }
    return $days;
}

function get_event_logo($event) {
    if ($event['event_cover_resized']) {
        return url_img($event['event_cover_resized']);
    } else {
        return get_avatar(200, $event['owner']);
    }
}

function get_my_event_invites() {
    $userid = get_userid();
    $q = db()->query("SELECT event_id FROM event_invites WHERE user_id='{$userid}'");
    $a = array(0);
    while($fetch = $q->fetch_assoc()) {
        $a[] = $fetch['event_id'];
    }

    return $a;
}

function find_event($id) {
    $sql = "SELECT * FROM events WHERE event_id='{$id}'";
    $query = db()->query($sql);
    return arrange_event($query->fetch_assoc());
}

function arrange_event($event) {
    if (!$event) return false;
    $category = get_event_category($event['category_id']);
    if ($category)  $event['category'] = $category;;
    $owner = find_user($event['user_id'], false);
    $event['owner'] = $owner;

    return $event;
}

function event_url($slug = null, $event = null) {
    return url_to_pager("event-profile", array('slug' => $event['event_id'])).'/'.$slug;
}

function get_event_date($event, $which = 'month', $no = 'M', $type = 'start') {
    $time = ($type == 'start') ? $event['start_time'] : $event['end_time'];
    if (!$time) return false;
    return date($no, $time);
}

function event_get_invite_friends($term = null, $limit = 20, $offset = 0) {
    $friends = get_friends();
    $friends[] = 0;
    $friends = implode(',', $friends);

    $sql = "SELECT id,first_name,last_name,avatar,username FROM users WHERE id IN ({$friends})";

    if (!$term) {
        $invited = get_event_invited(app()->profileEvent['event_id']);
        $invited[] = 0;
        $invited = implode(',', $invited);
        $sql .= " AND id NOT IN ({$invited}) ";
    }

    if ($term) $sql .= "  AND (first_name LIKE '%{$term}%' OR last_name LIKE '%{$term}%' OR username LIKE '%{$term}%' OR email_address LIKE '%{$term}%')";
    $sql .= " LIMIT {$offset},{$limit}";
    $query = db()->query($sql);
    return fetch_all($query);
}

function get_event_invited($id) {
    $cacheName = "event-invites-" .$id;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT user_id FROM event_invites WHERE event_id='{$id}'");
        $ids = array();
        while($fetch = $query->fetch_assoc()) {
            $ids[] = $fetch['user_id'];
        }

        set_cacheForever($cacheName, $ids);
        return $ids;
    }
}
function event_invite_user($eventId, $userid) {
    $invited = get_event_invited($eventId);
    if (!in_array($userid, $invited)) {
        db()->query("INSERT INTO event_invites (event_id,user_id)VALUES('{$eventId}','{$userid}')");
        forget_cache("event-invites-" .$eventId);
        return true;
    }

    return false;
}

function event_rsvp($eventId, $rsvp) {
    $userid = get_userid();
    if ($rsvp > 0) {
        $event = find_event($eventId);
        send_notification($event['user_id'], 'event.rsvp', $eventId, array('rsvp' => $rsvp));
    }
    if (event_already_invited($eventId, $userid)) {
        db()->query("UPDATE event_invites SET rsvp='{$rsvp}' WHERE event_id='{$eventId}' AND user_id='{$userid}'");
    } else {
        db()->query("INSERT INTO event_invites (event_id,user_id,rsvp)VALUES('{$eventId}','{$userid}','{$rsvp}')");
    }

    forget_cache("event-invites-" .$eventId);
    return true;
}

function count_event_invited($id) {
    return count(get_event_invited($id));
}

function count_event_going($eventId) {
    $query = db()->query("SELECT user_id FROM event_invites WHERE event_id='{$eventId}' AND rsvp='1'");
    return $query->num_rows + 1; //we added the hoster
}

function count_event_maybe($eventId) {
    $query = db()->query("SELECT user_id FROM event_invites WHERE event_id='{$eventId}' AND rsvp='2'");
    return $query->num_rows;
}

function get_event_my_rsvp($eventId) {
    $userid = get_userid();
    $query = db()->query("SELECT rsvp FROM event_invites WHERE event_id='{$eventId}' AND user_id='{$userid}'");
    if ($query->num_rows) {
        $fetch = $query->fetch_assoc();
        return $fetch['rsvp'];
    }

    return 0;
}

function event_already_invited($eventId, $userid) {
    $invited = get_event_invited($eventId);

    if (in_array($userid, $invited)) {
        return true;
    }

    return false;
}

function get_event_cover($event = null, $original = true) {
    $default = img("images/cover.jpg");
    if (!$original and !empty($event['event_cover_resized'])) return url_img($event['event_cover_resized']);
    if (!empty($event['event_cover'])) return url_img($event['event_cover']);
    return ($original) ? '' : $default;
}

function get_event_details($index, $event = null) {
    $event = ($event) ? $event : app()->profileEvent;
    if (isset($event[$index])) return $event[$index];
    return false;
}

function is_event_admin($event) {
    if (!is_loggedIn()) return false;
    if (is_admin()) return true;
    if (get_userid() == $event['user_id']) return true;
    return false;
}

function can_create_event() {
    return user_has_permission('can-create-event');
}

function update_event_details($fields, $eventId) {
    $sqlFields = "";
    foreach($fields as $key => $value) {
        $value = sanitizeText($value);
        $sqlFields .= (empty($sqlFields)) ? "`{$key}`='{$value}'" : ",`{$key}`='{$value}'";
    }
    db()->query("UPDATE `events` SET {$sqlFields} WHERE `event_id`='{$eventId}'");
    //exit(db()->error);
    fire_hook("event.updated", array($eventId));
}

function event_add_category($val) {
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     * @var $desc
     */
    extract(array_merge($expected, $val));
    $titleSlug = "event_category_".md5(time().serialize($val)).'_title';

    foreach($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'event');
    }


    $time = time();
    $order = db()->query('SELECT id FROM event_categories');
    $order = ($order) ? $order->num_rows : 1;
    $query = db()->query("INSERT INTO `event_categories`(
            `title`,`category_order`) VALUES(
            '{$titleSlug}','{$order}'
            )
        ");

    return true;
}

function save_event_category($val, $category) {
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     */
    extract(array_merge($expected, $val));
    $titleSlug = $category['title'];

    foreach($title as $langId => $t) {
        (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'event') : add_language_phrase($titleSlug, $t, $langId, 'event');
    }
    return true;
}

function get_event_categories() {
    $query = db()->query("SELECT * FROM `event_categories` ORDER BY `category_order` ASC");
    return fetch_all($query);
}

function get_event_category($id) {
    $query = db()->query("SELECT * FROM `event_categories` WHERE `id`='{$id}'");
    return $query->fetch_assoc();
}

function delete_event_category($id, $category) {
    delete_all_language_phrase($category['title']);

    db()->query("DELETE FROM `event_categories` WHERE `id`='{$id}'");

    return true;
}

function update_event_category_order($id, $order) {
    db()->query("UPDATE `event_categories` SET `category_order`='{$order}' WHERE  `id`='{$id}'");
}

function delete_event($event) {
    //delete all rsvp
    $eventId = $event['event_id'];
    db()->query("DELETE FROM event_invites WHERE event_id='{$eventId}'");

    //delete cover images
    if ($event['event_cover']) delete_file(path($event['event_cover']));
    if ($event['event_cover_resized']) delete_file(path($event['event_cover_resized']));

    //now delete the event itself
    db()->query("DELETE FROM events WHERE event_id='{$eventId}'");

    delete_posts('event', $eventId);

    return true;
}

