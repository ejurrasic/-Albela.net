<?php
function add_photo_album($val) {
    $expected = array(
        'name' => '',
        'description' => '',
        'privacy' => '',
        'category' => '',
        'entity_id' => get_userid(),
        'entity_type' => 'user',

    );

    /**
     * @var $name
     * @var $description
     * @var $privacy
     * @var $category
     * @var $entity_id
     * @var $entity_type
     */
    extract(array_merge($expected, $val));
    $time = time();
    $name = sanitizeText($name);
    $description = sanitizeText($description);
    $category = sanitizeText($category);
    $privacy = sanitizeText($privacy);
    db()->query("INSERT INTO `photo_albums`(entity_id,entity_type,title,description,category_id,privacy,time)VALUES(
        '{$entity_id}','{$entity_type}','{$name}','{$description}','{$category}','{$privacy}','{$time}'
    )");

    fire_hook("photo.album.create", null, array(db()->insert_id, $val));

    return db()->insert_id;
}

function save_photo_album($val, $album) {
    $expected = array(
        'name' => '',
        'description' => '',
        'privacy' => '',
        'category' => '',
    );

    /**
     * @var $name
     * @var $description
     * @var $privacy
     * @var $category
     */
    extract(array_merge($expected, $val));
    $time = time();
    $name = sanitizeText($name);
    $description = sanitizeText($description);
    $category = sanitizeText($category);
    $privacy = sanitizeText($privacy);
    $albumId = $album['id'];
    db()->query("UPDATE photo_albums SET title='{$name}',description='{$description}',category_id='{$category}',privacy='{$privacy}' WHERE id='{$albumId}'");
    if ($album['privacy'] != $privacy) {
        //we need to change all photos to this privacy too
        db()->query("UPDATE medias SET privacy='{$privacy}' WHERE album_id='{$albumId}'");
    }
    fire_hook("photo.album.updated", null, array($album));
    return true;
}

function delete_photo_album($album) {
    //let the photos of this album
    $albumId = $album['id'];
    $query = db()->query("SELECT path FROM medias WHERE album_id='$albumId'");
    while($fetch = $query->fetch_assoc()) {
        delete_file($fetch['path']);
    }
    db()->query("DELETE  FROM medias WHERE album_id='$albumId'");
    //now time to delete the album itself
    db()->query("DELETE  FROM photo_albums WHERE id='$albumId'");

    fire_hook("photo.album.deleted", null, array($album));
    return true;
}

function get_photo_albums($type = null, $typeId = null, $category = false, $limit = null, $offset = 0) {
    $limit = ($limit) ? $limit : config('photo-album-listing-per-page', 20);
    $type = (empty($type)) ? 'user' : $type;
    $typeId = ($typeId) ? $typeId : get_userid();
    if (!$category) {
        if ($type == 'user') {
            $sql = "SELECT id,entity_id,entity_type,title,description,time,category_id FROM photo_albums WHERE entity_type='{$type}' AND entity_id='{$typeId}' AND (privacy='1' ";
            if ($typeId != get_userid()) {
                if (config('relationship-method', 3) != 1) {
                    $friendStatus = friend_status($typeId);
                    if ($friendStatus == 2) {
                        $sql .= " OR privacy='2'";
                    }
                } else {
                    if (is_following($typeId)) {
                        $sql .= " OR privacy='2'";
                    }
                }

            } else {
                if (is_loggedIn()) $sql .= " OR privacy='2' OR privacy='3' ";
            }
            $sql .= ')';
        } else if ($type == 'all') {
            $sql = "SELECT id,entity_id,entity_type,title,description,time,category_id FROM photo_albums ";
            $userid = isset($userid) ? $userid : get_userid();
            $whereClause = " WHERE entity_type='user' ";
            $privacyClause = "(privacy = 1 OR entity_id='{$userid}' ";
            $users = array(get_userid());
            $users = array_merge($users, get_following($userid));
            $users = array_merge($users, get_friends($userid));
            $users = implode(',', $users);
            $privacyClause .= " OR (privacy='2'  AND entity_id IN ({$users}))) ";
            $whereClause .= ($whereClause) ? " AND {$privacyClause} " : " {$privacyClause} ";
            if ($whereClause) $sql .= " {$whereClause} ";
        }
    } else {
        $sql = "SELECT id,entity_id,entity_type,title,description,time,category_id FROM photo_albums WHERE entity_type='user' AND (SELECT COUNT(album_id) FROM medias WHERE medias.album_id=photo_albums.id) > 0";
        if ($category != 'all') {
            $sql .= "  AND category_id='{$category}' ";
        }
        $sql .= " AND (privacy='1'";
        if (is_loggedIn()) {
            $userid = get_userid();
            $sql .= " OR entity_id='{$userid}'";
        }
        $sql .= ")";
    }

    $sql .= " ORDER BY time DESC LIMIT {$offset},{$limit}";
    $query = db()->query($sql);
    //exit($sql);
    //exit(db()->error);
    $albums = array();
    while($fetch = $query->fetch_assoc()) {
        $album = arrange_photo_album($fetch);
        if ($album) $albums[] = $album;
    }

    return $albums;
}

