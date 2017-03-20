<?php
load_functions("activity::activity");

register_pager("activities", array("use" => 'activity::activity@lists_pager', 'filter' => 'auth'));

register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register_asset("comment::css/comment.css");
        //register_asset("activity::js/activity.js");
    }

    db()->query("DELETE FROM user_activities WHERE  time<" . (time() - (60 * 60 * 24 * 30)) . "");
});

function activity_form_link($link, $title, $ajax = true, $lower = false) {
    $ajax = ($ajax) ? "ajax='true'" : null;
    $title = ($lower) ? strtolower($title) : $title;
    return "<a {$ajax} href='{$link}'>{$title}</a>";
}

register_hook("activity.title", function($title, $activity, $user) {
    switch($activity['type']) {
        case 'feed':
            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ". lang("activity::added-new")." ".activity_form_link($activity['link'], lang('activity::post'), true, true);
            break;
        case 'like':
            $type = $activity['title_addition'];
            $typeId = $activity['type_id'];
            $mTitle = "";
            $mLink = "";
            switch($type) {
                case 'feed':
                    $feed = find_feed($typeId);
                    if (!$feed) return "invalid";
                    $mLink = url("feed/".$feed['feed_id']);
                    $mTitle = $feed['publisher']['name']."'s ".strtolower(lang("activity::post"));
                    break;
                case 'photo':
                    $photo = find_photo($typeId);
                    if (!$photo) return "invalid";

                    $mLink = url("photo/view/".$photo['id']);
                    $mTitle = $photo['publisher']['name']."'s ".strtolower(lang("photo::photo"));
                    break;
                case 'page':
                    $page = find_page($typeId);
                    if (!$page) return "invalid";
                    $mLink = page_url(null, $page);
                    $mTitle = $page['page_title'];
                    break;
                case 'blog':
                    $blog = get_blog($typeId);
                    if (!$blog) return "invalid";
                    $mLink = url("blog/".$blog['slug']);
                    $mTitle = lang("activity::this-blog-post");
                    break;
            }

            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ". lang("activity::activity-likes")." ".activity_form_link($mLink, $mTitle, true);
            break;
        case "blog.create":
            $blog = get_blog($activity['type_id']);
            if (!$blog) return "invalid";
            $mLink = url("blog/".$blog['slug']);
            $mTitle = lang("activity::blog-post");
            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ". lang("activity::added-new")." ".activity_form_link($mLink, $mTitle, true);
            break;
        case "comment":
            $type = $activity['title_addition'];
            $typeId = $activity['type_id'];
            $mTitle = "";
            $mLink = "";
            switch($type) {
                case 'feed':
                    $feed = find_feed($typeId);
                    if (!$feed) return "invalid";
                    $mLink = url("feed/".$feed['feed_id']);
                    $mTitle = $feed['publisher']['name']."'s ".strtolower(lang("activity::post"));
                    break;
                case 'photo':
                    $photo = find_photo($typeId);
                    if (!$photo) return "invalid";

                    $mLink = url("photo/view/".$photo['id']);
                    $mTitle = $photo['publisher']['name']."'s ".strtolower(lang("photo::photo"));
                    break;
                case 'game':
                    $game = find_game($typeId);
                    if (!$game) return "invalid";
                    $mLink = game_url(null, $game);
                    $mTitle = $game['game_title'];
                    break;
                case 'blog':
                    $blog = get_blog($typeId);
                    if (!$blog) return "invalid";
                    $mLink = url("blog/".$blog['slug']);
                    $mTitle = lang("activity::this-blog-post");
                    break;
            }

            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ". lang("activity::commented-on")." ".activity_form_link($mLink, $mTitle, true);
            break;
        case "event.create":
            $event = find_event($activity['type_id']);
            if (!$event) return "invalid";
            $mLink = url("event/".$event['event_id']);
            $mTitle = lang("activity::activity-event");
            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ". lang("activity::created-new")." ".activity_form_link($mLink, $mTitle, true);
            break;
        case "game.create":
            $game = find_game($activity['type_id']);
            if (!$game) return "invalid";
            $mLink = game_url(null, $game);
            $mTitle = lang("activity::activity-game");
            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ". lang("activity::added-new")." ".activity_form_link($mLink, $mTitle, true);
            break;
        case "page.create":
            $page = find_page($activity['type_id']);
            if (!$page) return "invalid";
            $mLink = page_url(null, $page);
            $mTitle = lang("activity::activity-page");
            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ". lang("activity::added-new")." ".activity_form_link($mLink, $mTitle, true);
            break;
        case "group.create":
            $group = find_group($activity['type_id']);
            if (!$group) return "invalid";
            $mLink = group_url(null, $group);
            $mTitle = lang("activity::activity-group");
            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ". lang("activity::created-new")." ".activity_form_link($mLink, $mTitle, true);
            break;
        case "follow":
            $muser = find_user($activity['type_id']);
            if (!$muser) return "invalid";
            $mLink = profile_url(null, $muser);
            $mTitle = get_user_name($muser);
            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ".lang("activity::is-following")." ".activity_form_link($mLink, $mTitle, true);
            break;
        case 'become.friend':
            $userid = $activity['type_id'];
            if ($userid == get_userid()) return "invalid";
            $muser = find_user($userid);
            if (!$user) return "invalid";
            $mLink = profile_url(null, $muser);
            $mTitle = get_user_name($muser);
            return activity_form_link(profile_url(null, $user), get_user_name($user), true)." ".lang("activity::became-friend-with")." ".activity_form_link($mLink, $mTitle, true);
            break;
    }

    return $title;
});


