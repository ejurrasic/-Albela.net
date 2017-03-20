<?php
function group_add($val) {
    /**
     * @var $title
     * @var $name
     * @var $description
     * @var $privacy
     */
    extract($val);
    $add = ($privacy == 2) ? 2 : 1;
    $userid = get_userid();
    $title = sanitizeText($title);
    //$name = unique_slugger($title, 'groups', 'group_id', 'group_title', 'group_name');
    $description = sanitizeText($description);
    $privacy = sanitizeText($privacy);
    db()->query("INSERT INTO groups (group_title, group_name, group_description, user_id, who_can_add_member, privacy)VALUES('".$title."', '".$name."', '".$description."', '".$userid."', '".$add."', '".$privacy."')");
    $groupId = db()->insert_id;
    //auto add creator as member of the group
    group_add_member($groupId);
    fire_hook('group.added', null, array($groupId, $val));
    return $groupId;
}

function save_group_settings($val, $groupId) {
    update_group_details($val, $groupId);
}

function get_group_fields() {
    return "group_id,featured,group_title,group_name,group_description,user_id,who_can_add_member,privacy,who_can_post,moderators,group_created_time,group_logo,group_cover,group_cover_resized";
}

function find_group($id, $few = false) {
    $fields = get_group_fields();
    if ($few) $fields = "group_id,group_title,group_name";
    $q = db()->query("SELECT {$fields} FROM groups WHERE group_id='{$id}' OR group_name='{$id}'");
    return $q->fetch_assoc();
}

function group_url($slug = null, $group = null) {
    $group = ($group) ? $group : app()->profileGroup;
    return url_to_pager("group-profile", array('slug' => $group['group_name'])).'/'.$slug;
}

function get_group_cover($group = null, $original = true) {
    $default = img("images/cover.jpg");
    if (!$original and !empty($group['group_cover_resized'])) return url_img($group['group_cover_resized']);
    if (!empty($group['group_cover'])) return url_img($group['group_cover']);
    return ($original) ? '' : $default;
}

function get_group_logo($size, $group = null) {
    $avatar = $group['group_logo'];
    if ($avatar) {
        return url(str_replace('%w', $size, $avatar));
    } else {

        return $image  = img("images/page-avatar.png");
    }
}

function get_group_details($index, $group = null) {
    $group = ($group) ? $group : app()->profileGroup;
    if (isset($group[$index])) return $group[$index];
    return false;
}

function delete_group($group) {
    $groupId = $group['group_id'];

    if ($group['group_cover']) delete_file(path($group['group_cover']));
    if ($group['group_logo']) delete_file(path($group['group_logo']));
    if ($group['group_cover_resized']) delete_file(path($group['group_cover_resized']));

    db()->query("DELETE FROM group_members WHERE member_group_id='{$groupId}'");
    //delete its posts too
    delete_posts('group', $groupId);
    db()->query("DELETE FROM groups WHERE group_id='{$groupId}'");

    return true;

}

function update_group_details($fields, $groupId) {
    $sqlFields = "";
    foreach($fields as $key => $value) {
        $value = sanitizeText($value);
        $sqlFields .= (empty($sqlFields)) ? "`{$key}`='{$value}'" : ",`{$key}`='{$value}'";
    }
    db()->query("UPDATE `groups` SET {$sqlFields} WHERE `group_id`='{$groupId}'");
    //exit(db()->error);
    fire_hook("group.updated", array($groupId));
}

function make_group_moderator($group, $uid) {
    if (is_group_moderator($group, $uid)) return true;
    $moderators = ($group['moderators']) ? perfectUnserialize($group['moderators']) : array();
    $moderators[] = $uid;
    $moderators = perfectSerialize($moderators);
    $groupId = $group['group_id'];
    db()->query("UPDATE groups SET moderators='{$moderators}' WHERE group_id='{$groupId}'");
    //exit($groupId);
    //send notification of this to the user
    send_notification($uid, 'group.role', $groupId);
    return true;
}

