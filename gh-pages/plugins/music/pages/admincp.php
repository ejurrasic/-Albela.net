<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("musics-manager")->setActive();

function musics_pager($app) {
    $term = input('term');
    $category = input('category');
    $limit = input('limit', 10);
    $musics = get_all_musics($category, $term, $limit);
    $app->setTitle(lang('music::musics'));
    return $app->render(view('music::admincp/lists', array('musics' => $musics)));
}

function playlists_pager($app) {
    $term = input('term');
    $limit = input('limit', 10);
    $playlists = get_all_playlists($term, $limit);
    $app->setTitle(lang('music::playlists'));
    return $app->render(view('music::admincp/playlists', array('playlists' => $playlists)));
}

function music_manage_pager($app) {
    $app->setTitle(lang('music::music-manager'));
    $action = input('action');
    $id  = input('id');
    $music = get_music($id);
    if (!$music) return redirect_to_pager('admin-musics-pager');
    $backUrl = (isset($_POST['back_url'])) ? $_POST['back_url'] : $_SERVER['HTTP_REFERER'];
    switch($action) {
        case 'edit':
            $val = input('val');
            if ($val) {
		CSRFProtection::validate();
                $cover_art = input_file('cover_art');
                $cover_art_path = $music['cover_art'];
                if ($cover_art) {
                    exit('YES');
                    $uploader = new Uploader($cover_art);
                    if ($uploader->passed()) {
                        $uploader->setPath($music['user_id'].'/'.date('Y').'/musiccovers/');
                        $cover_art_path = $uploader->resize()->result();
                    }
                    else {
                        $message = $uploader->getError();
                    }
                }
                $val['cover_art'] = $cover_art_path;
                save_music($val, $music);
                return redirect($backUrl);
            }
            return $app->render(view('music::admincp/edit', array('music' => $music, 'url' => $backUrl)));
            break;
        case 'delete':
            delete_music($music['id']);
            return redirect($backUrl);
            break;
    }
}

function playlist_manage_pager($app) {
    $app->setTitle(lang('music::playlist-manager'));
    $action = input('action');
    $id  = input('id');
    $playlist = get_playlist($id);
    if (!$playlist) return redirect_to_pager('admin-playlists-pager');
    $backUrl = (isset($_POST['back_url'])) ? $_POST['back_url'] : $_SERVER['HTTP_REFERER'];
    switch($action) {
        case 'edit':
            $val = input('val');
            if ($val) {
		CSRFProtection::validate();
                save_playlist($val, $playlist);
                return redirect($backUrl);
            }
            return $app->render(view('music::admincp/edit_playlist', array('playlist' => $playlist, 'url' => $backUrl)));
            break;
        case 'delete':
            delete_playlist($playlist['id']);
            return redirect($backUrl);
            break;
    }
}

function categories_pager($app) {
    $app->setTitle(lang('music::music-categories'));
    $categories = (input('id')) ? get_music_parent_categories(input('id')): get_music_categories();
    return $app->render(view('music::admincp/categories/list', array('categories' => $categories)));
}

function manage_categories_pager($app) {
    $action = input('action');
    $app->setTitle(lang('music::music-categories'));
    switch($action) {
        case 'order':
            $id = input('id');
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_music_order($ids[$i], $i, $id);
            }
            break;
        case 'edit':
            $category = get_music_category(input('id'));
            $val = input('val');
            if ($val) {
    		CSRFProtection::validate();
                save_music_category($val, $category);
                $url = ($category['parent_id']) ? url_to_pager('admin-music-categories-pager').'?id='.$category['parent_id'] : url_to_pager('admin-music-categories-pager');
                redirect($url);
            }
            return $app->render(view('music::admincp/categories/edit', array('category' => $category)));
            break;
        case 'delete':
            $category = get_music_category(input('id'));
            $url = ($category['parent_id']) ? url_to_pager('admin-music-categories-pager').'?id='.$category['parent_id'] : url_to_pager('admin-music-categories-pager');
            delete_music_category($category);
            redirect($url);
            break;
    }
}

function add_categories_pager($app) {
    $app->setTitle(lang('add-category'));
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        music_add_category($val);
        /**
         * @var $category
         */
        extract($val);
        $url = ($category) ? url_to_pager('admin-music-categories-pager').'?id='.$category : url_to_pager('admin-music-categories-pager');
        return redirect(url($url));
    }
    return $app->render(view('music::admincp/categories/add', array('message' => $message)));
}