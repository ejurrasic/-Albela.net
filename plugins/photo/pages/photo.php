<?php
app()->topMenu = lang('photo::photos');

function photo_pager($app){
    $app->setTitle(lang('photos'));
    $limit = input('limit') ? input('limit') : config("photo-listing-per-page", 20);
    $offset = input('offset') ? input('offset') : 0;
    $photos = get_photos(null, 'all', $limit, $offset);
    foreach($photos as $id => $pPath) {
        try{
            if (stripos(get_headers(url_img($pPath, 920))[0], "200 OK")) $images[$id] = $pPath;
        } catch(Exception $e) {
            $images[$id] = $pPath;
        }
    }
    return $app->render(view('photo::photo', array('photos' => $photos)));
}

function album_photos_pager($app){
    $id = input('id');
    $album = get_photo_album($id);
    $app->setTitle(lang('photo::album-photos'));
    $limit = input('limit') ? input('limit') : config("photo-listing-per-page", 20);
    $offset = input('offset') ? input('offset') : 0;
    return $app->render(view('photo::album_photos', array('photos' => get_photos($id, 'album', $limit, $offset), 'album' => $album)));
}

function photo_upload_pager($app){
    CSRFProtection::validate(false);
    $imagesFile = input_file('photos');
    $privacy = input_file('privacy') ? input_file('privacy') : 1;
    $result = array(
        'status' => 0,
        'message' => lang('unknown-error'),
        'photos' => ''
    );
    if ($imagesFile) {
        $images = array();
        $validate = new Uploader(null, 'image', $imagesFile);
        if ($validate->passed()) {
            foreach($imagesFile as $im) {
                $uploader = new Uploader($im);
                $path = get_userid().'/'.date('Y').'/photos/posts/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $image = $uploader->resize()->toDB('user-posts', get_userid(), $privacy, 0)->result();
                    $images[$uploader->insertedId] = $image;
                } else {
                    $result['status'] = 0;
                    $result['message'] = $uploader->getError();
                    return json_encode($result);
                }
            }
        } else {
            $result['status'] = 0;
            $result['message'] = $validate->getError();
            return json_encode($result);
        }
        if (!empty($images)) {
            $photos = "";
            foreach($images as $id => $image) {
                $photos .= view('photo::display-photo', array('id' => $id, 'image' => $image));
            }
            $result['status'] = 1;
            $result['message'] = lang('photo::successfully-upload-photos');
            $result['photos'] = $photos;
            $images = perfectSerialize($images);
            add_feed(array(
                'entity_id' => get_userid(),
                'entity_type' => 'user',
                'type' => 'feed',
                'type_id' => 'upload-photos',
                'type_data' => '',
                'privacy' => $privacy,
                'images' => $images,
                'auto_post' => true,
                'can_share' => 0
            ));
        }
    }
    return json_encode($result);
}

function create_album_pager($app){
    $app->setTitle(lang('photo::create-new-album'));
    $message = null;
    $val = input('val') ? input('val') : null;
    if ($val) {
		CSRFProtection::validate();
        if(add_photo_album($val)){
            $message = " ";
            redirect_to_pager('photo-myalbums', array('message' => $message));
        }
    }
    return $app->render(view('photo::create_album'));
}

function myphotos_pager($app){
    $app->setTitle(lang('photo::my-photos'));
    $limit = input('limit') ? input('limit') : config("photo-listing-per-page", 20);
    $offset = input('offset') ? input('offset') : 0;
    return $app->render(view('photo::myphotos', array('myphotos' => get_photos(get_userid(), 'user-all', $limit, $offset))));
}

function albums_pager($app) {
    $app->setTitle(lang('photo::albums'));
    $limit = input('limit') ? input('limit') : config("photo-album-listing-per-page", 20);
    $offset = input('offset') ? input('offset') : 0;
    return $app->render(view('photo::albums', array('albums' => get_photo_albums('all', null, false, $limit, $offset))));
}

function myalbums_pager($app) {
    $app->setTitle(lang('photo::my-albums'));
    $limit = input('limit') ? input('limit') : config("photo-album-listing-per-page", 20);
    $offset = input('offset') ? input('offset') : 0;
    return $app->render(view('photo::myalbums', array('myalbums' => get_photo_albums('user', get_userid(), false, $limit, $offset))));
}

function photo_view_pager($app) {
    $app->setLayout('layouts/blank');
    $photo = find_photo(segment(2));
    if (!$photo) redirect_back();
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => lang('photo::photo'), 'description' => '', 'image' => url_img($photo['path'], 600), 'keywords' => ''));
    return $app->render(view('photo::view-page', array('photo' => $photo)));
}

