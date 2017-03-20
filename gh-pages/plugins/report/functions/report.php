<?php
function add_report($val) {
    $expected = array(
        'link' => '',
        'type' => '',
        'reason' => ''
    );

    /**
     * @var $link
     * @var $type
     * @var $reason
     */
    extract(array_merge($expected, $val));
    $time = time();
    $userid = get_userid();
    $link = sanitizeText($link);
    $type = sanitizeText($type);
    $reason = sanitizeText($reason);
    db()->query("INSERT INTO reports (user_id,type,link,message,time)VALUES(
        '{$userid}','{$type}','{$link}','{$reason}','{$time}'
    )");

    fire_hook('report.added', null, array($val));
    return true;
}

function get_reports() {
    $query = "SELECT * FROM reports ORDER BY report_id DESC ";
    return paginate($query, 20);
}

function delete_report($id) {
    db()->query("DELETE FROM reports WHERE report_id='{$id}'");
    return true;
}