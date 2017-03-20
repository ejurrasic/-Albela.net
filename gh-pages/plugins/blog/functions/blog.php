<?php
function add_blog($val) {
    $expected = array(
        'title' => '',
        'slug' => '',
        'content' => '',
        'tags' => '',
        'category' => '',
        'privacy' => 1
    );
    /**
     * @var $title
     * @var $slug
     * @var $content
     * @var $tags
     * @var $status
     * @var $category
     * @var $privacy
     *
     */
    extract(array_merge($expected, $val));

    $image = '';
    $file = input_file('image');
    if ($file) {
        $uploader = new Uploader($file);
        if ($uploader->passed()) {
            $uploader->setPath('blogs/preview/');
            $image = $uploader->resize(700, 500)->result();
        }
    }

    $time = time();
    $userid = get_userid();
    $content = lawedContent(stripslashes($content));
    db()->query("INSERT INTO blogs (user_id,title,slug,content,image,tags,update_time,time,status,category_id,privacy)VALUES(
        '{$userid}','{$title}','{$slug}','{$content}','{$image}','{$tags}','{$time}','{$time}','{$status}','{$category}','{$privacy}'
    )");

    $blogId = db()->insert_id;
    fire_hook("blog.added", null, array($blogId, $val));
    return $blogId;
}

function save_blog($val, $blog, $admin = false) {
    $expected = array(
        'title' => '',
        'slug' => '',
        'content' => '',
        'tags' => '',
        'category' => '',
        'privacy' => 1,
        'featured' => $blog['featured']
    );
    /**
     * @var $title
     * @var $slug
     * @var $content
     * @var $tags
     * @var $status
     * @var $category
     * @var $privacy
     * @var $featured
     */
    if (!$admin) $val['featured'] = $blog['featured'];
    extract(array_merge($expected, $val));
    $image = $blog['image'];
    $id = $blog['id'];
    $file = input_file('image');
    if ($file) {
        $uploader = new Uploader($file);
        if ($uploader->passed()) {
            $uploader->setPath('blogs/preview/');
            $image = $uploader->resize(700, 500)->result();
        }
    }

    $time = time();
    $content = lawedContent(stripslashes($content));
    db()->query("UPDATE blogs SET featured='{$featured}',image='{$image}',title='{$title}',tags='{$tags}',content='{$content}',status='{$status}',update_time='{$time}',privacy='{$privacy}',category_id='{$category}' WHERE id='{$id}'");

    return true;
}

function get_blog($id) {
    $db = db()->query("SELECT * FROM blogs WHERE slug = '{$id}'");
    if($db->num_rows == 0) {
        $db = db()->query("SELECT * FROM blogs WHERE id='{$id}'");
    }
    return $db->fetch_assoc();
}

function is_blog_owner($blog) {
    if (!is_loggedIn()) return false;
    if ($blog['user_id'] == get_userid()) return true;
    return false;
}
function delete_blog($id) {
    $blog = get_blog($id);
    if ($blog['image']) delete_file(path($blog['image']));
    return db()->query("DELETE FROM blogs WHERE id='{$id}'");
}
function get_blogs($type, $category = null, $term = null, $userid = null, $limit = 10, $filter = 'all', $blog = null) {
    $sql = 'SELECT * FROM blogs ';

    if ($type == 'mine') {
        $status = false;
        if ($userid) {
            $status = true;
        }
        $userid = ($userid) ? $userid : get_userid();
        $sql .= " WHERE user_id='{$userid}' ";
        if ($status) $sql .= " AND status='1' ";
        if ($filter == 'featured') $sql .= " AND featured = '1' ";
    } elseif($type == 'related'){
        $title = $blog['title'];
        $explode = explode(' ', $title);
        $w = "";
        foreach($explode as $t) {
            $w .=  ($w)  ? " OR  (title LIKE '%{$t}%' OR content LIKE '%{$t}') " : "  (title LIKE '%{$t}%' OR content LIKE '%{$t}')";
        }
        $blogId = $blog['id'];
        $sql .= " WHERE ($w) AND status='1' AND id!='{$blogId}'";
    }else {
        if($term and !$category) {
            $sql .= " WHERE (title LIKE '%{$term}%' OR content LIKE '%{$term}') AND status='1'";
            if ($filter == 'featured') $sql .= " AND featured = '1' ";
        }
        elseif ($term and $category != 'all') {
            $sql .= " WHERE category_id='{$category}' AND (title LIKE '%{$term}%' OR content LIKE '%{$term}') AND status='1'";
            if ($filter == 'featured') $sql .= " AND featured = '1' ";
        } elseif($term and $category == 'all') {
            $sql .= " WHERE (title LIKE '%{$term}%' OR content LIKE '%{$term}') AND status='1'";
            if ($filter == 'featured') $sql .= " AND featured = '1' ";
        } elseif($category and $category != 'all') {
            $sql .= " WHERE status='1' AND category_id='{$category}'";
            if ($filter == 'featured') $sql .= " AND featured = '1' ";
        }
        else {
            $sql .= " WHERE status='1'";
            if ($filter == 'featured') $sql .= " AND featured = '1' ";
        }
    }


    if ($filter == 'top') {
        $sql .= " ORDER BY views desc";
    } else {
        $sql .= " ORDER BY time desc";
    }

    return paginate($sql, $limit);
}

function admin_get_blogs($term = null, $limit = 10) {
    $sql = '';

    if ($term) $sql .= " WHERE title LIKE '%{$term}%' OR content LIKE '%{$term}%' OR tags LIKE '%{$term}%'";
    return paginate("SELECT * FROM blogs ".$sql. " ORDER BY TIME DESC", $limit);
}

function count_total_blogs() {
    $query = db()->query("SELECT * FROM blogs");
    return $query->num_rows;
}

function blog_add_category($val) {
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     * @var $desc
     */
    extract(array_merge($expected, $val));
    $titleSlug = "blog_category_".md5(time().serialize($val)).'_title';

    foreach($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'blog');
    }


    $time = time();
    $order = db()->query('SELECT id FROM blog_categories');
    $order = $order->num_rows;
    $query = db()->query("INSERT INTO `blog_categories`(
            `title`,`category_order`) VALUES(
            '{$titleSlug}','{$order}'
            )
        ");

    return true;
}

function save_blog_category($val, $category) {
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     */
    extract(array_merge($expected, $val));
    $titleSlug = $category['title'];

    foreach($title as $langId => $t) {
        (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, ' blog') : add_language_phrase($titleSlug, $t, $langId, 'blog');
    }

    return true;
}

function get_blog_categories() {
    $query = db()->query("SELECT * FROM `blog_categories` ORDER BY `category_order` ASC");
    return fetch_all($query);
}

function get_blog_category($id) {
    $query = db()->query("SELECT * FROM `blog_categories` WHERE `id`='{$id}'");
    return $query->fetch_assoc();
}

function delete_blog_category($id, $category) {
    delete_all_language_phrase($category['title']);

    db()->query("DELETE FROM `blog_categories` WHERE `id`='{$id}'");

    return true;
}

function update_blog_category_order($id, $order) {
    db()->query("UPDATE `blog_categories` SET `category_order`='{$order}' WHERE  `id`='{$id}'");
}

function blog_slug_exists($slug) {
    $query = db()->query("SELECT COUNT(id) FROM `blogs` WHERE  `slug`='{$slug}'");
    $result = $query->fetch_row();
    return $result[0] == 0 ? FALSE : TRUE;
}