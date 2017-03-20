<?php
function get_membership_suscribers() {
    $type = "'recurring','one-time'";
    return paginate("SELECT * FROM users WHERE membership_type IN ({$type})");
}
function count_membership_suscribers() {
    $type = "'recurring','one-time'";
    $query = db()->query("SELECT id FROM users WHERE membership_type IN ({$type})");
    return $query->num_rows;
}
function user_need_membership() {
    $user = get_user();
    $time = time();

    if (($user['membership_type'] == 'free' or $user['membership_type'] == 'one-time')) return false;
    if (!$user['membership_type']
        or (($user['membership_type'] != 'free' or $user['membership_type'] != 'one-time') and $user['membership_expire_time'] < $time)) return true;
    return false;
}

function get_membership_plans() {
    $query = db()->query("SELECT * FROM membership_plans");
    return fetch_all($query);
}

function get_membership_plan($id) {
    $query = db()->query("SELECT * FROM membership_plans WHERE id='{$id}'");
    return $query->fetch_assoc();
}

function membership_activate($id) {
    $userid = get_userid();
    $plan = get_membership_plan($id);
    $type = $plan['type'];
    $eTime = '';
    if ($type == 'recurring') {
        $time = time();
        $ftime = 0;
        $eType = $plan['expire_type'];
        switch($eType) {
            case 'day':
                $ftime = (int) $plan['expire_no'] * 86400;
                break;
            case 'week':
                $ftime = (int) $plan['expire_no'] * 604800;
                break;
            case 'month':
                $ftime = (int) $plan['expire_no'] * 2628000;
                break;
            case 'year':
                $ftime = (int) $plan['expire_no'] * 31535965;
                break;
        }
        $eTime = $time + $ftime;
    }
    $role = $plan['user_role'];
    db()->query("UPDATE users SET membership_type='{$type}',membership_plan='{$id}',membership_expire_time='{$eTime}',role='{$role}' WHERE id='{$userid}'");

    return true;
}
function add_membership_plan($val) {
    $expected = array(
        'type' => '',
        'price' => '',
        'cycle_number' => '',
        'cycle_type' => '',
        'role' => '',
        'title' => '',
        'desc' => '',
        'recommend' => ''
    );

    /**
     * @var $type
     * @var $price
     * @var $cycle_number
     * @var $cycle_type
     * @var $role
     * @var $title
     * @var $desc
     * @var $recommend
     */
    extract(array_merge($expected, $val));

    /**
     * @var $title
     * @var $desc
     */
    extract(array_merge($expected, $val));
    $titleSlug = "membership_plan_".md5(time().serialize($val)).'_title';
    $descriptionSlug = "membership_plan_".md5(time().serialize($val))."_description";

    foreach($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'membership');
    }
    foreach($desc as $langId => $t) {
        add_language_phrase($descriptionSlug, $t, $langId, 'membership');
    }

    db()->query("INSERT INTO membership_plans (title,description,type,user_role,recommend,price,expire_no,expire_type)VALUES(
    '{$titleSlug}','{$descriptionSlug}','{$type}','{$role}','{$recommend}','{$price}','{$cycle_number}','{$cycle_type}'
    )");

    return true;
}

function save_membership_plan($val, $plan) {
    $expected = array(
        'type' => '',
        'price' => '',
        'cycle_number' => '',
        'cycle_type' => '',
        'role' => '',
        'title' => '',
        'desc' => '',
        'recommend' => ''
    );

    /**
     * @var $type
     * @var $price
     * @var $cycle_number
     * @var $cycle_type
     * @var $role
     * @var $title
     * @var $desc
     * @var $recommend
     */
    extract(array_merge($expected, $val));

    /**
     * @var $title
     * @var $desc
     */
    extract(array_merge($expected, $val));
    $titleSlug =$plan['title'];
    $descSlug = $plan['description'];
    foreach($title as $langId => $t) {
        (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'membership') : add_language_phrase($titleSlug, $t, $langId, 'membership');

    }

    foreach($desc as $langId => $t) {
        (phrase_exists($langId, $descSlug)) ? update_language_phrase($descSlug, $t, $langId, 'membership') : add_language_phrase($descSlug, $t, $langId, 'membership');

    }
    $id = $plan['id'];
    db()->query("UPDATE membership_plans SET type='{$type}',price='{$price}',user_role='{$role}',recommend='{$recommend}',expire_no='{$cycle_number}',expire_type='{$cycle_type}' WHERE id='{$id}'");

    return true;
}

function delete_membership_plan($id) {
    db()->query("DELETE FROM membership_plans WHERE id='{$id}'");
}
 