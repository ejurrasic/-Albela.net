<?php
function ajax_pager($app) {
    CSRFProtection::validate(false);
    $action = input('action') ? input('action') : null;
    switch($action) {
        case 'set_list_type':
            $type = input('type') ? input('type') : null;
            if($type) {
                $_SESSION['music_list_type'] = $type;
            }
        break;

        case 'music_played':
            $id = input('id') ? input('id') : null;
            if ($id) fire_hook('music.played', $id);
        break;

        case 'music_page_dashboard':
            $id = input('id') ? input('id') : null;
            if ($id) {
                $music = get_music($id);
                if ($music) {
                    $refId = $music['id']; $refName = 'music';
                    echo view('music::music_page_dashboard', array('refName' => $refName, 'refId' => $refId, 'music' => $music));
                }
            }
        break;

        case 'music_page_comment':
            $id = input('id') ? input('id') : null;
            if ($id) {
                $music = get_music($id);
                if ($music) {
                    $refId = $music['id']; $refName = 'music';
                    echo view('music::music_page_comment', array('refName' => $refName, 'refId' => $refId, 'music' => $music));
                }
            }
        break;

        default:
        break;
    }
}