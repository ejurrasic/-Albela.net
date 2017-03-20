<?php
load_functions("like::like");

register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("like::css/like.css");
        register_asset("like::js/like.js");
    }
});

register_hook("footer", function() {
   echo view('like::modal');
});
register_get_pager("like/item", array('use' => 'like::like@like_item_pager', 'filter' => 'auth'));
    register_get_pager("like/react", array('use' => 'like::like@react_pager', 'filter' => 'auth'));
register_get_pager("like/load/people", array('use' => 'like::like@load_people_pager'));

register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM likes WHERE user_id='{$userid}'");
});