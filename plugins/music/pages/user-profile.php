<?php
function musics_pager($app) {
    $musics = get_musics('user-profile', input('category','all'), input('term', null), $app->profileUser['id'], null, input('filter'));
    $playlist = array();
    foreach($musics->results() as $music) {
        $playlist[$music['slug']] = $music;
    }
    $_SESSION['music_list_type'] = isset($_SESSION['music_list_type']) ? $_SESSION['music_list_type'] : config('default-music-list-type', 'list');
    $list_type = $_SESSION['music_list_type'];
    return $app->render(view('music::user-profile/lists', array('musics' => $musics, 'playlist' => $playlist, 'list_type' => $list_type)));
}