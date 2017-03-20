<?php
load_functions("blog::blog");
register_asset("blog::css/blog.css");
register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('blog::blog-permissions'),
        'description' => '',
        'roles' => array(
            'can-create-blog' => array('title' => lang('blog::can-create-blog'), 'value' => 1),

        )
    );
    return $roles;
});
register_pager("blogs", array('use' => 'blog::blog@blog_pager', 'as' => 'blogs'));
register_pager("blog/add", array('use' => 'blog::blog@add_blog_pager', 'filter' => 'auth', 'as' => 'blog-add'));
register_pager("blog/manage", array('use' => 'blog::blog@manage_pager', 'as' => 'blog-manage', 'filter' => 'auth'));
register_pager("blogs/api", array('use' => 'blog::blog@blog_api_pager'));
register_pager("blog/{slugs}", array('use' => 'blog::blog@blog_page_pager', 'as'=> 'blog-page'))->where(array('slugs' => '[a-zA-Z0-9\-\_]+'));


register_pager("admincp/blogs", array('use' => "blog::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-blogs'));
register_pager("admincp/blog/add", array('use' => "blog::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-blog-add'));
register_pager("admincp/blog/manage", array('use' => "blog::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-blog-manage'));
register_pager("admincp/blog/categories", array('use' => "blog::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admincp-blog-categories'));
register_pager("admincp/blog/categories/add", array('use' => "blog::admincp@categories_add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-blog-categories-add'));
register_pager("admincp/blog/category/manage", array('use' => "blog::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admincp-blog-manage-category'));


register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset("help::css/help.css");
        register_asset("help::js/help.js");
    }
});

register_hook("admin-started", function() {

    add_menu("admin-menu", array('icon' => 'ion-document-text', "id" => "admin-blogs", "title" => lang('blog::manage-blogs'), "link" => '#'));
    get_menu("admin-menu", "plugins")->addMenu(lang('blog::blogs-manager'), '#', 'admin-blogs');
    get_menu("admin-menu", "plugins")->findMenu('admin-blogs')->addMenu(lang('blog::lists'), url_to_pager("admincp-blogs"), "manage");
    get_menu("admin-menu", "plugins")->findMenu('admin-blogs')->addMenu(lang('blog::add-new-blog'), url_to_pager("admincp-blog-add"), "add");
    get_menu("admin-menu", "plugins")->findMenu('admin-blogs')->addMenu(lang('blog::manage-categories'), url_to_pager("admincp-blog-categories"), "categories");

});

register_hook('admin.statistics', function($stats) {
    $stats['blogs'] = array(
        'count' => count_total_blogs(),
        'title' => lang('blog::blogs'),
        'icon' => 'ion-document-text',
        'link' => url_to_pager('admincp-blogs'),
    );
    return $stats;
});

register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'blog') {
        $blog = get_blog($typeId);
        $subscribers = get_subscribers($type, $typeId);
        foreach($subscribers as $userid) {
            if ($userid != get_userid()) {
                send_notification_privacy('notify-site-comment',$userid, 'blog.comment', $typeId, $blog, null, $text);
            }
        }

    }
});

register_hook("like.item", function($type, $typeId, $userid) {
    if ($type == 'blog') {
        $blog = get_blog($typeId);
        if ($blog['user_id'] and $blog['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $blog['user_id'], 'blog.like', $typeId, $blog);
        }
    } elseif($type == 'comment') {
        $comment = find_comment($typeId, false);
        if ($comment and $comment['user_id'] != get_userid()) {
            if ($comment['type'] == 'blog') {
                $blog = get_blog($comment['type_id']);
                send_notification_privacy('notify-site-like', $comment['user_id'], 'blog.like.comment', $comment['type_id'], $blog);
            }
        }
    }
});

register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'blog') {
        $blog = get_blog($typeId);
        $subscribers = get_subscribers($type, $typeId);
        if(!in_array($blog['user_id'], $subscribers)) {
            $subscribers[] = $blog['user_id'];
        }
        foreach($subscribers as $userid) {
            if ($userid != get_userid()) {
                send_notification_privacy('notify-site-comment',$userid, 'blog.comment', $typeId, $blog, null, $text);
            }
        }

    }
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'blog.like') {
        return view("blog::notifications/like", array('notification' => $notification, 'blog' => unserialize($notification['data'])));
        delete_notification($notification['notification_id']);
    }
    elseif($notification['type'] == 'blog.like.comment') {
        return view("blog::notifications/like-comment", array('notification' => $notification, 'blog' => unserialize($notification['data'])));
        delete_notification($notification['notification_id']);
    }
    elseif($notification['type'] == 'blog.comment') {
        return view("blog::notifications/comment", array('notification' => $notification, 'blog' => unserialize($notification['data'])));
        delete_notification($notification['notification_id']);
    }
});

add_menu_location('blogs-menu', lang('blog::blogs-menu'));
add_available_menu('blog::blogs', 'blogs', 'ion-android-clipboard');


register_pager("{id}/blogs", array("use" => "blog::user-profile@blogs_pager", "as" => "profile-blogs", 'filter' => 'profile'))
    ->where(array('id' => '[a-zA-Z0-9\_\-]+'));


register_hook('profile.started', function($user) {
    add_menu('user-profile-more', array('title' => lang('blog::blogs'), 'as' => 'blogs', 'link' => profile_url('blogs', $user)));
});

register_block("blog::block/profile-recent", lang('blog::user-profile-recent-blogs'), null, array(
        'limit' => array(
            'title' => lang('list-limit'),
            'description' => lang('list-limit-desc'),
            'type' => 'text',
            'value' => 6
        ),)
);

register_block("blog::block/latest", lang('blog::latest-blogs'), null, array(
        'limit' => array(
            'title' => lang('list-limit'),
            'description' => lang('list-limit-desc'),
            'type' => 'text',
            'value' => 6
        ),)
);


//page blocks
register_hook('admin-started', function() {
    register_block_page('blogs', lang('blog::blogs'));

});

register_hook('user.delete', function($userid) {
    $d = db()->query("SELECT * FROM blogs WHERE user_id='{$userid}'");
    while($blog = $d->fetch_assoc()) {
        delete_blog($blog['id']);
    }
});