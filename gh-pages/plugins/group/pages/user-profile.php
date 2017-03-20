<?php
//get_menu('user-profile', 'connections')->setActive();
function groups_pager($app) {
    return $app->render(view('group::user-profile/groups', array('groups' => get_groups('profile', $app->profileUser['id']))));
}

