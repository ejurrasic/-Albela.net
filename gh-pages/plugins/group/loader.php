<?php
load_functions('group::group');
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register_asset("group::css/group.css");
        register_asset("group::js/group.js");
    }
});

register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => 'Group Permissions',
        'description' => '',
        'roles' => array(
            'can-create-group' => array('title' => lang('group::can-create-group'), 'value' => 1),

        )
    );
    return $roles;
});

register_hook('username.check', function($result, $value) {
    $game = find_group($value);
    if ($game) $result = false;
    return $result;
});

register_pager("group/create", array('use' => "group::group@create_group_pager", 'filter' => 'user-auth', 'as' => 'group-create'));
register_pager("groups", array('use' => "group::group@manage_group_pager", 'filter' => 'user-auth', 'as' => 'group-manage'));
register_post_pager("group/change/cover", array('use' => 'group::profile@upload_cover_pager', 'filter' => 'user-auth'));
register_pager("group/cover/reposition", array('use' => 'group::profile@reposition_cover_pager', 'filter' => 'user-auth'));
register_pager("group/cover/remove", array('use' => 'group::profile@remove_cover_pager', 'filter' => 'user-auth'));
register_pager("group/change/logo", array('use' => 'group::profile@change_logo_pager', 'filter' => 'user-auth'));
register_pager("group/member/role", array('use' => 'group::profile@member_role_pager', 'filter' => 'user-auth'));
register_pager("group/add/member", array('use' => 'group::profile@add_member_pager', 'filter' => 'user-auth'));

register_pager("group/join", array('use' => 'group::profile@join_pager', 'filter' => 'user-auth'));

register_hook('system.started', function() {
    register_pager("{slug}", array('use' => "group::profile@group_profile_pager", 'filter' => 'group-profile', 'as' => 'group-profile'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{slug}/edit", array('use' => "group::profile@group_profile_edit_pager", 'filter' => 'group-profile', 'as' => 'group-profile-edit'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{slug}/members", array('use' => "group::profile@group_profile_members_pager", 'filter' => 'group-profile', 'as' => 'group-profile-members'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    //register_pager("{slug}/about", array('use' => "page::profile@page_about_profile_pager", 'filter' => 'page-profile', 'as' => 'page-profile-about'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    //register_pager("{slug}/photos", array('use' => "page::profile@page_photos_profile_pager", 'filter' => 'page-profile', 'as' => 'page-profile-photos'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
});

register_filter("group-profile", function($app) {
    $slug = segment(0);

    $group = find_group($slug);
    if (!$group) return false;
    if ($group['privacy'] == 2 and !is_group_member($group['group_id'])) return false;
    $app->profileGroup = $group;
    $app->groupUser = find_user($group['user_id']);
    $app->setTitle($group['group_title'])->setLayout("group::profile/layout");

    //register page profile menu
    add_menu("group-profile", array('id' => 'posts', 'title' => lang('group::discussion'), 'link' => group_url('', $group)));
    add_menu("group-profile", array('id' => 'members', 'title' => lang('group::members'), 'link' => group_url('members', $group)));
        //add_menu("page-profile-more", array('id' => 'likes', 'title' => lang('page::likes'), 'link' => page_url('likes', $page)));
    fire_hook('group.profile.started', null, array($group));

    return true;
});

register_hook('feeds.query', function($type, $typeId) {
    if ($type == 'group') {
        $sqlFields = get_feed_fields();
        $sql = "SELECT {$sqlFields} FROM `feeds` WHERE ";
        $sql .= " type='group' AND type_id='{$typeId}' ";
        $pinnedPosts = get_pinned_feeds();
        $pinnedPosts[] = 0;
        $pinnedPosts = implode(',', $pinnedPosts);
        $sql .= " AND feed_id NOT IN ({$pinnedPosts})";
        return $sql;
    }
});

register_hook('feed.edit.check', function($result, $feed) {
    if($feed['type'] == 'group') {
        $group =  (isset($feed['group'])) ? $feed['group'] : find_group($feed['type_id']);
        $feed['group'] = $group;
        if (is_group_admin($group, false, false)) $result['edit'] = true;
    }
    return $result;
});

register_hook('feed.pin.check', function($result, $feed) {
    if($feed['type'] == 'group') {
        $group =  (isset($feed['group'])) ? $feed['group'] : find_group($feed['type_id']);
        $feed['group'] = $group;
        if (is_group_admin($group, false, false)) $result['edit'] = true;
    }
    return $result;
});



if (config('enable-group-posts-in-timeline', true)) {
    register_hook('user.feeds.query', function($sql) {
        $groups = get_joined_groups();
        $groups[] = 0;
        $groupsId = implode(',', $groups);
        $sql .= " OR (type='group' AND type_id IN ({$groupsId})) ";
        return $sql;
    });
}

register_hook('feed.edit.privacy.check', function($result, $feed) {
    if($feed['type'] == 'group') {
        $result['edit'] = false;
    }
    return $result;
});
register_hook('feed-title', function($feed) {
    if ($feed['type'] == 'group' and !isset(app()->profileGroup)) {
        $group = find_group($feed['type_id'], true);
        echo "<i class='ion-arrow-right-c'></i> "."<a ajax='true' href='".group_url(null, $group)."'>".$group['group_title']."</a>";
    }
});
register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'group.role') {
        $group = find_group($notification['type_id']);
        if ($group) return view("group::notifications/role", array('notification' => $notification, 'group' => $group));
        delete_notification($notification['notification_id']); //ensure deletion of this notification
    } elseif($notification['type'] == 'group.add.member') {
        $group = find_group($notification['type_id']);
        if ($group) return view("group::notifications/member", array('notification' => $notification, 'group' => $group));
        delete_notification($notification['notification_id']); //ensure deletion of this notification
    } elseif ($notification['type'] == 'group.post') {
        $group = find_group($notification['type_id']);
        if ($group) {
            return view("group::notifications/post", array('notification' => $notification, 'group' => $group));
        } else {
            delete_notification($notification['notification_id']);
        }
    }
});