function remove_group_moderator($group, $uid) {
    if (!is_group_moderator($group, $uid)) return true;
    $moderators = ($group['moderators']) ? perfectUnserialize($group['moderators']) : array();
    $newModerators = array();
    foreach($moderators as $u) {
        if ($u != $uid) $newModerators[] = $u;
    }
    $moderators = perfectSerialize($newModerators);
    $groupId = $group['group_id'];
    db()->query("UPDATE groups SET moderators='{$moderators}' WHERE group_id='{$groupId}'");
    return true;
}

function is_group_admin($group, $userid = null, $admin = true) {
    $userid = ($userid) ? $userid : get_userid();
    if ($admin and  is_admin()) return true;
    if ($userid == $group['user_id']) return true;
    return false;
}

function is_group_moderator($group, $userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    $moderators = ($group['moderators']) ? perfectUnserialize($group['moderators']) : array();
    if (in_array($userid, $moderators)) return true;
    return false;
}

function can_join_group($group) {
    return true;
}

function group_can_post($group = null) {
    if (!is_loggedIn()) return false;
    $group = ($group) ? $group : app()->profileGroup;
    $add = $group['who_can_post'];
    if ($add == 1 and is_group_member($group['group_id']))  return true;
    if (is_group_admin($group) or ($add == 2 and is_group_moderator($group))) return true;
    if ($add == 3 and is_group_admin($group)) return true;
    return false;
}

function group_can_add_member($group) {
    if (!is_loggedIn()) return false;
    $add = $group['who_can_add_member'];

    if ($add == 1)  return true;
    if ($add == 2 and is_group_moderator($group)) return true;
    if (is_group_admin($group)) return true;
    return false;
}

function group_add_member($groupId, $userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    if (is_group_member($groupId, $userid)) return true;
    db()->query("INSERT INTO group_members (member_id,member_group_id)VALUES('{$userid}','{$groupId}')");
    forget_cache("group-members-".$groupId);
    forget_cache('group-joined-'.$userid);
    return true;
}

function group_remove_member($groupId, $userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    if (!is_group_member($groupId, $userid)) return true;
    db()->query("DELETE FROM group_members WHERE member_group_id='{$groupId}' AND member_id='{$userid}'");
    forget_cache("group-members-".$groupId);
    forget_cache('group-joined-'.$userid);
    return true;
}

function is_group_member($groupId, $userid = null) {
    if (!is_loggedIn()) return false;
    $userid = ($userid) ? $userid : get_userid();
    $members = get_group_members_id($groupId);
    if (in_array($userid, $members)) return true;
    return false;
}

function get_group_members_id($groupId) {
    $cacheName = "group-members-".$groupId;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $q = db()->query("SELECT member_id FROM group_members WHERE member_group_id='{$groupId}'");
        $r = array();
        while($fetch = $q->fetch_assoc()) {
            $r[] = $fetch['member_id'];
        }
        set_cacheForever($cacheName, $r);
        return $r;
    }
}

function get_group_members($groupId, $limit = 10) {
    $membersId = get_group_members_id($groupId);
    $membersId[] = 0;
    $membersId = implode(',', $membersId);
    //$q = db()->query();
    return paginate("SELECT id,first_name,last_name,avatar,username FROM users WHERE id IN ({$membersId})", $limit);
}