function get_photo_album($albumId, $all = true) {
    $query = db()->query("SELECT id,entity_id,entity_type,title,description,category_id,privacy,time,default_photo FROM photo_albums WHERE id='{$albumId}'");
    return ($all) ? arrange_photo_album($query->fetch_assoc()) : $query->fetch_assoc();
}

function arrange_photo_album($album) {
    if (!$album) return false;
    $albumId = $album['id'];
    if ($album['entity_type'] == 'user') {
        $user = find_user($album['entity_id'], false);
        if ($user) {
            $album['publisher'] = $user;
            $album['publisher']['url'] = profile_url(null, $user);
        }
    } else {
        $album['publisher'] = fire_hook('photo.album.get.publisher', null, array($album));
    }
    if (!$album['publisher']) return false;



    //let get the last uploaded photo to this album
    $album['photo-count'] = count_photos($album['id'], 'album');
    $query = db()->query("SELECT path FROM medias WHERE album_id='{$albumId}' ORDER BY id DESC LIMIT 1");
    //sexit(db()->error);
    $fetch = $query->fetch_assoc();
    if ($fetch) {
        $album['image'] = url_img($fetch['path'], 600);
    } else {
        $album['image'] = img("photo::images/default.png");
    }
    return $album;
}

function can_manage_photo_album($album) {
    if (!is_loggedIn()) return false;
    if ($album['entity_type'] == 'user' and $album['entity_id'] == get_userid()) return true;
    return false;
}

function can_view_photo_album($album) {
    if ($album['privacy'] == 1) return true;
    if ($album['privacy'] == 3 and can_manage_photo_album($album)) return true;
    if ($album['privacy'] == 2 and relationship_valid($album['entity_id'], 2)) {
        return true;
    }
    if (can_manage_photo_album($album)) return true;
    return false;
}

