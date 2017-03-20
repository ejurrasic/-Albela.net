<?php
function close_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $announcement = get_announcement($id);
    if (!$announcement['can_close']) return false;
    return close_announcement($id);
}


function list_pager($app) {
    $app->setTitle(lang('announcement::announcements'));
    return $app->render(view('announcement::list', array('announcements' => get_user_announcements(10, false, true))));
}