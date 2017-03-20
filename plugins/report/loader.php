<?php
load_functions("report::report");
register_pager("report", array('use' => 'report::report@report_pager', 'filter' => 'auth'));

register_pager("admincp/reports", array('use' => "report::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-reports'));
register_pager("admincp/reports/delete", array('use' => "report::admincp@delete_report_pager", 'filter' => 'admin-auth', 'as' => 'admincp-reports-delete'));

register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register_asset("comment::css/comment.css");
        register_asset("report::js/report.js");
    }
});

register_hook("admin-started", function() {

    get_menu("admin-menu", "cms")->addMenu(lang("report::reports"), url_to_pager("admincp-reports"), "reports");

});

register_hook("footer", function() {
    echo view('report::modal');
});

register_hook("feed.menu", function($feed) {
   if (is_loggedIn() and !feed_is_owner($feed)) {
       echo view('report::link', array('type' => 'post','link' => url_to_pager('view-post', array('id' => $feed['feed_id']))));
   }
});

register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM reports WHERE user_id='{$userid}'");
});