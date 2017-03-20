<?php

function add_game($val, $gameFile, $logoFile) {
    /**
     * @var $category
     * @var $title
     * @var $game_name
     * @var $description
     * @var $code
     * @var $width
     * @var $height
     */
    extract($val);
    $category = sanitizeText($category);
    $title = sanitizeText($title);
    $game_name = sanitizeText($game_name);
    $description = sanitizeText($description);
    $width = sanitizeText($width);
    $height = sanitizeText($height);
    $time = time();
    $userid = get_userid();
    $code = input('val.code', '', false);
    if (!can_embed_game()) $code = '';
    $query = db()->query("INSERT INTO games (game_title,game_name,game_logo,user_id,category_id,game_file,game_code,game_width,game_height,game_description,time) VALUES(
       '".mysqli_real_escape_string(db(), $title)."','".mysqli_real_escape_string(db(), $game_name)."','".mysqli_real_escape_string(db(), $logoFile)."','".mysqli_real_escape_string(db(), $userid)."','".mysqli_real_escape_string(db(), $category)."','".mysqli_real_escape_string(db(), $gameFile)."','".mysqli_real_escape_string(db(), $code)."','".mysqli_real_escape_string(db(), $width)."','".mysqli_real_escape_string(db(), $height)."','".mysqli_real_escape_string(db(), $description)."','".mysqli_real_escape_string(db(), $time)."'
    )");

    $gameId = db()->insert_id;

    //testing for non-english page url
    $slug = toAscii($game_name);
    if (!preg_match('/^[\pL\pN]+$/u', $game_name) or empty($slug) or strlen($game_name) != strlen($slug)) {
        $newSlug = 'game-'.$gameId;
        if ($slug) {
            $validator = validator(array('slug' => $slug), array('slug' => 'username'));
            if (validation_passes()) {
                $newSlug = $slug;
            }
        }

        db()->query("UPDATE games SET game_name='{$newSlug}' WHERE game_id='{$gameId}'");
    }


    fire_hook('game.added', null, array($gameId));
    return $gameId;
}

