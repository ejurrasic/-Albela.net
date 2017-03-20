<?php

register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("search::css/search.css");
        register_asset("search::js/search.js");
    }
});
register_get_pager("search/dropdown", array('use' => 'search::search@dropdown_search_pager'));
register_pager("search", array("use" => "search::search@search_pager", "as" => 'search'));

register_hook('feeds.query', function($type, $type_id) {
   if ($type == 'search') {
       $sqlFields = get_feed_fields();
       if (is_loggedIn()) {

           $users = array();
           if (config('relationship-method') > 1) {
               $users = array_merge($users, get_friends(get_userid()));
               if (config('relationship-method') == 3) {
                   $users = array_merge($users, get_following(get_userid()));
               }
           } else {
               $users = array_merge($users, get_following(get_userid()));
           }
           $users = implode(',', $users);
           $userid = get_userid();
           $sql = "SELECT {$sqlFields} FROM `feeds` WHERE (privacy = '1' OR (privacy='2' AND entity_type='user' AND entity_id='{$userid}') OR (privacy='2' AND entity_type='user' AND entity_id IN ({$users}))) AND feed_content LIKE '%{$type_id}%'";
       } else {
           $sql = "SELECT {$sqlFields} FROM `feeds` WHERE privacy = '1' AND feed_content LIKE '%{$type_id}%'";
       }
       //exit($sql);
       return $sql;
   }

});

add_menu_location('search-menu', 'search::search-menu');
add_available_menu('search::search', 'search', 'ion-search');
