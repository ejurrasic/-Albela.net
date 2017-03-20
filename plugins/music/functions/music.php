<?php
function add_music($val) {
    /**
     * @var $title
     * @var $artist
     * @var $album
     * @var $privacy
     * @var $code
     * @var $source
     * @var $file_path
     * @var $entity_id
     * @var $entity_type
     * @var $status
     * @var $category_id
     * @var $cover_art
     * @var $auto_posted
     */
    $expected = array('title' => '', 'artist' => '', 'album' => '', 'privacy' => 1, 'code' => '', 'source' => '', 'status' => 1, 'file_path' => '', 'entity_type' => 'user', 'entity_id' => get_userid(), 'category_id' => '', 'cover_art' => '', 'auto_posted' => 0);
    extract(array_merge($expected, $val));
    $result = array('result' => true);
    $result = fire_hook('can.post.music', $result, array($entity_type, $entity_id));
    if (!$result['result']) return false;
    $musicFile = input_file('music_file');
    if ($musicFile) {
        $uploader = new Uploader($musicFile, 'audio');
        $uploader->setPath(get_userid().'/'.date('Y').'/musics/');
        $uploader->uploadFile();
        $file_path = $uploader->result();
        $status = 1;
    }
    $time = time();
    $userid = get_userid();
    $slug = toAscii($title);
    if (empty($slug)) $slug = md5(time());
    if (music_exists($slug) || in_array($slug, array('playlist'))) {
        $slug = md5($slug.time());
    }
  db()->query("INSERT INTO musics (auto_posted, title, slug, artist, album, user_id, entity_type, entity_id, cover_art, category_id, source, code, status, file_path, privacy, time) VALUES('{$auto_posted}', '{$title}', '{$slug}', '{$artist}', '{$album}', '{$userid}', '{$entity_type}', '{$entity_id}', '{$cover_art}', '{$category_id}', '{$source}', '{$code}', '{$status}', '{$file_path}', '{$privacy}', '{$time}' )");
    $musicId = db()->insert_id;
    $music = get_music($musicId);
    fire_hook('music.added', null, array($music, $musicId));
    return $music;
}

function is_music_owner($music) {
    if (!is_loggedIn()) return false;
    if ($music['user_id'] == get_userid()) return true;
    return false;
}

function save_music($val, $music) {
    $expected = array('title' => '', 'artist' => '', 'album' => '', 'featured' => $music['featured'], 'privacy' => $music['privacy'], 'category' => '');
    /**
     * @var $title
     * @var $artist
     * @var $album
     * @var $cover_art
     * @var $featured
     * @var $privacy
     * @var $category
     */
    extract(array_merge($expected, $val));
    $musicId = $music['id'];
    db()->query("UPDATE musics SET title = '{$title}', artist = '{$artist}', album = '{$album}', cover_art = '{$cover_art}', featured = '{$featured}', category_id = '{$category}', privacy = '{$privacy}' WHERE id = '{$musicId}'");
    fire_hook('music.admin.edited', null, array($musicId));
    return true;
}

function delete_music($id) {
    $music = get_music($id);
    //delete the row
    db()->query("DELETE FROM musics WHERE id='{$id}'");
    if ($music['source'] == 'upload') {
        delete_file(path($music['cover_art']));
        delete_file(path($music['file_path']));
    }
    $query = db()->query("SELECT feed_id FROM feeds WHERE type_id='upload-music' AND type_data = {$id} AND feed_content = '' AND photos = '' AND link_details = ''");
    if ($query->num_rows > 0) {
        $feeds = fetch_all($query);
        foreach($feeds as $feed) {
            remove_feed($feed['feed_id']);
        }
    }
    return true;
}

