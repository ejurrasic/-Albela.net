<?php
function albums_create_pager($app) {
    $val = array(
        'name' => input("title"),
        'description' => input("desc"),
        "privacy" => input("privacy")
    );

    $albumId = add_photo_album($val);
    return json_encode(array('status' => 1));
}

function albums_photos_pager($app) {
    $limit = input("limit", 10);
    $offset = input("offset", 0);
    $albumId = input('album_id');
    if (in_array($albumId, array('cover', 'profile', 'timeline'))) {
        $photos = get_photos(input("the_userid"), 'user-'.$albumId,$limit, $offset);
    } else {
        $photos = get_photos($albumId, 'album', $limit, $offset);
    }

    $result = array();
    foreach($photos as $photo) {
        $result[] = array(
            'id' => $photo['id'],
            'path' => url_img($photo['path'], 600)
        );
    }

    return json_encode($result);
}

function albums_details_pager($app) {
    $albumId = input('album_id');
    $album = get_photo_album($albumId);
    return json_encode(array(
        'id' => $album['id'],
        'title' => $album['title'],
        'image' => $album['image'],
        'userid' => $album['entity_id'],
        'privacy' => $album['privacy'],
        'description' => $album['description']
    ));
}

function albums_edit_pager($app) {
    $albumId = input("album_id");
    $album = get_photo_album($albumId);
    $val = array(
        'name' => input("title"),
        'description' => input("desc"),
        "privacy" => input("privacy")
    );
    $albumId = save_photo_album($val, $album);
    return json_encode(array('status' => 1));
}

function albums_delete_pager($app) {
    $albumId = input("album_id");
    $album = get_photo_album($albumId);
    delete_photo_album($album);
    return json_encode(array('status' => 1));
}

function albums_upload_pager($app) {
    $albumId = input('album_id');
    $album = get_photo_album($albumId);
    $entity_id = $album['entity_id'];
    $entity_type = $album['entity_type'];
    $uploader = new Uploader(input_file("photo"));
    $path = $entity_type.'/'.$entity_id.'/'.date('Y').'/photos/album/'.$album['id'].'/';
    $uploader->setPath($path);
    $images = array();
    $result = array();
    if ($uploader->passed()) {
        $image = $uploader->resize()->toDB('album', $entity_id, $album['privacy'], $album['id'])->result();
        $images[$uploader->insertedId] = $image;
        $result['path'] = url_img($image, 600);
    }

    $result['id'] = $uploader->insertedId;


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
    return json_encode($result);
}

function photo_details_pager($app) {
    $photo = input("photo");
    $userid = input("userid");
    $url = url();
    $photo = str_replace($url, "", $photo);
    $photo = str_replace(array("_75_", "_200_","_600_", "_920_"), "_%w_", $photo);
    //exit($photo);
    $query = db()->query("SELECT user_id,id,path,type,type_id,album_id,privacy,ref_id,ref_name FROM medias WHERE path='{$photo}' OR id='{$photo}'");
    $result = array('status' => 0);
    if ($query && $query->num_rows > 0) {
        $photo = arrange_photo($query->fetch_assoc());

        $type = "photo";
        $typeId = $photo['id'];
        $result = array(
            'status' => 1,
            'has_like' => false,
            'has_dislike' => false,
            'has_react' => false,
            'like_count' => 0,
            'dislike_count' => 0,
            'comments' => count_comments($type, $typeId),
            'react_members' => array(),
            'id' => $photo['id'],
            'path' => url_img($photo['path'], 920)
        );
        $im = get_photo_before($photo);
        if ($im) {
            $result['imageBefore'] = $im['id'];
        }
        $im = get_photo_after($photo);
        if ($im) {
            $result['imageAfter'] = $im['id'];
        }

        if (config('feed-like-type', 'regular') == 'regular') {
            if (has_liked($type, $typeId, 1, $userid)) {
                $result['has_like'] = true;
            }
            $result['like_count'] = count_likes($type, $typeId);
            if (config('enable-dislike', false)) {
                if (has_disliked($type, $typeId, 1, $userid)) {
                    $result['has_dislike'] = true;
                }
                $result['dislike_count'] = count_dislikes('feed', $typeId);
            }

        } else {
            if (has_reacted($type, $typeId, 1, $userid)) {
                $result['has_react'] = true;
            }
            $people = get_reactors($type, $typeId, 5);
            foreach($people as $user) {
                $result['react_members'][] = array(
                    get_avatar(75, $user),
                    $user['like_type'],
                    get_user_name($user)
                );
            }
        }
    }

    return json_encode($result);
}