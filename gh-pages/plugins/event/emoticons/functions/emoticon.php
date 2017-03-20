<?php
function add_emoticon($val) {
    $expected = array(
        'title' => '',
        'symbol' => '',
        'width' => '',
        'height' => '',
        'icon' => '',
        'category' => 1
    );

    /**
     * @var $title
     * @var $symbol
     * @var $width
     * @var $height
     * @var $icon
     * @var $category
     */
    extract(array_merge($expected, $val));

    $query = db()->query("INSERT INTO emoticons (name,category,symbol,path,width,height)VALUES(
    '{$title}','{$category}','{$symbol}','{$icon}','{$width}','{$height}'
    )");
    forget_cache("emoticons-1");
    forget_cache("emoticons-2");
    fire_hook("emoticons.added", null, array($val));
    return true;
}

function save_emoticon($val, $emoticon) {
    $expected = array(
        'title' => '',
        'symbol' => '',
        'width' => '',
        'height' => '',
        'icon' => '',
        'category' => 1
    );

    /**
     * @var $title
     * @var $symbol
     * @var $width
     * @var $height
     * @var $icon
     * @var $category
     */
    extract(array_merge($expected, $val));
    //if (emoticon_exists($symbol)) return false;
    $sql = "name='{$title}',symbol='{$symbol}',width='{$width}',height='{$height}', category='{$category}'";
    if ($icon) $sql .= ",path='{$icon}'";
    db()->query("UPDATE emoticons SET {$sql} WHERE id='{$emoticon}'");
    //exit(db()->error);
    forget_cache("emoticons-1");
    forget_cache("emoticons-2");
    //exit(db()->error);
    fire_hook("emoticons.edited", null, array($emoticon, $val));
    return true;
}

function emoticon_exists($symbol) {
    $query = db()->query("SELECT id FROM emoticons WHERE symbol='{$symbol}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}
function list_emoticons($type) {
    $type = ($type == 'emoticons') ? 1 : 2;
    $query = db()->query("SELECT * FROM emoticons WHERE category='{$type}'");
    return fetch_all($query);
}

function get_emoticon($id) {
    $query = db()->query("SELECT * FROM emoticons WHERE id='{$id}'");
    return $query->fetch_assoc();
}

function delete_emoticon($id) {
    forget_cache("emoticons-1");
    forget_cache("emoticons-2");
    return db()->query("DELETE FROM emoticons WHERE id='{$id}'");
}

function get_emoticons($type) {
    $cacheName = "emoticons-".$type;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT * FROM emoticons WHERE category='{$type}'");
        $list = array();
        while($fetch = $query->fetch_assoc()) {
            $list[$fetch['symbol']] = $fetch;
        }

        set_cacheForever($cacheName, $list);
        return $list;
    }
}

function find_emoticons($term) {
    $query = db()->query("SELECT * FROM emoticons WHERE name LIKE '%{$term}%' OR symbol='{$term}'");
    $list = array();
    while($fetch = $query->fetch_assoc()) {
        $list[$fetch['symbol']] = $fetch;
    }
    return $list;
}