function count_photos($id, $type = 'album') {
    $sql = '';
    if ($type == 'album') {
        $sql = "SELECT id,path FROM medias WHERE album_id='{$id}'";
    } else{
        $sql = "SELECT id,path FROM medias WHERE type_id='{$id}' AND type='{$type}'";
    }
    $query = db()->query($sql);
    return $query->num_rows;
}
function get_photos($id, $type = 'album', $limit = null, $offset = 0) {
    $limit = ($limit) ? $limit : config("photo-listing-per-page", 20);
    $sql = '';
    if ($type == 'album') {
        $sql = "SELECT id, user_id, path FROM medias WHERE file_type = 'image' AND album_id='{$id}' ORDER BY `id` DESC LIMIT {$offset}, {$limit}";
    } elseif ($type == 'user-all') {
        $sql = "SELECT id,user_id,path FROM medias WHERE file_type = 'image' AND ((type='profile-cover' OR type='profile-avatar' OR type='user-posts' OR type='album') AND type_id='{$id}')  AND (privacy='1' ";
        if ($id != get_userid()) {
            if (config('relationship-method', 3) != 1) {
                $friendStatus = friend_status($id);
                if ($friendStatus == 2) {
                    $sql .= " OR privacy='2'";
                }
            } else {
                if (is_following($id)) {
                    $sql .= " OR privacy='2'";
                }
            }

        } else {
            $sql .= " OR privacy='2' OR privacy='3' ";
        }
        $sql .= " )  ORDER BY `id` DESC LIMIT {$offset},{$limit}";
        //exit($sql);
    } elseif($type == 'user-profile') {
        $sql = "SELECT id,user_id,path FROM medias WHERE file_type = 'image' AND type_id='{$id}' AND type='profile-avatar' ORDER BY `id` DESC LIMIT {$offset},{$limit}";
    } elseif ($type == 'user-cover') {
        $sql = "SELECT id,user_id,path FROM medias WHERE file_type = 'image' AND type_id='{$id}' AND type='profile-cover' ORDER BY `id` DESC LIMIT {$offset},{$limit}";
    } elseif ($type == 'user-timeline') {
        $sql = "SELECT id, user_id, path FROM medias";
        $userid = isset($userid) ? $userid : get_userid();
        $whereClause = " WHERE file_type = 'image' AND type_id = '{$id}' AND (type = 'user-posts')";
        $privacyClause = "(privacy = '1' OR user_id='{$userid}' ";
        if(is_loggedIn()) {
            $users = array(get_userid());
            $users = array_merge($users, get_following($userid));
            $users = array_merge($users, get_friends($userid));
            $users = implode(',', $users);
            if(!empty ($users)) {
                $privacyClause .= " OR (privacy='2'  AND user_id IN ({$users}))) ";
                $whereClause .= ($whereClause) ? " AND {$privacyClause} " : " {$privacyClause} ";
            }
        }
        if ($whereClause) $sql .= " {$whereClause} ";
        $sql .= " ORDER BY `id` DESC LIMIT {$offset}, {$limit}";
    } elseif ($type == 'all') {
        $sql = "SELECT id,user_id,path FROM medias ";
        $userid = isset($userid) ? $userid : get_userid();
        $whereClause = " WHERE file_type ='image' AND type != 'profile-avatar' AND type != 'profile-cover' ";
        $privacyClause = "(privacy = '1' OR user_id='{$userid}' ";
        if(is_loggedIn()) {
            $users = array(get_userid());
            $users = array_merge($users, get_following($userid));
            $users = array_merge($users, get_friends($userid));
            $users = implode(',', $users);
            if(!empty ($users)) {
            $privacyClause .= " OR (privacy='2'  AND user_id IN ({$users}))) ";
            $whereClause .= ($whereClause) ? " AND {$privacyClause} " : " {$privacyClause} ";
            }
        }
        if ($whereClause) $sql .= " {$whereClause} ";
        $sql .= "order by id desc LIMIT {$offset}, {$limit}";
        //exit($sql);
    }
    else{
        $sql = "SELECT id,user_id,path FROM medias WHERE file_type = 'image' AND type_id='{$id}' AND type='{$type}' ORDER BY `id` DESC LIMIT {$offset},{$limit}";
        $sql = fire_hook('photos.query', $sql, array($type, $id, $limit, $offset));
    }
    $query = db()->query($sql);
    return fetch_all($query);
}

function find_photo($id, $all = true) {
    $query = db()->query("SELECT user_id,id,path,type,type_id,album_id,privacy,ref_id,ref_name FROM medias WHERE id='{$id}'");
    return ($all) ? arrange_photo($query->fetch_assoc()) : $query->fetch_assoc();
}

function arrange_photo($fetch) {
    $photo = $fetch;

    if (!$photo) return false;

    //lets get the publisher
    if (in_array($fetch['type'], array('profile-cover', 'user-posts', 'album','profile-avatar'))) {
        $user = find_user($fetch['type_id'], false);
        if ($user) {
            $photo['publisher'] = $user;
            $photo['publisher']['avatar'] = get_avatar(75, $user);
            $photo['publisher']['url'] = profile_url(null, $user);
        }
    } else {
        $photo['publisher'] = fire_hook('photo.get.publisher', null, array($photo));
    }

   if (!isset($photo['publisher'])) return false;

    //album
    if ($photo['album_id']) {
        $photo['album'] = get_photo_album($photo['album_id'], false);
    }

    //lets determine the editor of this photo for comments
    $photo['editor'] = array(
        'id' => get_userid(),
        'type' => 'user',
        'avatar' => get_avatar(75)
    );
    //any other can override the editor if they like for example page e.t.c
    $photo['imageBefore'] = get_photo_before($photo);
    $photo['imageAfter'] = get_photo_after($photo);
    $photo = fire_hook("photo.arrange", $photo);
    return $photo;
}