function music_exists($slug) {
    $query = db()->query("SELECT slug FROM musics WHERE slug='{$slug}' LIMIT 1");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

function get_music($id) {
    if (is_numeric($id)) {
        $sql = "SELECT * FROM musics WHERE id='{$id}'";
    } else {
        $sql = "SELECT * FROM musics WHERE slug='{$id}'";
    }
    $sql .= " LIMIT 1";
    $query = db()->query($sql);
    if ($query) return $query->fetch_assoc();
    return false;
}

function get_all_musics($cat, $term, $limit = 10) {
    $sql = "SELECT * FROM musics ";
    $where = "";
    if ($cat and $cat != 'all') $where = " category_id='{$cat}' ";
    if ($term) $where  .= ($where) ? " AND (title LIKE '%{$term}%' OR artist LIKE '%{$term}%' OR album LIKE '%{$term}%') " : " (title LIKE '%{$term}%' OR artist LIKE '%{$term}%' OR album LIKE '%{$term}%')";
    if ($where) $sql .= "WHERE {$where} ";
    $sql .= "ORDER BY time desc";
    return paginate($sql, $limit);
}

function get_musics($type, $category = 'all', $term = null, $userid = null, $limit = 10, $filter = 'all', $withTitle = false) {
    $sql = "SELECT * FROM musics ";
    $whereClause = "";
    $userid = ($userid) ? $userid : get_userid();
    if ($type == 'mine') {
        $whereClause .= ($whereClause) ? " AND user_id='{$userid}' ": " user_id='{$userid}'";
    }
    if ($type == 'user-profile') {
        $w = " user_id='{$userid}' AND entity_type='user' ";
        $privacy = array(1);
        if (is_loggedIn()) {
            if ($userid == get_userid()) $privacy = array_merge($privacy, array(2,3));
            if (friend_status($userid) == 2) $privacy[] = 2;
        }
        $privacy = implode(',', $privacy);
        $w .= " AND privacy IN ({$privacy}) ";
        $whereClause .= ($whereClause) ? " AND {$w} ": " {$w}";
    }
    if ($category and $category != 'all') $whereClause .= ($whereClause) ? " AND category_id='{$category}' " : "category_id='{$category}' ";
    if ($filter and $filter == 'featured') $whereClause .= ($whereClause) ? " AND featured='1' " : " featured='1' ";
    if ($term) $whereClause  .= ($whereClause) ? " AND (title LIKE '%{$term}%' OR artist LIKE '%{$term}%' OR album LIKE '%{$term}%' ) " : " (title LIKE '%{$term}%' OR artist LIKE '%{$term}%' OR album LIKE '%{$term}%')";
    if ($type == 'browse') {
        $privacyClause = "(privacy = 1 OR user_id='{$userid}' ";
        $users = array(0);
        $users = array_merge($users, get_following($userid));
        $users = array_merge($users, get_friends($userid));
        $users = implode(',', $users);
        $privacyClause .= " OR (privacy='2'  AND user_id IN ({$users}))) ";
        $whereClause .= ($whereClause) ? " AND {$privacyClause} " : " {$privacyClause} ";
    }
    if ($whereClause) $sql .= " WHERE {$whereClause} ";
    if ($type != 'mine') $sql .= " AND status='1'";
    if ($withTitle) $sql .= " AND title != '' ";
    if ($filter and $filter == 'top') {
        $sql .= " ORDER BY play_count desc";
    } else {
        $sql .= " ORDER BY time desc";
    }
    $limit = ($limit)  ? $limit : config('music-list-limit', 10);
    return paginate($sql, $limit);
}

function get_related_musics($music, $limit = 6) {
    $title = $music['title'];
    $explode = explode(" ", $title);
    $musicId = $music['id'];
    $sql = "SELECT * FROM musics WHERE id != '{$musicId}'  AND (";
    $where = "";
    foreach($explode as $t) {
        $where .= ($where) ? " OR title LIKE '%{$t}%' " : "title LIKE '%{$t}%'";
    }
    $sql .= $where.') ORDER BY time desc';
    return paginate($sql, $limit);

}

function get_music_owner($music) {
    $result = array('name' => '', 'image' => '', 'link' => '', 'id' => '');
    if ($music['entity_type'] == 'user')  {
        $user = find_user($music['user_id'], false);
        $result['name'] = get_user_name($user);
        $result['image'] = get_avatar(200, $user);
        $result['link'] = profile_url(null, $user);
        $result['id'] = $user['id'];
    }
    return fire_hook('get.music.owner', $result);
}

function get_music_url($music) {
    return url_to_pager('music-page', array('id' => $music['slug']));
}

function get_music_categories() {
//    $cacheName = "music-categories";
//    if (cache_exists($cacheName)) {
//        return get_cache($cacheName);
//    } else {
        $db = db()->query("SELECT * FROM music_categories WHERE parent_id='0' ORDER BY `order` ASC");
        $result = fetch_all($db);
//        set_cacheForever($cacheName, $result);
        return $result;
//    }
}

function get_music_category($id) {
    $query = db()->query("SELECT * FROM music_categories WHERE id='{$id}'");
    if ($query) return $query->fetch_assoc();
    return false;
}

function get_music_parent_categories($id) {
//    $cacheName = 'music-parent-categories-'.$id;
//    if (cache_exists($cacheName)) {
//        return get_cache($cacheName);
//    } else {
        $db = db()->query("SELECT * FROM music_categories WHERE parent_id='{$id}' ORDER BY `order` ASC");
        $result = fetch_all($db);
//      set_cacheForever($cacheName, $result);
        return $result;
//    }
}

function update_music_order($catId, $no, $parentId = null) {
    db()->query("UPDATE music_categories SET `order`='{$no}' WHERE id='{$catId}'");
    if ($parentId) {
        forget_cache('music-parent-categories-'.$parentId);
    } else {
        forget_cache("music-categories");
    }
    return true;
}

function delete_music_category($category) {
    $id = $category['id'];
    db()->query("DELETE FROM music_categories WHERE id='{$id}'");
    forget_cache('music-categories');
    if ($category['parent_id']) forget_cache('music-parent-categories-'.$category['parent_id']);
    return true;
}
function save_music_category($val, $cat) {
    /**
     * @var $title
     * @var $category
     */
    extract($val);
    $englishValue = $title['english'];
    $slug = $cat['title'];
    foreach($title as $langId => $t) {
        if (!$t) $t = $englishValue;
        (phrase_exists($langId, $slug)) ? update_language_phrase($slug, $t, $langId, 'music-category') : add_language_phrase($slug, $t, $langId, 'music-category');
    }
    $categoryId = $cat['id'];
    db()->query("UPDATE music_categories SET parent_id='{$category}' WHERE id='{$categoryId}'");
    fire_hook('music.category.edit', null, array($cat));
    forget_cache('music-categories');
    if ($category) forget_cache('music-parent-categories-'.$category);
    return true;
}

function music_add_category($val) {
    /**
     * @var $title
     * @var $category
     */
    extract($val);
    $titleSlug = 'music_category_'.md5(time().serialize($val)).'_title';
    $englishValue = $title['english'];
    foreach($title as $langId => $t) {
        if (!$t) $t = $englishValue;
        add_language_phrase($titleSlug, $t, $langId, 'music-category');
    }
    $slug = toAscii($englishValue);
    if (empty($slug)) $slug = md5(time());
    if (music_category_exists($slug)) $slug = md5($slug.time());
    db()->query("INSERT INTO music_categories (title, parent_id, slug) VALUES('{$titleSlug}', '{$category}', '{$slug}')");
    $insertedId = db()->insert_id;
    fire_hook('music.category.add', null, array($insertedId));
    forget_cache('music-categories');
    if ($category) forget_cache('music-parent-categories-'.$category);
    return true;
}

function music_category_exists($slug) {
    $db = db()->query("SELECT id FROM music_categories WHERE slug='{$slug}' LIMIT 1");
    if ($db and $db->num_rows > 0) return true;
    return false;
}

function get_playlists($type, $term = null, $userid = null, $limit = 10, $filter = 'all', $withTitle = false) {
    $sql = "SELECT * FROM music_playlists ";
    $whereClause = "";
    $userid = ($userid) ? $userid : get_userid();
    if ($type == 'mine') $whereClause .= $whereClause ? " AND user_id = '{$userid}' ": " user_id = '{$userid}'";
    if ($type == 'user-profile') {
        $w = " user_id = '{$userid}' AND entity_type='user' ";
        $privacy = array(1);
        if (is_loggedIn()) {
            if ($userid == get_userid()) $privacy = array_merge($privacy, array(2, 3));
            if (friend_status($userid) == 2) $privacy[] = 2;
        }
        $privacy = implode(',', $privacy);
        $w .= " AND privacy IN ({$privacy}) ";
        $whereClause .= ($whereClause) ? " AND {$w} ": " {$w}";
    }
    if ($filter and $filter == 'featured') $whereClause .= $whereClause ? " AND featured = 1 " : " featured = 1 ";
    if ($term) $whereClause  .= $whereClause ? " AND (title LIKE '%{$term}%' OR description LIKE '%{$term}%') " : " (title LIKE '%{$term}%' OR description LIKE '%{$term}%')";
    if ($type == 'browse') {
        $privacyClause = "(privacy = 1 OR user_id = '{$userid}' ";
        $users = array(0);
        $users = array_merge($users, get_following($userid));
        $users = array_merge($users, get_friends($userid));
        $users = implode(',', $users);
        $privacyClause .= " OR (privacy = '2'  AND user_id IN ({$users}))) ";
        $whereClause .= ($whereClause) ? " AND {$privacyClause} " : " {$privacyClause} ";
    }
    if ($whereClause) $sql .= " WHERE {$whereClause} ";
    if ($type != 'mine') $sql .= " AND status = '1'";
    if ($withTitle) $sql .= " AND title != '' ";
    if ($filter and $filter == 'top') {
        $sql .= " ORDER BY play_count desc";
    } else {
        $sql .= " ORDER BY time desc";
    }
    $limit = ($limit)  ? $limit : config('music-list-limit', 10);
    return paginate($sql, $limit);
}

function get_playlist_musics($id) {
    $playlist_musics = array();
    $playlist = get_playlist($id);
    if(!$playlist) return false;
    $musics = unserialize($playlist['musics']) ? unserialize($playlist['musics']) : array();
    foreach($musics as $slug) {
        $music = get_music($slug);
        $music['file_path'] = fire_hook('filter.url', url($music['file_path']));
        if(music_exists($slug)) {
            $playlist_musics[$music['slug']] = $music;
        }
    }
    return $playlist_musics;
}

function get_playlist($id) {
    $db = db();
    $query = $db->query("SELECT * FROM music_playlists WHERE id = '{$id}' OR slug = '{$id}' LIMIT 1");
    if ($query) return $query->fetch_assoc();
    return false;
}

function is_playlist_owner($playlist) {
    if (!is_loggedIn()) return false;
    if ($playlist['user_id'] == get_userid()) return true;
    return false;
}

function save_playlist($val, $playlist) {
    $expected = array('title' => '', 'description' => '', 'featured' => $playlist['featured'], 'privacy' => $playlist['privacy']);
    /**
     * @var $title
     * @var $description
     * @var $musics
     * @var $cover_art
     * @var $featured
     * @var $privacy
     */
    extract(array_merge($expected, $val));
    $musics = serialize($musics);
    $playlistId = $playlist['id'];
    db()->query("UPDATE music_playlists SET title = '{$title}', description = '{$description}',  musics = '{$musics}', featured = '{$featured}', privacy = '{$privacy}' WHERE id = '{$playlistId}'");
    fire_hook('playlist.admin.edited', null, array($playlistId));
    return true;
}

function get_playlist_url($playlist) {
    return url_to_pager('music-playlist-page', array('id' => $playlist['slug']));
}

function delete_playlist($id) {
    db()->query("DELETE FROM music_playlists WHERE id = '{$id}'");
    return true;
}

function add_playlist($val) {
    /**
     * @var $title
     * @var $description
     * @var $musics
     * @var $entity_id
     * @var $entity_type
     * @var $privacy
     * @var $status
     */
    $expected = array('title' => '', 'description' => '', 'privacy' => 1, 'status' => 1, 'entity_type' => 'user', 'entity_id' => get_userid());
    extract(array_merge($expected, $val));
    $result = array('result' => true);
    $result = fire_hook('can.post.playlist', $result, array($entity_type, $entity_id));
    if (!$result['result']) return false;
    $time = time();
    $userid = get_userid();
    $slug = toAscii($title);
    if (empty($slug)) $slug = md5(time());
    if (playlist_exists($slug)) {
        $slug = md5($slug.time());
    }
    $musics = serialize($musics);
    $db = db();
    $db->query("INSERT INTO music_playlists (title, slug, description, user_id, entity_type, entity_id, musics, status, privacy, time) VALUES('{$title}', '{$slug}', '{$description}', '{$userid}', '{$entity_type}', '{$entity_id}', '{$musics}', '{$status}', '{$privacy}', '{$time}')");
    //exit($db->error);
    $playlistId = db()->insert_id;
    $playlist = get_playlist($playlistId);
    fire_hook('playlist.added', null, array($playlist, $playlistId));
    return $playlist;
}

function playlist_exists($slug) {
    $query = db()->query("SELECT slug FROM music_playlists WHERE slug = '{$slug}' LIMIT 1");
    if ($query and $query->num_rows > 0) return true;
    return false;
}


function get_playlist_owner($playlist) {
    $result = array('name' => '', 'image' => '', 'link' => '', 'id' => '');
    if ($playlist['entity_type'] == 'user')  {
        $user = find_user($playlist['user_id'], false);
        $result['name'] = get_user_name($user);
        $result['image'] = get_avatar(200, $user);
        $result['link'] = profile_url(null, $user);
        $result['id'] = $user['id'];
    }
    return fire_hook('get.music.owner', $result);
}


function get_related_playlist($playlist, $limit = 6) {
    $title = $playlist['title'];
    $explode = explode(" ", $title);
    $playlistId = $playlist['id'];
    $sql = "SELECT * FROM music_playlists WHERE id != '{$playlistId}'  AND (";
    $where = "";
    foreach($explode as $t) {
        $where .= ($where) ? " OR title LIKE '%{$t}%' " : "title LIKE '%{$t}%'";
    }
    $sql .= $where.') ORDER BY time desc';
    return paginate($sql, $limit);

}


function get_related_playlists($playlist, $limit = 6) {
    $title = $playlist['title'];
    $explode = explode(" ", $title);
    $playlistId = $playlist['id'];
    $sql = "SELECT * FROM music_playlists WHERE id != '{$playlistId}'  AND (";
    $where = "";
    foreach($explode as $t) {
        $where .= ($where) ? " OR title LIKE '%{$t}%' " : "title LIKE '%{$t}%'";
    }
    $sql .= $where.') ORDER BY time desc';
    return paginate($sql, $limit);

}

function count_musics() {
    $db = db();
    $num_musics = $db->query("SELECT COUNT(id) FROM musics");
    if($db->error){return 0;}else{return $num_musics->fetch_row()[0];}
}

function count_playlists() {
    $db = db();
    $num_playlists = $db->query("SELECT COUNT(id) FROM music_playlists");
    if($db->error){return 0;}else{return $num_playlists->fetch_row()[0];}
}

function count_playlist($id) {
    $playlist_musics = get_playlist_musics($id);
    return $playlist_musics ? count(get_playlist_musics($id)) : 0;
}


function get_all_playlists($term, $limit = 10) {
    $sql = "SELECT * FROM music_playlists ";
    $where = "";
    if ($term) $where  .= ($where) ? " AND (title LIKE '%{$term}%' OR description LIKE '%{$term}%') " : " (title LIKE '%{$term}%' OR description LIKE '%{$term}%')";
    if ($where) $sql .= "WHERE {$where} ";
    $sql .= "ORDER BY time desc";
    return paginate($sql, $limit);
}
