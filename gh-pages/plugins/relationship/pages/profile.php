<?php
get_menu('user-profile', 'connections')->setActive();
function friends_pager($app) {
    return $app->render(view('relationship::profile/lists', array(
        'title' => lang('relationship::friends'),
        'users' => list_connections(get_friends($app->profileUser['id']))
    )));
}

function followers_pager($app) {

    return $app->render(view('relationship::profile/lists', array(
        'title' => lang('relationship::followers'),
        'users' => list_connections(get_followers($app->profileUser['id']))
    )));
}

function following_pager($app) {
    return $app->render(view('relationship::profile/lists', array(
        'title' => lang('relationship::following'),
        'users' => list_connections(get_following($app->profileUser['id']))
    )));
}
