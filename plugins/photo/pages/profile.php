<?php
get_menu('user-profile', 'photos')->setActive();
function profile_photos_pager($app) {

    $type = input('type', 'all');

    switch($type) {
        case 'albums':

            break;
        case 'photo':
            $album = get_photo_album(input('id'));
            if (!$album or !can_view_photo_album($album)) return redirect_back();
            $type = 'albums';
            $content = view("photo::profile/album-photos", array('photos' => get_photos($album['id']), 'album' => $album));
            break;
        default:
            $content = view('photo::profile/photos', array('photos' => get_photos($app->profileUser['id'], 'user-all')));
            break;
    }
    return $app->render(view('photo::profile/layout', array('content'  => $content, 'type' => $type)));
}

function profile_photos_albums_pager($app) {
    $content = view('photo::profile/albums', array('albums' => get_photo_albums('user',$app->profileUser['id'])));
    return $app->render(view('photo::profile/layout', array('content'  => $content, 'type' => 'albums')));
}

function profile_album_photos_pager($app) {
    $album = get_photo_album(segment(3));
    if (!$album or !can_view_photo_album($album)) return redirect_back();
    $type = 'albums';
    $content = view("photo::profile/album-photos", array('photos' => get_photos($album['id']), 'album' => $album));
    return $app->render(view('photo::profile/layout', array('content'  => $content, 'type' => 'albums')));
}

function profile_photos_album_create_pager($app) {
    $val = input('val');
    $message = null;
    $app->setTitle(lang('photo::create-new-album'));
    if (!is_profile_owner()) return redirect(profile_url(null, $app->profileUser));
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'name' => 'required',
        ));
        if (validation_passes()) {
            $albumId = add_photo_album($val);
            return redirect(profile_url('photos/album/'.$albumId, $app->profileUser));
        } else {
            $message = validation_first();
        }
    }
    $content = view('photo::profile/create-album', array('message' => $message));
    return $app->render(view('photo::profile/layout', array('content'  => $content, 'type' => 'albums')));
}

function profile_photos_album_edit_pager($app) {
    $albumId = segment(4);
    $album = get_photo_album($albumId);
    if (!$album or !can_manage_photo_album($album)) redirect_back();
    $app->setTitle($album['title']);
    $val = input('val');
    $message = null;
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'name' => 'required',
        ));
        if (validation_passes()) {
            $albumId = save_photo_album($val, $album);
            return redirect(profile_url('photos/album/'.$album['id'], $app->profileUser));
        } else {
            $message = validation_first();
        }
    }

    $content = view("photo::profile/edit-album", array("album" => $album, 'message' => $message));
    return $app->render(view('photo::profile/layout', array('content'  => $content, 'type' => 'albums')));
}

function profile_photos_user_pager($app) {
    $type = segment(2);
    if (!in_array($type, array('cover', 'profile', 'timeline'))) return redirect(profile_url('photos/albums', $app->profileUser));
    $content = view('photo::profile/user', array('photos' => get_photos($app->profileUser['id'], 'user-'.$type), 'type' => $type));

    return $app->render(view('photo::profile/layout', array('content'  => $content, 'type' => 'albums')));
}
 