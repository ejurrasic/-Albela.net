<?php
function browse_pager($app) {
    $category = input('category', 'all');
    $term = input('term');
    $type = input('type', 'browse');
    $filter = input('filter', 'all');
    $limit = input("limit", 10);
    $musics = get_musics($type, $category, $term, null, null, $filter);

    $result = array(
        'categories' => array(
            array('id' => 'all', 'title' => lang('all'))
        ),
        'songs' => array(),
    );

    foreach(get_music_categories() as $category) {
        $subCategories = get_music_parent_categories($category['id']);
        $sub = array();
        if ($subCategories) {
            foreach($subCategories as $cat) {
                $sub[] = array(
                    'id' => $cat['id'],
                    'title' => $cat['title']
                );
            }
        }
        $result['categories'][] = array(
            'id' => $category['id'],
            'title' => lang($category['title']),
            'subcategories' => $sub
        );
    }

    foreach($musics->results() as $music) {
        $result['songs'][]  = api_arrange_songs($music);
    }

    return json_encode($result);
}

function get_categories_pager($app) {
    $result = array();
    foreach(get_music_categories() as $category) {
        $subCategories = get_music_parent_categories($category['id']);
        $sub = array();
        if ($subCategories) {
            foreach($subCategories as $cat) {
                $sub[] = array(
                    'id' => $cat['id'],
                    'title' => $cat['title']
                );
            }
        }
        $result[] = array(
            'id' => $category['id'],
            'title' => lang($category['title']),
            'subcategories' => $sub
        );
    }

    return json_encode($result);
}

function music_page_pager($app) {
    $musicId = input("music_id");
    $music = get_music($musicId);

    return json_encode(api_arrange_songs($music));
}

function music_delete_pager($app) {
    $music = get_music(input('music_id'));
    $result = array('status' => 0);
    if (!$music or !is_music_owner($music)) return  json_encode($result);
    delete_music($music['id']);
    $result['status'] = 1;
    return json_encode($result);
}

function music_edit_pager($app) {
    $music = get_music(input('music_id'));

    $val = array(
        'title' => input('title'),
        'artist' => input('artist'),
        'album' => input('album'),
        'privacy' => input('privacy'),
        'category_id' => input('category_id')
    );

    $cover_art = input_file('cover_art');
    $cover_art_path = $music['cover_art'];
    if ($cover_art) {
        $uploader = new Uploader($cover_art);
        if ($uploader->passed()) {
            $uploader->setPath(get_userid().'/'.date('Y').'/musiccovers/');
            $cover_art_path = $uploader->resize()->result();
        }
    }
    $val['cover_art'] = $cover_art_path;
    save_music($val, $music);
    $music = get_music($music['id']);
    return json_encode(array_merge(array('status' => 1), api_arrange_songs($music)));
}

function music_create_pager($app) {
    $val = array(
        'title' => input('title'),
        'artist' => input('artist'),
        'album' => input('album'),
        'privacy' => input('privacy'),
        'category_id' => input('category_id')
    );
    $result = array(
        'status' => 0,
        'message' => ''
    );
    $musicFile = input_file('music_file');
    if ($musicFile) {

            $uploader = new Uploader($musicFile, 'music_file');
            if ($uploader->passed()) {
                $cover_art = input_file('cover_art');
                $cover_art_path = '';
                if ($cover_art) {
                    $uploader = new Uploader($cover_art);
                    if ($uploader->passed()) {
                        $uploader->setPath(get_userid().'/'.date('Y').'/musiccovers/');
                        $cover_art_path = $uploader->resize()->result();
                    }
                }
                $val['cover_art'] = $cover_art_path;
                $added = add_music($val);
                if ($added) {
                    $result['status'] = 1;
                    $result = array_merge($result, api_arrange_songs($added));
                }
            } else {
                $result['message'] = $uploader->getError();
            }

    }
    return json_encode($result);
}