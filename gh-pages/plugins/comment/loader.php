<?php
load_functions("comment::comment");
register_post_pager("comment/add", array('use' => 'comment::comment@comment_add_pager', 'filter' => 'auth'));
register_get_pager('comment/delete', array('use' => 'comment::comment@comment_delete_pager', 'filter' => 'auth'));
register_get_pager('comment/more', array('use' => 'comment::comment@comment_more_pager'));
register_post_pager('comment/save', array('use' => 'comment::comment@comment_save_pager', 'filter' => 'auth'));
register_get_pager('comment/load/replies', array('use' => 'comment::comment@load_replies_pager'));

register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset("comment::css/comment.css");
        register_asset("comment::js/comment.js");
    }
});

register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'comment') {
        $comment = find_comment($typeId);
        fire_hook('reply.add', null, array($typeId, $comment['type'], $comment['type_id'], $text));
    }
});

register_hook('admin.statistics', function($stats) {
    $stats['comments'] = array(
        'count' => count_total_comments(),
        'title' => lang('comment::comments'),
        'icon' => 'ion-android-chat',
        'link' => 'javascript::void(0)',
    );
    return $stats;
});

register_hook('user.delete', function($userid) {
    $d = db()->query("SELECT * FROM comments WHERE user_id='{$userid}'");
    while($comment = $d->fetch_assoc()) {
        do_delete_comment($comment);
    }

    db()->query("DELETE FROM comments WHERE user_id='{$userid}'");
});


