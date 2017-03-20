<?php
load_functions('event::event');
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset("event::css/event.css");
        register_asset("event::js/event.js");

    }
});

register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => 'Event Permissions',
        'description' => '',
        'roles' => array(
            'can-create-event' => array('title' => lang('event::can-create-event'), 'value' => 1),

        )
    );
    return $roles;
});


register_hook('feeds.query', function($type, $typeId) {
    if ($type == 'event') {
        $sqlFields = get_feed_fields();
        $sql = "SELECT {$sqlFields} FROM `feeds` WHERE ";
        $sql .= " type='event' AND type_id='{$typeId}' ";
        $pinnedPosts = get_pinned_feeds();
        $pinnedPosts[] = 0;
        $pinnedPosts = implode(',', $pinnedPosts);
        $sql .= " AND feed_id NOT IN ({$pinnedPosts})";
        return $sql;
    }
});

register_hook('feed.edit.privacy.check', function($result, $feed) {
    if($feed['type'] == 'event') {
        $result['edit'] = false;
    }
    return $result;
});

register_hook('feed.edit.check', function($result, $feed) {
    if($feed['type'] == 'event') {
        $event =  (isset($feed['event'])) ? $feed['event'] : find_event($feed['type_id']);
        $feed['event'] = $event;
        if (is_event_admin($event)) $result['edit'] = true;
    }
    return $result;
});

register_hook('feed.pin.check', function($result, $feed) {
    if($feed['type'] == 'event') {
        $event =  (isset($feed['event'])) ? $feed['event'] : find_event($feed['type_id']);
        $feed['event'] = $event;
        if (is_event_admin($event)) $result['edit'] = true;
    }
    return $result;
});
register_hook('feed-title', function($feed) {
    if ($feed['type'] == 'event' and !isset(app()->profileEvent)) {
        $event = find_event($feed['type_id'], true);
        echo "<i class='ion-arrow-right-c'></i> "."<a ajax='true' href='".event_url(null, $event)."'>".$event['event_title']."</a>";
    }
});

register_hook('feed.added', function($feedId, $val) {
    if($val['type'] == 'event') {
        $event = find_event($val['type_id']);
        if ($event['user_id'] != get_userid())send_notification($event['user_id'], 'event.post', $event['event_id']);
    }
});

register_hook('search-dropdown-start', function($content, $term) {
    $events = get_events('search', $term, 5);
    if ($events->total) {
        $content.= view('event::search/dropdown', array('events' => $events));
    }
    return $content;
});

register_hook('register-search-menu', function($term) {
    add_menu("search-menu", array('title' => lang('event::events'), 'id' => 'event', 'link' => form_search_link('event', $term)));
});

register_hook('search-result', function($content, $term, $type) {
    if ($type == 'event') {
        get_menu('search-menu', 'event')->setActive();
        $content = view('event::browse', array('events' => get_events('search', $term)));
    }
    return $content;
});

register_filter("event-profile", function($app) {
    $slug = segment(1);

    $event = find_event($slug);

    if (!$event) return false;

    if ($event['privacy'] == 1) {

        if (!is_event_admin($event) and !event_already_invited($slug, get_userid())) return false;
    }

    $app->profileEvent = $event;
    //$app->pageUser = find_user($page['page_user_id']);
    $app->setTitle($event['event_title'])->setLayout("event::profile/layout");

    //register page profile menu
    //add_menu("page-profile", array('id' => 'timeline', 'title' => lang('page::timeline'), 'link' => page_url('', $page)));
    //add_menu("page-profile", array('id' => 'about', 'title' => lang('page::about'), 'link' => page_url('about', $page)));
    //add_menu("page-profile", array('id' => 'photos', 'title' => lang('page::photos'), 'link' => page_url('photos', $page)));
    //add_menu("page-profile-more", array('id' => 'likes', 'title' => lang('page::likes'), 'link' => page_url('likes', $page)));
    fire_hook('event.profile.started', null, array($event));

    return true;
});

register_hook("admin-started", function() {
    add_menu("admin-menu", array("id" => "events-manager", "title" => lang("event::events-manager"), "link" => "#", "icon" => "ion-android-calendar"));
    get_menu("admin-menu", "plugins")->addMenu(lang("event::events-manager"), '#', 'events-manager');
    get_menu("admin-menu", "plugins")->findMenu("events-manager")->addMenu(lang("event::events"), url_to_pager('admin-event-lists'));
    get_menu("admin-menu", "plugins")->findMenu("events-manager")->addMenu(lang("event::categories"), url_to_pager("admin-event-categories"), 'list');
    get_menu("admin-menu", "plugins")->findMenu("events-manager")->addMenu(lang("event::add-category"), url_to_pager("admin-event-category-add"), 'add-category');

    /**get_menu("admin-menu", "events-manager")->addMenu(lang("event::run-notifications"), '#', 'admin-events-notification');
    get_menu("admin-menu", "events-manager")->findMenu("admin-events-notification")->addMenu(lang("event::today-events"), url_to_pager("event-run").'?type=event&web=true', 'events');
    get_menu("admin-menu", "events-manager")->findMenu("admin-events-notification")->addMenu(lang("event::remind-events"), url_to_pager("event-run").'?type=event&when=before&web=true', 'remind-events');
    get_menu("admin-menu", "events-manager")->findMenu("admin-events-notification")->addMenu(lang("event::remind-birthdays"), url_to_pager("event-run").'?type=birthday&when=before&web=true', 'remind-birthday');
    get_menu("admin-menu", "events-manager")->findMenu("admin-events-notification")->addMenu(lang("event::today-birthdays"), url_to_pager("event-run").'?type=birthday&web=true', 'today');
    //get_menu("admin-menu", "events-manager")->addMenu(lang("settings"), url('admincp/plugin/settings/game'), "admin-game-settings");
    **/
    register_block_page('events', lang('event::events-pages'));
});

