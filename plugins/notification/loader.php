<?php
load_functions("notification::notification");
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("notification::css/notification.css");
        register_asset("notification::js/notification.js");
    }
});



register_get_pager("notification/load/latest", array('use' => 'notification::notification@notification_load_pager', 'filter' => 'auth'));
register_get_pager("notification/preload", array('use' => 'notification::notification@preload_pager', 'filter' => 'auth'));
register_get_pager("notification/mark", array("use" => 'notification::notification@notification_mark_pager', 'filter' => 'auth'));
register_get_pager("notification/delete", array("use" => 'notification::notification@notification_delete_pager', 'filter' => 'auth'));
register_get_pager("notifications", array("use" => 'notification::notification@notifications_pager', 'filter' => 'auth', 'as' => 'notifications'));


register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM notifications WHERE from_userid='{$userid}' OR to_userid='{$userid}'");
});

register_hook('footer', function() {
    if(is_loggedIn() and !isMobile()) echo "<div id='notification-popup'><div id='content'></div><a onclick='return closeNotificationpopup()' class='close' href=''><i class='ion-close'></i></a></div>";
});