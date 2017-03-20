<?php
function musics_pager($app) {
    if(preg_match('/musics\//i', $_SERVER['REDIRECT_URL'])) {
        $redir = http_build_query($_GET) == '' ? url_to_pager('musics') : url_to_pager('musics').'?'.http_build_query($_GET);
        header('location: '.$redir);
    }
    $app->setTitle(lang('music::musics'));
    $category = input('category', 'all');
    $term = input('term');
    $type = input('type', 'browse');
    $filter = input('filter', 'all');
    $musics = get_musics($type, $category, $term, null, null, $filter);
    $playlist = array();
    foreach($musics->results() as $music) {
        $playlist[$music['slug']] = $music;
    }
    $_SESSION['music_list_type'] = isset($_SESSION['music_list_type']) ? $_SESSION['music_list_type'] : config('default-music-list-type', 'list');
    $list_type = $_SESSION['music_list_type'];
    return $app->render(view('music::index', array('musics' => $musics, 'playlist' => $playlist, 'list_type' => $list_type)));
}

function music_page_pager($app) {
    $musicId = segment(1);
    $music = get_music($musicId);
    if(!$music) return MyError::error404();
    $music['file_path'] = fire_hook('filter.url', url($music['file_path']));
    $playlist = array($music['slug'] => $music);
    $app->setTitle($music['title']);
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $music['title'], 'description' =>  $music['artist'].' - '.$music['title'], 'image' =>  url_img($music['cover_art'], 920), 'keywords' => ''));
    return $app->render(view('music::page', array('music' => $music, 'playlist' => $playlist)));
}

function music_edit_pager($app) {
    $music = get_music(input('id'));
    if (!$music or !is_music_owner($music)) redirect('musics');
    $app->setTitle(lang('music::edit-music'));
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        $cover_art = input_file('cover_art');
        $cover_art_path = $music['cover_art'];
        if ($cover_art) {
            $uploader = new Uploader($cover_art);
            if ($uploader->passed()) {
                $uploader->setPath(get_userid().'/'.date('Y').'/musiccovers/');
                $cover_art_path = $uploader->resize()->result();
            }
            else {
                $message = $uploader->getError();
            }
        }
        $val['cover_art'] = $cover_art_path;
        save_music($val, $music);
        return redirect(get_music_url($music));
    }
    return $app->render(view('music::edit', array('music' => $music, 'message' => $message)));
}

function music_delete_pager($app) {
    $music = get_music(input('id'));
    if (!$music or !is_music_owner($music)) return  redirect('musics');
    delete_music($music['id']);
    return redirect_to_pager('musics');
}
function create_pager($app) {
    $app->setTitle(lang('music::add-new-music'));
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        //check for music files
        $musicFile = input_file('music_file');
        if ($musicFile) {
            $validator = validator($val, array(
                'title' => 'required',
            ));
            if (validation_fails()) {
                $message = validation_first();
            } else {
                $uploader = new Uploader($musicFile, 'audio');
                if ($uploader->passed()) {
                    $cover_art = input_file('cover_art');
                    $cover_art_path = '';
                    if ($cover_art) {
                        $uploader = new Uploader($cover_art);
                        if ($uploader->passed()) {
                            $uploader->setPath(get_userid().'/'.date('Y').'/musiccovers/');
                            $cover_art_path = $uploader->resize()->result();
                        }
                        else {
                            $message = $uploader->getError();
                        }
                    }
                    $val['cover_art'] = $cover_art_path;
                    $added = add_music($val);
                    if ($added) {
                        redirect(get_music_url($added));
                    } else {
                        $message = lang('music::music-add-error-message');
                    }
                } else {
                    $message = $uploader->getError();
                }
            }
        } else {
            $message = lang('music::music-add-error-message');
        }

    }
    return $app->render(view('music::create', array('message' => $message)));
}