<?php
load_functions("notification::notification");

function notification_load_pager($app) {
    CSRFProtection::validate(false);
    pusher()->reset('notification');
    delete_old_notifications();
    return view("notification::display", array("notifications" => get_notifications(10)->results()));
}

function notification_mark_pager() {
    CSRFProtection::validate(false);
    mark_notification_read(input('id'), input('type'));
}

function notification_delete_pager() {
    CSRFProtection::validate(false);
    return delete_notification(input('id'));
}

function notifications_pager($app) {
    pusher()->reset('notification');
    $app->setTitle(lang('notification::notifications'));
    delete_old_notifications();
    return $app->render(view("notification::lists", array('notifications' => get_notifications())));
}

function preload_pager($app) {
    CSRFProtection::validate(false);
    $ids = input('ids');
    return view("notification::display", array("notifications" => preload_notifications($ids)));
}
 