register_hook("feed.added", function($id, $val) {
    /**
     * @var $privacy
     */
    extract($val);
    add_activity(url("feed/".$id), null, 'feed', $id, $privacy);
});

register_hook("like.item", function($type, $typeId) {
    $accepted = array("feed", "photo", "page", "blog");
    $privacy = 1;
    switch($type) {
        case 'feed':
            $feed = find_feed($typeId);
            if ($feed) $privacy = $feed['privacy'];
            break;
        case 'photo':
            $photo = find_photo($typeId);
            if ($photo) $privacy = $photo['privacy'];
            break;
    }
    if (in_array($type, $accepted)) add_activity(null, null, 'like', $typeId, $privacy, $type);
});

register_hook("blog.added", function($id, $val) {

    if ($val['status']) add_activity(null, null, 'blog.create', $id, 1);
});

register_hook("comment.add", function($type, $id) {
    $accepted = array("feed", "photo", "game", "blog");
    $privacy = 1;
    switch($type) {
        case 'feed':
            $feed = find_feed($id);
            if ($feed) $privacy = $feed['privacy'];
            break;
        case 'photo':
            $photo = find_photo($id);
            if ($photo) $privacy = $photo['privacy'];
            break;
    }
    if (in_array($type, $accepted)) add_activity(null, null, 'comment', $id, $privacy, $type);
});

register_hook("event.create", function($id) {
    add_activity(null, null, 'event.create', $id, 1);
});

register_hook("game.added", function($id) {
    add_activity(null, null, 'game.create', $id, 1);
});

register_hook("page.created",function($id) {
    add_activity(null, null, 'page.create', $id, 1);
});

register_hook("group.added", function($id, $val) {
    /**
     * @var $privacy
     */
    extract($val);
    if ($privacy == 1) add_activity(null, null, 'group.create', $id, $privacy);
});

register_hook("user.follow", function($from, $userid) {
    add_activity(null, null, 'follow', $userid, 1, null, $from);
});

register_hook("user.confirm-friend", function($user1, $user2) {
    add_activity(null, null, 'become.friend', $user2, 1);

    add_activity(null, null, 'become.friend', $user1, 1, null, $user2);
});

register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM user_activities WHERE user_id='{$userid}'");
});

register_block("activity::block/list", lang('activity::activities'), null, array(
        'limit' => array(
            'title' => lang('list-limit'),
            'description' => lang('list-limit-desc'),
            'type' => 'text',
            'value' => 6
        ),)
);




add_available_menu('activity::activity-log', 'activities');