function get_photo_before($photo) {
    $photoId = $photo['id'];
    $type = $photo['type'];
    $typeId = $photo['type_id'];

    if (isset($photo['album'])) {
        $albumId = $photo['album']['id'];
        $query = db()->query("SELECT id,path FROM   `medias` WHERE  id = (SELECT min(id) FROM medias WHERE album_id='{$albumId}' AND id > {$photoId}) ORDER BY id ASC LIMIT 1");
    } else {

        $query = db()->query("SELECT id,path FROM `medias` WHERE  id = (SELECT min(id) FROM medias WHERE type='{$type}' AND type_id='{$typeId}'  AND id > {$photoId}) ORDER BY id ASC LIMIT 1");
    }

    return ($query and $query->num_rows > 0) ? $query->fetch_assoc() : false;
}

function get_photo_after($photo) {
    $photoId = $photo['id'];
    $type = $photo['type'];
    $typeId = $photo['type_id'];
    if (isset($photo['album'])) {
        $albumId = $photo['album']['id'];
        $query = db()->query("SELECT id,path FROM   `medias` WHERE album_id='{$albumId}' AND id = (SELECT max(id) FROM medias WHERE album_id='{$albumId}' AND id < {$photoId}) ORDER BY id ASC LIMIT 1");
    } else {
        $query = db()->query("SELECT id,path FROM `medias` WHERE type='{$type}' AND type_id='{$typeId}' AND id = (SELECT max(id) FROM medias WHERE type='{$type}' AND type_id='{$typeId}'  AND id < {$photoId}) ORDER BY id ASC LIMIT 1");
    }

    return ($query and $query->num_rows > 0) ? $query->fetch_assoc() : false;
}

function count_user_photos($userid, $type = 'profile-avatar') {
    $query = db()->query("SELECT id FROM medias WHERE type_id='{$userid}' AND type='{$type}'");
    return $query->num_rows;
}

function get_last_user_photo($userid, $type = 'profile-avatar') {
    $query = db()->query("SELECT id,path FROM medias WHERE type_id='{$userid}' AND type='{$type}' ORDER BY id DESC");
    return $query->fetch_assoc();
}

function is_photo_owner($photo, $admin = false) {
        if (!is_loggedIn()) return false;
    if (is_admin() and $admin) return true;
    if ($photo['user_id'] == get_userid()) return true;

    return false;
}


function delete_photo($id, $photo = null) {
    $photo = ($photo) ? $photo : find_photo($id);
    if (!is_photo_owner($photo, true)) return false;
    //lets deleted comments and likes
    if (plugin_loaded('like')) delete_likes('photo', $id);
    if (plugin_loaded('comment')) delete_comments('photo', $id);

    //lets delete ref as well
    if ($photo['ref_name'] and $photo['ref_name'] == 'feed') {
        remove_feed($photo['ref_id']);
    }

    $user = get_user();
    if ($user['avatar'] == $photo['path']) {
        update_user(array('avatar' => ''));
    }

    db()->query("DELETE FROM medias WHERE id='{$id}'");
    foreach(array(75, 200,600, 920) as $size) {
        delete_file(path(str_replace('%w', $size, $photo['path'])));
    }
    return true;
}

function delete_photos($type, $id) {
    //$q = db()->query("SELECT * FROM medias WHERE type='{$type}' AND type_id='{$id}'");
}

function make_photo_db($id) {
    $photo = find_photo($id);
    if (!is_photo_owner($photo)) return false;
    if (preg_match('#posts#', $photo['path'])) {
        $path = str_replace('%w','600', $photo['path']);
    } else {
        $path = $photo['path'];
    }
    update_user(array('avatar' => $path));
}

function is_album_owner($album, $admin = false) {
    if (!is_loggedIn()) return false;
    if (is_admin() and $admin) return true;
    if ($album['entity_id'] == get_userid()) return true;
    return false;
}