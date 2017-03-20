<?php
function playlists_pager($app) {
    $app->setTitle(lang('music::playlists'));
    $term = input('term');
    $type = input('type', 'browse');
    $filter = input('filter', 'all');
    $playlists = get_playlists($type, $term, null, null, $filter);
    $_SESSION['music_list_type'] = isset($_SESSION['music_list_type']) ? $_SESSION['music_list_type'] : config('default-music-list-type', 'list');
    $list_type = $_SESSION['music_list_type'];
    return $app->render(view('music::playlists', array('playlists' => $playlists, 'list_type' => $list_type)));
}

function playlist_page_pager($app) {
    $playlistId = segment(2);
    $play_list = get_playlist($playlistId);
    if(!$play_list) return MyError::error404();
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $play_list['title'], 'description' =>  $play_list['description'], 'image' =>  img('music::images/playlist.png'), 'keywords' => ''));
    $playlist = get_playlist_musics($play_list['id']);
    $empty_music = array("id" => 0, "slug" => "0", "title" => "", "artist" => "", "album" => "", "user_id" => 0, "entity_type" => "user", "entity_id" => "0", "cover_art" => "", "category_id" => "0", "source" => "upload", "code" => "", "status" => 1, "file_path" => img('music::audio/empty.mp3'), "play_count" => "", "" => "", "privacy" => "", "auto_posted" => "", "time" => "");
    $playlist = empty($playlist) ? array('0' => $empty_music) : $playlist;
    $first_track = reset($playlist)['slug'];
    $music = input('now_playing') ? get_music(input('now_playing')) : $playlist[$first_track];
    $music['file_path'] = fire_hook('filter.url', url($music['file_path']));
    $app->setTitle($play_list['title']);
    return $app->render(view('music::page', array('music' => $music, 'playlist' => $playlist, 'play_list' => $play_list)));
}

function playlist_edit_pager($app) {
    $playlist = get_playlist(input('id'));
    if (!$playlist or !is_playlist_owner($playlist)) redirect('music-playlists');
    $app->setTitle(lang('music::edit-playlist'));
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        if(isset($val['musics']) && !empty($val['musics'])) {
            save_playlist($val, $playlist);
            return redirect(get_playlist_url($playlist));
        } else {
            $message = lang('music::empty-playlist-error');
        }
    }
    return $app->render(view('music::edit_playlist', array('playlist' => $playlist, 'message' => $message)));
}

function playlist_delete_pager($app) {
    $playlist = get_playlist(input('id'));
    if (!$playlist or !is_playlist_owner($playlist)) return redirect_to_pager('music-playlists');
    delete_playlist($playlist['id']);
    return redirect_to_pager('music-playlists');
}
function playlist_create_pager($app) {
    $app->setTitle(lang('music::add-new-playlist'));
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array('title' => 'required'));
        if (validation_fails()) {
            $message = validation_first();
        } else {
            if(isset($val['musics']) && !empty($val['musics'])) {
                $added = add_playlist($val);
                if ($added) {
                    redirect(get_playlist_url($added));
                } else {
                    $message = lang('music::playlist-add-error-message');
                }
            } else {
                $message = lang('music::empty-playlist-error');
            }
        }
    }
    return $app->render(view('music::create_playlist', array('message' => $message)));
}


function playlist_editor_search_result_pager($app) {
    $category = input('category', 'all');
    $term = input('term');
    $type = input('type', 'browse');
    $filter = input('filter', 'all');
    $musics = get_musics($type, $category, $term, null, null, $filter);
    //exit(var_dump($musics));
    return view('music::ajax/playlist_editor_search_result', array('musics' => $musics));
}