function get_groups($type, $term = null, $limit = 10, $filter = 'all') {
    $fields = get_group_fields();
    $sql = "SELECT {$fields} FROM groups ";
    $userid = get_userid();
    if ($type == 'yours') {

        $sql .= " WHERE user_id='{$userid}' ";
        if ($term) {
            $sql .= " AND (group_title LIKE '%{$term}%' OR group_description LIKE '%{$term}%') ";
        }

        if ($filter and $filter == 'featured') {
            $sql .= " AND featured='1' ";
        }
        $sql .= " ORDER BY group_id DESC";
    } elseif($type == 'saved') {
        $saved = get_user_saved('group');
        $saved[] = 0;
        $saved = implode(',', $saved);
        $sql .= " WHERE group_id IN ({$saved}) ";
        if ($term) {
            $sql .= " AND (group_title LIKE '%{$term}%' OR group_description LIKE '%{$term}%') ";
        }

        if ($filter and $filter == 'featured') {
            $sql .= " AND featured='1' ";
        }
        $sql .= " ORDER BY group_id DESC";
    }
    elseif($type == 'member') {
        $groupIds = get_joined_groups();
        $groupIds[] = 0;
        $groupIds = implode(',', $groupIds);
        $sql .= " WHERE user_id !='{$userid}' AND group_id IN ({$groupIds}) ";
        if ($term) {
            $sql .= " AND (group_title LIKE '%{$term}%' OR group_description LIKE '%{$term}%') ";
        }

        if ($filter and $filter == 'featured') {
            $sql .= " AND featured='1' ";
        }
        $sql .= " ORDER BY group_id DESC";
    } elseif($type == 'profile') {
        $groupIds = get_joined_groups($term);
        $groupIds[] = 0;
        $groupIds = implode(',', $groupIds);
        $sql .= " WHERE privacy='1' AND (user_id='{$term}' OR group_id IN ({$groupIds})) ";
    }
    elseif ($type == 'recommend') {
        if ($filter == 'top') {
            $sql = "SELECT {$fields},(SELECT SUM(member_id) FROM group_members WHERE member_group_id=group_id) as members FROM groups ";
        }
        $friendsGroups = array(0);
        $groupIds = get_joined_groups();
        $groupIds[] = 0;
        foreach(get_friends() as $user) {
            $friendsGroups = array_merge($friendsGroups, get_joined_groups($user));
        }
        $friendsGroups = implode(',', $friendsGroups);
        $groupIds = implode(',', $groupIds);
        $sql .= " WHERE privacy = '1' ";
        if ($term) {
            $sql .= " AND (group_title LIKE '%{$term}%' OR group_description LIKE '%{$term}%') ";
        }

        if ($filter and $filter == 'featured') {
            $sql .= " AND featured='1' ";
        }
        if ($filter == 'top') {
            $sql .= " ORDER BY members DESC";
        } else {
            $sql .= " ORDER BY group_id DESC";
        }

    } elseif ($type == 'search') {
        $groupIds = get_joined_groups();
        $groupIds[] = 0;
        $groupIds = implode(',', $groupIds);
        $sql .= " WHERE (privacy='1' AND (group_title LIKE '%{$term}%' OR group_description LIKE '%{$term}%' OR group_name LIKE '%{$term}%')) OR (
        privacy = '2' AND group_id IN ({$groupIds}) AND (group_title LIKE '%{$term}%' OR group_description LIKE '%{$term}%' OR group_name LIKE '%{$term}%')
        ) ";
    }

    return paginate($sql, $limit);
}

function get_joined_groups($userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    $cacheName = 'group-joined-'.$userid;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $q = db()->query("SELECT member_group_id FROM group_members WHERE member_id='{$userid}'");
        $a = array();
        while($fetch = $q->fetch_assoc()) {
            $a[] = $fetch['member_group_id'];
        }
        set_cacheForever($cacheName, $a);
        return $a;
    }
}

function count_total_groups() {
    $q = db()->query("SELECT * FROM groups ");
    return $q->num_rows;
}

function count_groups_in_month($n, $year) {
    $q = db()->query("SELECT * FROM groups WHERE YEAR(timestamp)={$year} AND MONTH(timestamp)={$n}");
    return $q->num_rows;
}


function get_all_groups() {
    $sql = "SELECT * FROM groups ";

    $term = input('term', false);
    if ($term) {
        $sql .= " WHERE group_title LIKE '%{$term}%' OR group_description LIKE '%{$term}%' OR group_name LIKE '%{$term}%'";
    }
    $sql .= "ORDER BY group_id DESC";
    return paginate($sql);
}
