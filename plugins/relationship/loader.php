<?php
load_functions("relationship::relationship");
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("relationship::css/relationship.css");
        register_asset("relationship::js/relationship.js");
    }
});

register_get_pager("relationship/follow", array("use" => "relationship::relationship@process_follow_pager", 'filter' => 'auth'));
register_get_pager("relationship/add/friend", array("use" => "relationship::relationship@add_friend_pager", 'filter' => 'auth'));
register_get_pager("relationship/remove/friend", array("use" => "relationship::relationship@remove_friend_pager", 'filter' => 'auth'));
register_get_pager("relationship/load/requests", array("use" => "relationship::relationship@load_friend_requests_pager", 'filter' => 'auth'));
register_get_pager("friend/requests/preload", array("use" => "relationship::relationship@preload_friend_requests_pager", 'filter' => 'auth'));
register_get_pager("friend/request/confirm", array("use" => "relationship::relationship@confirm_friend_request_pager", 'filter' => 'auth'));
register_get_pager("friend/requests", array('use' => "relationship::relationship@friend_requests_pager", 'filter' => 'auth', 'as' => 'friend-requests'));
register_get_pager("suggestions", array('use' => "relationship::relationship@suggestions_pager", 'filter' => 'auth', 'as' => 'suggestions'));
/**Notification display hook**/
register_hook("display.notification", function($notification) {
   if ($notification['type'] == 'relationship.follow') {
       return view("relationship::notifications/follow", array('notification' => $notification));
   } elseif($notification['type'] == 'relationship.confirm') {
       return view("relationship::notifications/friend", array('notification' => $notification));
   }
});

register_hook("plugin.check", function($plugin){
   if ($plugin == 'relationship') {

   }
});



register_block("relationship::suggestion-block", lang('relationship::people-suggestion'), null, array(
    'limit' => array(
        'title' => lang('relationship::people-suggestion-limit'),
        'description' => lang('relationship::people-suggestion-limit-desc'),
        'type' => 'text',
        'value' => 5
    ),
));

register_block("relationship::block/friends", lang('relationship::user-friends'), null, array(
    'limit' => array(
        'title' => lang('relationship::user-list-limit'),
        'description' => lang('relationship::user-list-limit-desc'),
        'type' => 'text',
        'value' => 6
    ),
));

register_block("relationship::block/followers", lang('relationship::user-followers'), null, array(
    'limit' => array(
        'title' => lang('relationship::user-list-limit'),
        'description' => lang('relationship::user-list-limit-desc'),
        'type' => 'text',
        'value' => 6
    ),
));

register_block("relationship::block/following", lang('relationship::user-following'), null, array(
    'limit' => array(
        'title' => lang('relationship::user-list-limit'),
        'description' => lang('relationship::user-list-limit-desc'),
        'type' => 'text',
        'value' => 6
    ),
));

if (is_loggedIn()) {
    add_menu("dashboard-main-menu", array("icon" => "<i class='ion-android-person-add'></i>", "id" => "find-friends", "title" => lang("find-friends"), "link" => url_to_pager("suggestions")));
}

register_hook('profile.started', function($user) {
    $count = (is_loggedIn() and $user['id'] != get_userid())  ? count(get_mutual_friends($user['id'])) : '';
    $count = ($count) ?"<span style='color:lightgrey;font-size:11px'>".$count . ' '.lang('mutual')."</span>"  : '';
    add_menu("user-profile", array('title' => lang('relationship::friends').' '.$count, 'link' => profile_url('friends', $user), 'id' => 'connections'));

});

register_pager("{id}/friends", array("use" => "relationship::profile@friends_pager", "as" => "profile-friends", 'filter' => 'profile'))
    ->where(array('id' => '[a-zA-Z0-9\_\-]+'));
register_pager("{id}/following", array(
    "use" => "relationship::profile@following_pager",
    "as" => "profile-following",
    'filter' => 'profile')
)->where(array('id' => '[a-zA-Z0-9\_\-]+'));

register_pager("{id}/followers", array("use" => "relationship::profile@followers_pager", "as" => "profile-followers", 'filter' => 'profile'))
    ->where(array('id' => '[a-zA-Z0-9\_\-]+'));

register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM relationship WHERE from_userid='{$userid}' OR to_userid='{$userid}'");
});

register_hook("relationship.request.list", function($user) {
    echo $user['type'] == 2 ? lang('relationship::friend-request') : null;
});

register_hook("relationship.request.confirm.script", function($user) {
    echo ($user['type'] == 2) ? 'return confirm_friend_request('.$user['id'].')' : null;
});

register_hook("relationship.request.delete.script", function($user) {
    echo $user['type'] == 2 ? 'return delete_friend_request('.$user['id'].')' : null;
});