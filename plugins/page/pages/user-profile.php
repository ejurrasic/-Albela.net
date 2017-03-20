<?php
//get_menu('user-profile', 'connections')->setActive();
function likes_pager($app) {
    return $app->render(view('page::user-profile/likes', array('pages' => get_pages('likes', $app->profileUser['id']))));
}

