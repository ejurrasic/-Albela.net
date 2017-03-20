<?php
function cdn_add_server($val) {
    $expected = array(
        'name' => '',
        'desc' => '',
        'engine' => '',
    );

    /**
     * @var $name
     * @var $desc
     * @var $engine
     */
    extract(array_merge($expected, $val));

    $settings = input('val.'.$engine);
    $settings = perfectSerialize($settings);
    db()->query("INSERT INTO cdn_servers (name,description,type,settings)VALUES(
        '{$name}','{$desc}','{$engine}','{$settings}'
    )");

    $id = db()->insert_id;
    fire_hook("cdn.add", null, array($id, $val));
    forget_cache("cdn-lists");
    return true;
}

function cdn_save_server($val, $id) {
    $expected = array(
        'name' => '',
        'desc' => '',
        'engine' => '',
    );

    /**
     * @var $name
     * @var $desc
     * @var $engine
     */
    extract(array_merge($expected, $val));

    $settings = input('val.'.$engine);
    $settings = perfectSerialize($settings);
    db()->query("UPDATE cdn_servers SET name='{$name}',description='{$desc}',type='{$engine}',settings='{$settings}' WHERE id='{$id}'");

    fire_hook("cdn.edit", null, array($id, $val));
    forget_cache("cdn-server-". $id);
    return true;
}

function get_cdn_servers() {
    $cacheName = "cdn-lists";
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT id,name,description,type,settings FROM cdn_servers WHERE status='1'");
        $results = fetch_all($query);
        set_cacheForever($cacheName, $results);

        return $results;
    }
}

function get_cdn_server($id) {
    $cacheName = "cdn-server-". $id;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT id,name,description,type,settings,status FROM cdn_servers WHERE  id='{$id}'");
        $results = $query->fetch_assoc();
        set_cacheForever($cacheName, $results);

        return $results;
    }
}

function get_usable_cdn() {
    $servers = get_cdn_servers();
    if (!count($servers)) return false;
    shuffle($servers);
    $rand = array_rand($servers, 1);
    return $servers[$rand];
}

function list_cdn_servers() {
    return  fetch_all(db()->query("SELECT * FROM cdn_servers"));
}

function cdn_get_server($id) {
    $query = db()->query("SELECT * FROM cdn_servers WHERE id='{$id}'");
    return $query->fetch_assoc();
}
 