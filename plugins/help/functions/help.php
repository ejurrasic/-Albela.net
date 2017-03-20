<?php

function add_help($val) {
    $expected = array(
        'title' => '',
        'slug' => '',
        'category' => ''
    );

    /**
     * @var $title
     * @var $slug
     * @var $category
     * @var $content
     * @var $tags
     */
    extract(array_merge($expected, $val));

    $time = time();
    $content = lawedContent(stripslashes($content));
    db()->query("INSERT INTO helps (title,slug,content,tags,category,modify_time,time)VALUES(
        '{$title}','{$slug}','{$content}','{$tags}','{$category}','{$time}','{$time}'
    )");

    fire_hook('help.add', null, array(db()->insert_id));
    return db()->insert_id;
}

function save_help($val, $id) {
    $expected = array(
        'title' => '',
        'slug' => '',
        'category' => ''
    );

    /**
     * @var $title
     * @var $slug
     * @var $category
     * @var $content
     * @var $tags
     */
    extract(array_merge($expected, $val));

    $time = time();
    $content = addslashes($content);
    $content = lawedContent(stripslashes($content));
    db()->query("UPDATE helps SET title='{$title}',content='{$content}',category='{$category}',tags='{$tags}',modify_time='{$time}' WHERE id='{$id}'");
    fire_hook('help.edit', null, array($id));
    return true;
}

function delete_help($id) {
    db()->query("DELETE FROM helps WHERE id='{$id}' OR category='{$id}'");
    return true;
}
function help_slug_exists($slug, $category = false) {
    $sql = "SELECT id FROM helps WHERE slug='{$slug}' AND category='{$category}'";
    $q = db()->query($sql);
    return $q->num_rows;
}

function get_help($slug, $category = null) {
    $es = null;
    if ($category) {
        $es = " AND category='{$category}'";
    }
    $query = db()->query("SELECT * FROM helps WHERE (id='{$slug}' OR slug='{$slug}') {$es}");
    return $query->fetch_assoc();
}


function get_help_categories() {
    $query = db()->query("SELECT * FROM helps WHERE category='0' ORDER BY help_order ASC");
    return fetch_all($query);
}

function get_helps($term = null) {
    $sql = "SELECT * FROM helps WHERE category!='0'";
    if ($term) {
        $sql .= " AND  (title LIKE '%{$term}%' OR tags LIKE '%{$term}%')";
    }

    return paginate($sql);
}

function get_category_helps($id) {
    $query = db()->query("SELECT * FROM helps WHERE category='{$id}'");
    return fetch_all($query);
}

function update_help_category_order($id, $order) {
    db()->query("UPDATE helps SET help_order='{$order}' WHERE id='{$id}'");
    return true;
}