function save_game($val, $gameFile, $logoFile, $game) {

    /**
     * @var $category
     * @var $title
     * @var $game_name
     * @var $description
     * @var $code
     * @var $width
     * @var $height
     */
    extract($val);
    $category = sanitizeText($category);
    $title = sanitizeText($title);
    $description = sanitizeText($description);
    $width = sanitizeText($width);
    $height = sanitizeText($height);
    $gameId = $game['game_id'];
    if (!can_embed_game()) $code = '';
    db()->query("UPDATE games SET game_title='{$title}',game_description='{$description}',game_code='{$code}',game_file='{$gameFile}',
    game_logo='{$logoFile}',game_width='{$width}',game_height='{$height}',category_id='{$category}' WHERE game_id='{$gameId}'");

    fire_hook('game.updated', null, array($game));
    return true;
}

function find_game($id) {
    $sql = "SELECT * FROM games WHERE game_id='{$id}' OR game_name='{$id}'";
    $query = db()->query($sql);
    return arrange_game($query->fetch_assoc());
}

function can_create_game() {

    if (is_admin()) return true;
    if (user_has_permission('can-create-game')) return true;
    return false;
}

function can_embed_game() {
    if (is_admin()) return true;
    if (user_has_permission('can-embed-game')) return true;
    return false;
}

function arrange_game($game) {
    if (!$game) return false;
    $category = get_game_category($game['category_id']);
    if ($category)  $game['category'] = $category;;
    $owner = find_user($game['user_id'], false);
    $game['owner'] = $owner;

    return $game;
}

function add_game_player($userid, $game) {
    $players = ($game['players']) ? unserialize($game['players']) : array();
    $players[] = $userid;
    $players = serialize($players);
    $gameId = $game['game_id'];
    db()->query("UPDATE games SET players='{$players}',players_count= players_count + 1 WHERE game_id='{$gameId}'");
    return true;
}

function get_game_players($game, $limit = 10) {
    $players = ($game['players']) ? unserialize($game['players']) : array();
    $players[] = 0;
    $players = implode(',', $players);

    $query = "SELECT id,username,first_name,last_name,avatar FROM users WHERE id IN ({$players})";

    return paginate($query, $limit);
}

function count_total_games() {
    $q = db()->query("SELECT game_id FROM games");
    return $q->num_rows;
}

function get_games($type = 'all', $term = null, $limit = 12) {
    $sql = "SELECT * FROM games ";
    switch($type) {
        case 'saved' :
            $saved = get_user_saved('game');
            $saved[] = 0;
            $saved = implode(',', $saved);
            $sql .= " WHERE game_id IN ({$saved})";
            break;
        case 'me':
            $playedGames = get_privacy('played-games', array());
            $playedGames[] = 0;
            $playedGames = implode(',', $playedGames);
            $userid = get_userid();
            $sql .= " WHERE game_id IN ($playedGames) OR user_id='{$userid}'";
            break;
        case 'cat':
            $sql .= " WHERE category_id='{$term}'";
            break;
        case 'search':
            $sql .= " WHERE game_title LIKE '%{$term}%' ";
            break;
        case 'featured':
            $sql .= " WHERE featured = 1";
            break;
    }

    switch($type) {
        case 'top' :
            $sql .= " ORDER BY players_count DESC";
            break;
        case 'latest' :
            $sql .= " ORDER BY game_id DESC";
            break;
            $sql .= " ORDER BY time DESC";
        default :
            break;
    }
    return paginate($sql, $limit);
}

function game_url($slug = null, $game = null) {
    return url_to_pager("game-profile", array('slug' => $game['game_name'])).'/'.$slug;
}

function get_game_cover($game = null, $original = true) {
    $default = img("images/cover.jpg");
    if (!$original and !empty($game['game_cover_resized'])) return url_img($game['game_cover_resized']);
    if (!empty($game['game_cover'])) return url_img($game['game_cover']);
    return ($original) ? '' : $default;
}

function get_game_logo($size, $game = null) {
    $avatar = $game['game_logo'];
    if ($avatar) {
        return url(str_replace('%w', $size, $avatar));
    } else {

        return $image  = img("images/page-avatar.png");
    }
}

function get_game_details($index, $game = null) {
    $game = ($game) ? $game : app()->profileGame;
    if (isset($game[$index])) return $game[$index];
    return false;
}

function is_game_admin($game) {
    if (!is_loggedIn()) return false;
    if (is_admin() or $game['user_id'] == get_userid()) return true;
    return false;
}

function update_game_details($fields, $gameId) {
    $sqlFields = "";
    foreach($fields as $key => $value) {
        $value = sanitizeText($value);
        $sqlFields .= (empty($sqlFields)) ? "`{$key}`='{$value}'" : ",`{$key}`='{$value}'";
    }
    db()->query("UPDATE `games` SET {$sqlFields} WHERE `game_id`='{$gameId}'");
    //exit(db()->error);
    fire_hook("game.updated", array($gameId));
}

function game_add_category($val, $cover = '') {
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     * @var $desc
     */
    extract(array_merge($expected, $val));
    $titleSlug = "game_category_".md5(time().serialize($val)).'_title';

    foreach($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'game');
    }


    $time = time();
    $order = db()->query('SELECT id FROM game_categories');
    $order = ($order) ? $order->num_rows : 1;
    $query = db()->query("INSERT INTO `game_categories`(
            `title`,`cover`,`category_order`) VALUES(
            '{$titleSlug}','{$cover}','{$order}'
            )
        ");

    return true;
}

function save_game_category($val, $image = null, $category) {
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     */
    extract(array_merge($expected, $val));
    $titleSlug = $category['title'];

    foreach($title as $langId => $t) {
        (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'game') : add_language_phrase($titleSlug, $t, $langId, 'game');
    }

    if ($image) {
        $catId = $category['id'];
        db()->query("UPDATE game_categories SET cover='{$image}' WHERE id='{$catId}'");
    }

    return true;
}

function get_game_categories() {
    $query = db()->query("SELECT * FROM `game_categories` ORDER BY `category_order` ASC");
    return fetch_all($query);
}

function get_game_category($id) {
    $query = db()->query("SELECT * FROM `game_categories` WHERE `id`='{$id}'");
    return $query->fetch_assoc();
}

function delete_game($game) {
    $gameId = $game['game_id'];
    //delete cover images
    if ($game['game_cover']) delete_file(path($game['game_cover']));
    if ($game['game_logo']) delete_file(path($game['game_logo']));
    if ($game['game_cover_resized']) delete_file(path($game['game_cover_resized']));

    //delete comments
    if (plugin_loaded('comment')) delete_comments('game', $gameId);
    //now delete the event itself
    db()->query("DELETE FROM games WHERE game_id='{$gameId}'");

    return true;

}

function delete_game_category($id, $category) {
    delete_all_language_phrase($category['title']);
    delete_file($category['cover']);
    db()->query("DELETE FROM `game_categories` WHERE `id`='{$id}'");

    return true;
}

function update_game_category_order($id, $order) {
    db()->query("UPDATE `game_categories` SET `category_order`='{$order}' WHERE  `id`='{$id}'");
}

function game_suggestion($limit) {
    $db = db()->query("SELECT game_id,game_title,game_name,game_logo FROM games ORDER BY rand() LIMIT {$limit}");
    return fetch_all($db);

}