register_pager("admincp/events", array('use' => "event::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admin-event-lists'));
register_pager("admincp/event/categories", array('use' => "event::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admin-event-categories'));
register_pager("admincp/event/category/add", array('use' => "event::admincp@add_category_pager", 'filter' => 'admin-auth', 'as' => 'admin-event-category-add'));
register_pager("admincp/event/category/manage", array('use' => "event::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admin-event-manage-category'));


register_post_pager("event/change/cover", array('use' => 'event::profile@upload_cover_pager', 'filter' => 'user-auth'));
register_pager("event/cover/reposition", array('use' => 'event::profile@reposition_cover_pager', 'filter' => 'user-auth'));
register_pager("event/cover/remove", array('use' => 'event::profile@remove_cover_pager', 'filter' => 'user-auth'));

if (is_loggedIn()) {
    if (user_has_permission('can-create-event')) register_pager("event/create", array('use' => "event::event@create_event_pager", 'filter' => 'user-auth', 'as' => 'event-create'));
}

register_pager("event/invite/user", array('use' => "event::profile@invite_user_pager", 'filter' => 'user-auth'));
register_pager("event/invite/search", array('use' => "event::profile@search_invite_user_pager", 'filter' => 'user-auth'));
register_pager("event/rsvp", array('use' => "event::profile@rsvp_pager", 'filter' => 'user-auth'));
register_pager("events", array('use' => "event::event@events_pager", 'as' => 'events'));
register_pager("event/delete/{id}", array('use' => "event::event@event_delete_pager", 'as' => 'event-delete', 'filter' => 'user-auth'))->where(array('id' => '[0-9]+'));
register_pager("events/run", array('use' => "event::event@events_run_pager", 'as' => 'event-run'));

//frontend pager registration
register_hook('system.started', function() {
    register_pager("event/{slug}", array('use' => "event::profile@event_profile_pager", 'filter' => 'event-profile', 'as' => 'event-profile'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    //register_pager("event/{slug}/play", array('use' => "game::profile@game_play_profile_pager", 'filter' => 'game-profile', 'as' => 'game-profile-play'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    register_pager("event/{slug}/edit", array('use' => "event::profile@event_edit_profile_pager", 'filter' => 'event-profile', 'as' => 'event-profile-edit'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    //register_pager("{slug}/photos", array('use' => "page::profile@page_photos_profile_pager", 'filter' => 'page-profile', 'as' => 'page-profile-photos'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
});

if (is_loggedIn()) {
    //add_menu("dashboard-main-menu", array("icon" => "<i class='ion-android-calendar'></i>", "id" => "events", "title" => lang("event::events"), "link" => url("events")));
    //add_menu("dashboard-menu", array("icon" => "<i class='ion-android-calendar'></i>", "id" => "events", "title" => lang("event::manage-events"), "link" => url('events')));
}

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'event.rsvp') {
        $event = find_event($notification['type_id']);
        if ($event) {
            $data = unserialize($notification['data']);
            return view("event::notifications/rsvp", array('notification' => $notification, 'event' => $event, 'rsvp' => $data['rsvp']));
        } else{
            delete_notification($notification['notification_id']);
        }

    } elseif ($notification['type'] == 'event.events') {
        $event = find_event($notification['type_id']);
        if ($event) {
            $data = unserialize($notification['data']);
            return view("event::notifications/events", array('notification' => $notification, 'event' => $event, 'when' => $data['when']));
        } else{
            delete_notification($notification['notification_id']);
        }
    } elseif ($notification['type'] == 'event.invite') {
        $event = find_event($notification['type_id']);
        if ($event) {
            $data = unserialize($notification['data']);
            return view("event::notifications/invite", array('notification' => $notification, 'event' => $event, 'event' => $data['event']));
        } else{
            delete_notification($notification['notification_id']);
        }
    } elseif ($notification['type'] == 'event.birthday') {
        return view("event::notifications/birthday", array('notification' => $notification, 'when' => $notification['type_id']));
    } elseif ($notification['type'] == 'event.post') {
        $event = find_event($notification['type_id']);
        if ($event) {
            return view("event::notifications/post", array('notification' => $notification, 'event' => $event));
        } else{
            delete_notification($notification['notification_id']);
        }
    }
});

register_hook('admin.statistics', function($stats) {
    $stats['events'] = array(
        'count' => count_total_events(),
        'title' => lang('event::events'),
        'icon' => 'ion-android-calendar',
        'link' => url_to_pager('admin-event-lists'),
    );
    return $stats;
});



register_block("event::block/birthdays", lang('event::birthdays-coming-up'), null,array(
        'limit' => array(
            'title' => lang('list-limit'),
            'description' => lang('list-limit-desc'),
            'type' => 'text',
            'value' => 6
        ),)
);

register_hook('user.delete', function($userid) {
    $d = db()->query("SELECT * FROM events WHERE user_id='{$userid}'");
    while($event = $d->fetch_assoc()) {
        delete_event($event);
    }
});

register_hook('saved.content', function($content, $type) {
    add_menu('saved', array('title' => lang('event::events'). ' <span style="color:lightgrey;font-size:12px">'.count(get_user_saved('event')).'</span>', 'link' => url('saved/events'), 'id' => 'events'));
    if ($type == 'events') {
        $content = view('event::saved/content', array('events' => get_events('saved')));
    }

    return $content;
});


add_available_menu('event::events', 'events', 'ion-android-calendar');