function paginate_albums_pager($app) {
    CSRFProtection::validate(false);
    $user = isset($app->profileUser) ? $app->profileUser : null;
    $id = input('id');
    $type = input('type');
    $offset = input('offset');
    $limit = config('photo-album-listing-per-page', 20);
    $newOffset = $offset + $limit;
    $category = input('category');
    $result = array(
        'albums' => '',
        'offset' => $newOffset
    );
    $albums = get_photo_albums($type, $id, $category, null, $newOffset);
    $content = '';
    foreach($albums as $album) {
        $link = input('link') && input('link') != 'undefined' ? urldecode(input('link')).'?id='.$album['id'] : profile_url('photos/album/'. $album['id'], $user);
        $content .= view('photo::display-album', array('album' => $album, 'link' => $link));
    }
    $result['albums'] = $content;
    return json_encode($result);
}

function  load_photo_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $result = array(
        'status' => 0,
        'left' => '',
        'right' => ''
    );
    $photo = find_photo($id);
    if ($photo) {
        $result['status'] = 1;
        $result['left'] = view('photo::viewer/left', array('photo' => $photo));
        $result['right'] = view('photo::viewer/right', array('photo' => $photo));
    }
    return json_encode($result);
}

function album_delete_pager($app) {
    $albumId = segment(3);
    $album = get_photo_album($albumId);
    if (!$album or !can_manage_photo_album($album)) redirect_back();
    $link = input('link');
    //eeya photos are gone with album
    delete_photo_album($album);
    return $link ? redirect(urldecode($link)) : redirect(profile_url('photos/albums'));
}

function paginate_album_photos_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $type = input('type');
    $offset = input('offset');
    $limit = config('photo-listing-per-page', 20);
    $newOffset = $offset + $limit;
    $result = array(
        'photos' => '',
        'offset' => $newOffset
    );

    $photos = get_photos($id, $type, null, $newOffset);
    $content = '';
    foreach($photos as $photo) {
        $content .= view('photo::display-photo', array('id' => $photo['id'], 'image' => $photo['path']));
    }
    $result['photos'] = $content;
    return json_encode($result);
}

function album_upload_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $album = get_photo_album($id);
    $imagesFile = input_file('photos');
    $result = array(
        'status' => 0,
        'message' => lang('unknown-error'),
        'photos' => ''
    );
    if ($imagesFile) {
        $images = array();
        $entity_id = $album['entity_id'];
        $entity_type = $album['entity_type'];
        $validate = new Uploader(null, 'image', $imagesFile);
        if ($validate->passed()) {
            foreach($imagesFile as $im) {
                $uploader = new Uploader($im);
                $path = $entity_type.'/'.$entity_id.'/'.date('Y').'/photos/album/'.$album['id'].'/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $image = $uploader->resize()->toDB('album', $entity_id, $album['privacy'], $album['id'])->result();
                    $images[$uploader->insertedId] = $image;
                } else {
                    $result['status'] = 0;
                    $result['message'] = $uploader->getError();
                    return json_encode($result);
                }
            }
        } else {
            $result['status'] = 0;
            $result['message'] = $validate->getError();
            return json_encode($result);
        }
        if (!empty($images)) {
            $photos = "";
            foreach($images as $id => $image) {
                $photos .= view('photo::display-photo', array('id' => $id, 'image' => $image));
            }
            $result['status'] = 1;
            $result['message'] = lang('photo::successfully-upload-album-photos');
            $result['photos'] = $photos;
            //lets help to post for friends and public awareness
            if ($album['privacy'] < 3 and $album['entity_type'] == 'user') {
                $images = perfectSerialize($images);
                add_feed(array(
                   'entity_id' => $album['entity_id'],
                    'entity_type' => $album['entity_type'],
                    'type' => 'feed',
                    'type_id' => 'upload-album-photos',
                    'type_data' => $album['id'],
                    'privacy' => $album['privacy'],
                    'images' => $images,
                    'auto_post' => true,
                    'can_share' => 0
                ));
            }
        }
    }
    return json_encode($result);
}

function photo_delete_pager($app) {
    $id = segment(2);
    $link = urldecode(input('link'));
    delete_photo($id);
    return $link ? redirect(urldecode($link)) : redirect_back();
}

function photo_dp_pager($app) {
    $id = segment(2);
    make_photo_db($id);
    redirect_back();
}

function photo_edit_album_pager($app) {
    $albumId = input('id');
    $album = get_photo_album($albumId);
    if (!$album or !can_manage_photo_album($album)) redirect_back();
    $app->setTitle($album['title']);
    $val = input('val');
    $message = null;
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array('name' => 'required'));
        if (validation_passes()) {
            save_photo_album($val, $album);
            return redirect(url_to_pager('photo-album-photos').'?id='.$album['id']);
        } else {
            $message = validation_first();
        }
    }
    return $app->render(view("photo::edit_album", array("album" => $album, 'message' => $message)));
}