register_hook('search-dropdown-start', function($content, $term) {
    $groups = get_groups('search', $term, 5);
    if ($groups->total) {
        $content.= view('group::search/dropdown', array('groups' => $groups));
    }
    return $content;
});

register_hook('register-search-menu', function($term) {
    add_menu("search-menu", array('title' => lang('group::groups'), 'id' => 'group', 'link' => form_search_link('group', $term)));
});

register_hook('search-result', function($content, $term, $type) {
    if ($type == 'group') {
        get_menu('search-menu', 'group')->setActive();
        $content = view('group::search/page', array('groups' => get_groups('search', $term)));
    }
    return $content;
});

register_hook("admin-started", function() {
    get_menu('admin-menu', 'plugins')->addMenu(lang("group::groups-manager"), url('admincp/groups'), "groups-manager");
});

register_pager("admincp/groups", array('use' => "group::group@admin_group_pager", 'filter' => 'admin-auth', 'as' => 'admin-group-lists'));
register_pager("group/delete/{id}", array('use' => "group::group@group_delete_pager", 'as' => 'group-delete', 'filter' => 'user-auth'))->where(array('id' => '[0-9]+'));
register_pager("admincp/group/edit/{id}", array('use' => "group::group@group_admin_edit_pager", 'as' => 'group-admin-edit', 'filter' => 'admin-auth'))->where(array('id' => '[0-9]+'));

//page blocks
register_hook('admin-started', function() {
    register_block_page('group-profile', lang('group::group-profile'));
    register_block_page('groups', lang('group::groups'));

});

register_block("group::block/suggestion", lang('group::group-suggestions'), null,array(
        'limit' => array(
            'title' => lang('list-limit'),
            'description' => lang('list-limit-desc'),
            'type' => 'text',
            'value' => 6
        ),)
);

register_block("group::block/profile", lang('group::user-profile-groups'), null, array(
        'limit' => array(
            'title' => lang('list-limit'),
            'description' => lang('list-limit-desc'),
            'type' => 'text',
            'value' => 6
        ),)
);



register_hook('admin.statistics', function($stats) {
    $stats['groups'] = array(
        'count' => count_total_groups(),
        'title' => lang('group::groups'),
        'icon' => 'ion-ios-people-outline',
        'link' => '#',
    );
    return $stats;
});
register_hook('admin.charts', function($result, $months, $year) {
    $c = array(
        'name' => lang('group::groups'),
        'points' => array()
    );


    foreach($months as $name => $n) {
        $c['points'][$name] = count_groups_in_month($n, $year);

    }

    $result['charts']['members'][] = $c;


    return $result;
});

register_hook('user.delete', function($userid) {
    $d = db()->query("SELECT * FROM groups WHERE user_id='{$userid}'");
    while($group = $d->fetch_assoc()) {
        delete_group($group);
    }

    db()->query("DELETE FROM group_members WHERE member_id='{$userid}'");
});

register_hook('saved.content', function($content, $type) {
    add_menu('saved', array('title' => lang('group::groups'). ' <span style="color:lightgrey;font-size:12px">'.count(get_user_saved('group')).'</span>', 'link' => url('saved/groups'), 'id' => 'groups'));
    if ($type == 'groups') {
        $content = view('group::saved/content', array('groups' => get_groups('saved')));
    }

    return $content;
});

register_pager("{id}/groups", array("use" => "group::user-profile@groups_pager", "as" => "profile-groups", 'filter' => 'profile'))
    ->where(array('id' => '[a-zA-Z0-9\_\-]+'));

register_hook('profile.started', function($user) {
    add_menu('user-profile-more', array('title' => lang('group::groups'), 'as' => 'groups', 'link' => profile_url('groups', $user)));
});

add_available_menu('group::groups', 'groups', 'ion-ios-people');


register_hook('find.feed', function($result) {
    if($result['feed']['type'] == 'group' && !is_group_member($result['feed']['type_id'])) {
        $result['status'] = false;
    }
    return $result;
});

register_hook('feed.exclude.type', function($result) {
    $result[] = 'group';
    return $result;
});

register_pager("group/ajax", array('as' => 'group-ajax', 'use' => 'group::ajax@ajax_pager'));

register_hook('feed.added', function($feed_id, $val) {
    if($val['type'] == 'group') {
        $group = find_group($val['type_id']);
        if ($group['user_id'] != get_userid()) {
            send_notification($group['group_id'], 'group.post', $group['group_id']);
        }
    }
});