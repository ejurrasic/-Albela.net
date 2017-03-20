<?php
function videos_pager($app) {
    return $app->render(view('video::user-profile/lists', array('videos' => get_videos('user-profile', input('category','all'), input('term', null), $app->profileUser['id'], null, input('filter')))));
}