<?php
load_functions('lastvisitor::lastvisitor');

register_asset("lastvisitor::css/display.css");

register_pager('{id}/lastvisitor', array('use' => 'lastvisitor::lastvisitor@lastvisitor_pager', 'as' => 'lastvisitor', "filter" => "auth"))
    ->where(array('id' => '[a-zA-Z0-9\_\-]+'));

register_hook("privacy-settings", function() {
    echo view('lastvisitor::privacy');
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'profile.view') {
        return view('lastvisitor::notifications/profile_view', array('notification' => $notification, 'data' => unserialize($notification['data'])));
        delete_notification($notification['notification_id']);
    }
});

register_hook("profile.started", function($viewuser) {
    if (is_loggedIn()  && $viewuser['id'] != get_userid()) //app()->profileUser['id'])
    {
        $vieweruser = get_user();
        $datetimes = time();//date("Y-m-d H:m:s");
        $vieweduserid = $viewuser['id'];
        $vieweruserid = $vieweruser['id'];



        $datetime1 = isset($_SESSION['$vieweruserid']) ? $_SESSION['$vieweruserid'] : 0;
        $datetime2 = time();
        $elapsed = $datetime2 - $datetime1;
        $min = $elapsed / 60;

        $profileview = isset($_SESSION['profile.view']) ? $_SESSION['profile.view'] : "";

        if($profileview != $vieweruserid)
        {
            $_SESSION['profile.view'] = $vieweruserid;
            $_SESSION['$vieweruserid'] = time();
            send_notification($vieweduserid, 'profile.view', $vieweruserid, '', 'once');
        }
        else if($profileview == $vieweruserid && $min >= 5)
        {
            $_SESSION['profile.view'] = $vieweruserid;
            $_SESSION['$vieweruserid'] = time();
            send_notification($vieweduserid, 'profile.view', $vieweruserid, '', 'again');
        }

        if(isset($vieweruser['gender']) && ($vieweruser['gender'] != '' || $vieweruser['gender'] != null) && ($vieweruser['avatar'] != '' || $vieweruser['avatar'] != null))
        {
            insert_view_visitors($vieweruserid, $vieweduserid, $datetimes, $viewuser['gender'], '1');
        }
        else if(isset($vieweruser['gender']) && ($vieweruser['gender'] == '' || $vieweruser['gender'] == null) && ($vieweruser['avatar'] != '' || $vieweruser['avatar'] != null))
        {
            insert_view_visitors($vieweruserid, $vieweduserid, $datetimes, false, '1');
        }
        else if(isset($vieweruser['gender']) && ($vieweruser['gender'] != '' || $vieweruser['gender'] != null) && ($vieweruser['avatar'] == '' || $vieweruser['avatar'] == null))
        {
            insert_view_visitors($vieweruserid, $vieweduserid, $datetimes, $viewuser['gender'], '-1');
        }
        else if(isset($vieweruser['gender']) && ($vieweruser['gender'] == '' || $vieweruser['gender'] == null) && ($vieweruser['avatar'] == '' || $vieweruser['avatar'] == null))
        {
            insert_view_visitors($vieweruserid, $vieweduserid, $datetimes, false, '-1');
        }
        else
        {
            insert_view_visitors($vieweruserid, $vieweduserid, $datetimes);
        }
    }
});
