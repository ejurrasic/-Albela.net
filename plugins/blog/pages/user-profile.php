<?php
//get_menu('user-profile', 'connections')->setActive();
function blogs_pager($app) {
    return $app->render(view('blog::user-profile/blogs', array('blogs' => get_blogs('mine', null, null, $app->profileUser['id']))));
}

