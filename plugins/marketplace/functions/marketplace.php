<?php
function marketplace_get_categories(){
    $db = db();
    $categories = $db->query("SELECT * FROM marketplace_categories ORDER BY id");
    return fetch_all($categories);
}

function marketplace_is_category_exist($category_id){
    $db = db();
    if($db->query("SELECT id FROM marketplace_categories WHERE id = ".mysqli_real_escape_string(db(),$category_id))->num_rows == 0){
        return false;
    }
    else{
        return true;
    }
}

function marketplace_get_category($category_id){
    $db = db();
    $category = $db->query("SELECT id, title FROM marketplace_categories WHERE id = ".mysqli_real_escape_string(db(),$category_id));
    if($category->num_rows > 0){
        return fetch_all($category);
    }
    else{
        return false;
    }
}

function marketplace_get_listings($category_id, $search, $type, $page, $limit, $admin = false){
    $db = db();
    $admin_sql = $admin ? '' : ' AND marketplace_listings.approved = 1 AND marketplace_listings.active = 1';
    $admin_sql = $type == 'm' ? ' AND marketplace_listings.active = 1' : $admin_sql;
    $category_id_sql = $category_id ? ' AND marketplace_listings.category_id = '.mysqli_real_escape_string(db(),$category_id) : '';
    $type_sql = $type == 'm' ? ' AND marketplace_listings.lister_id = '.mysqli_real_escape_string(db(),get_userid()) : '';
    $type_sql = $type == 'p' ? ' AND marketplace_listings.approved = 0' : $type_sql;
    $type_sql = $type == 'f' ? ' AND marketplace_listings.featured = 1' : $type_sql;
    $type_sql = is_numeric($type) ? ' AND marketplace_listings.lister_id = '.$type : $type_sql;
    $search_sql = $search ? " AND (marketplace_listings.title LIKE '%".mysqli_real_escape_string(db(),$search)."%' OR marketplace_listings.tags LIKE '%".$search."%')" : '';
    $where_sql = $category_id_sql.$type_sql.$search_sql.$admin_sql;
    $query = "
		SELECT DISTINCT marketplace_listings.id, marketplace_listings.slug, marketplace_listings.title, marketplace_listings.description, marketplace_listings.date, marketplace_listings.lister_id, marketplace_listings.category_id, marketplace_listings.tags, marketplace_listings.image, marketplace_listings.address, marketplace_listings.link, marketplace_listings.nov, marketplace_listings.last_viewed, marketplace_listings.price, marketplace_listings.featured, marketplace_listings.approved, marketplace_listings.active, marketplace_categories.title AS category_title, users.username
		FROM marketplace_listings
		LEFT JOIN marketplace_categories
		ON marketplace_listings.category_id = marketplace_categories.id
		LEFT JOIN users
		ON marketplace_listings.lister_id = users.id
		WHERE 1 = 1 {$where_sql}
		ORDER BY date DESC";
    $listings = paginate($query, $limit);
    return $listings;
}

function marketplace_get_listing($listing_id, $type = 'm', $admin = false){
    $db = db();
    $admin_sql = $admin ? '' : ' AND marketplace_listings.approved = 1 AND marketplace_listings.active = 1';
    $admin_sql = $type == 'm' ? ' AND marketplace_listings.active = 1' : $admin_sql;
    $where_sql = $admin_sql;
    $listing = $db->query("
        SELECT DISTINCT marketplace_listings.id, marketplace_listings.slug, marketplace_listings.title, marketplace_listings.description, marketplace_listings.date, marketplace_listings.category_id, marketplace_listings.tags, marketplace_listings.address, marketplace_listings.link, marketplace_listings.image, marketplace_listings.lister_id, marketplace_listings.nov, marketplace_listings.last_viewed, marketplace_listings.price, marketplace_listings.featured, marketplace_listings.approved, marketplace_listings.active, marketplace_categories.title AS category_title, users.username
		FROM marketplace_listings
		LEFT JOIN marketplace_categories
		ON marketplace_listings.category_id = marketplace_categories.id
		LEFT JOIN users
		ON marketplace_listings.lister_id = users.id
		WHERE marketplace_listings.id = ".mysqli_real_escape_string(db(), $listing_id).$where_sql);
    return fetch_all($listing);
}

function marketplace_get_listing_images($listing_id){
    $db = db();
    $listing_images = $db->query("SELECT id, image FROM marketplace_images WHERE listing_id = ".$listing_id);
    return fetch_all($listing_images);
}

function marketplace_execute_form($post_vars){
    $db = db();
    $type = isset($post_vars['type']) ? $post_vars['type'] : null;
    $errors = array();
    switch($type){
        case 'add_category':
            $expected = array('title' => '');
            extract(array_merge($expected, $post_vars));
            $titleSlug = "marketplace_category_".md5(time().serialize($post_vars)).'_title';
            $slug = marketplace_unique_slugger(lang($titleSlug), 'category');
            $db->query("INSERT INTO marketplace_categories(slug, title) VALUES('".mysqli_real_escape_string(db(),$slug)."', '".mysqli_real_escape_string(db(),$titleSlug)."')");
            foreach($title as $langId => $t){
                add_language_phrase($titleSlug, $t, $langId, 'marketplace');
            }
            marketplace_update_slugs('category');
            break;

        case 'edit_category':
            $expected = array('title' => '');
            extract(array_merge($expected, $post_vars));
            $category = marketplace_get_category($post_vars['category_id'])[0];
            $titleSlug = $category['title'];
            $slug = marketplace_unique_slugger(lang($titleSlug), 'category');
            $db->query("UPDATE marketplace_categories SET slug = '".mysqli_real_escape_string(db(),$slug)."' WHERE id = ".mysqli_real_escape_string(db(),$post_vars['category_id']));
            foreach($title as $langId => $t){
                (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'marketplace') : add_language_phrase($titleSlug, $t, $langId, 'marketplace');
            }
            marketplace_update_slugs('category');
            break;

        case 'delete_category':
            $db = db();
            $category = marketplace_get_category($post_vars['category_id'])[0];
            delete_all_language_phrase($category['title']);
            $new_category_id = $post_vars['new_category_id'] == 'NULL' ? $post_vars['category_id'] : $post_vars['new_category_id'];

            $db->query("DELETE FROM marketplace_categories WHERE id = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['category_id'])));
            $db->query("UPDATE marketplace_listings SET category_id = ".$new_category_id." WHERE category_id = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['category_id'])));
            break;

        case 'create_listing':
            $db = db();

            $slug = marketplace_unique_slugger($post_vars['title'], 'listing');
            $db->query("INSERT INTO marketplace_listings (slug, title, description, date, category_id, lister_id, tags, address, link, price, featured, image, approved, active) VALUES ('".stripslashes(mysqli_real_escape_string(db(),marketplace_unique_slugger($post_vars['title'], 'listing')))."', '".stripslashes(mysqli_real_escape_string(db(),$post_vars['title']))."', '".stripslashes(mysqli_real_escape_string(db(),$post_vars['description']))."', '".stripslashes(mysqli_real_escape_string(db(),date('Y-m-d H:i:s')))."', ".stripslashes(mysqli_real_escape_string(db(),$post_vars['category_id'])).", ".stripslashes(mysqli_real_escape_string(db(),get_userid())).", '".stripslashes(mysqli_real_escape_string(db(),$post_vars['tags']))."', '".stripslashes(mysqli_real_escape_string(db(),$post_vars['address']))."', '".stripslashes(mysqli_real_escape_string(db(),$post_vars['link']))."', '".stripslashes(mysqli_real_escape_string(db(),$post_vars['price']))."', 0, '".stripslashes(mysqli_real_escape_string(db(),$post_vars['image_path']))."', ".stripslashes(mysqli_real_escape_string(db(),config('default-approval', 1))).", 1)");
            fire_hook('marketplace.create', null, array($type = 'marketplace.create', $type_id = $db->insert_id, $text = $post_vars['title']));
            //exit($db->error);
            return $db->insert_id;

            break;

        case 'edit_listing':
            $db = db();
            $slug = marketplace_unique_slugger($post_vars['title'], 'listing');
            $title = $post_vars['title'];

            marketplace_update_slugs('listing');
            $db->query("UPDATE marketplace_listings SET title = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['title']))."', description = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['description']))."', category_id = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['category_id'])).", tags = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['tags']))."', address = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['address']))."', link = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['link']))."', price = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['price']))."', image = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['image_path']))."' WHERE id = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['listing_id'])));

            break;

        case 'edit_listing_admin':
            $db = db();

            $slug = marketplace_unique_slugger($post_vars['title'], 'listing');
            $title = $post_vars['title'];

            marketplace_update_slugs('listing');

            $db->query("UPDATE marketplace_listings SET title = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['title']))."', description = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['description']))."', category_id = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['category_id'])).", tags = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['tags']))."', address = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['address']))."', link = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['link']))."', price = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['price']))."', featured = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['featured'])).", approved = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['approved'])).", active = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['active'])).", image = '".stripslashes(mysqli_real_escape_string(db(),$post_vars['image_path']))."' WHERE id = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['listing_id'])));

            break;

        case 'delete_listing':
            $db = db();

            $db->query("DELETE FROM marketplace_listings WHERE id = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['listing_id'])));

            break;

        case 'add_photo':
            $db = db();

            $db->query("INSERT INTO marketplace_images (image, listing_id) VALUES('".stripslashes(mysqli_real_escape_string(db(),$post_vars['image_path']))."', ".stripslashes(mysqli_real_escape_string(db(),$post_vars['listing_id'])).")");

            break;

        case 'delete_photo':
            $db = db();

            $db->query("DELETE FROM marketplace_images WHERE id = ".stripslashes(mysqli_real_escape_string(db(),$post_vars['image_id'])));

            break;

        default:
            return false;
            break;
    }
}

function marketplace_assign_get_var($url, $var, $val){
    $scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
    $host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
    $path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
    $query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
    $fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
    $variables = array();
    if(!is_null($query)){
        parse_str($query, $variables);
    }
    $variables[$var] = $val;
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return $scheme.$s.$host.$path.$q.http_build_query($variables).$h.$fragment;
}

function marketplace_remove_get_var($url, $var){
    $scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
    $host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
    $path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
    $query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
    $fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
    $variables = array();
    if(!is_null($query)){
        parse_str($query, $variables);
    }
    if(isset($variables[$var])){
        unset($variables[$var]);
    }
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return $scheme.$s.$host.$path.$q.http_build_query($variables).$h.$fragment;
}

function marketplace_get_image_size($path, $size){
    return str_replace('%w', $size, $path);
}


function marketplace_get_num_listing_comments($listing_id){
    $db = db();
    return $db->query("SELECT COUNT(comment_id) FROM `comments` WHERE `type` = 'marketplace' and `type_id` = '".$listing_id."'")->fetch_row()[0];
}

function marketplace_view_listing($listing_id){
    $db = db();
    if(!isset($_SESSION['veiwedlistings']) || (isset($_SESSION['veiwedlistings']) && !in_array($listing_id, $_SESSION['veiwedlistings']))){
        $db->query("UPDATE marketplace_listings SET nov = nov + 1, last_viewed = '".date("Y-m-d H:i:s")."' WHERE id = ".mysqli_real_escape_string(db(),$listing_id));
        $_SESSION['veiwedlistings'][] = $listing_id;
    }
}

function marketplace_delete_listing($listing_id) {
    $db = db();
    $db->query("DELETE FROM marketplace_listings WHERE id = ".$listing_id);
    $obsolete_images =  db()->query("SELECT id, image FROM marketplace_images WHERE listing_id = ".$listing_id);
    while($obsolete_image = $obsolete_images->fetch_assoc()) {
        $db->query("DELETE FROM marketplace_images WHERE id = ".$obsolete_images['id']);
        delete_file(path($obsolete_images['image']));
    }
}

function marketplace_num_listings(){
    $db = db();
    $num_listings = $db->query("SELECT COUNT(id) FROM marketplace_listings");
    if($db->error){
        return 0;
    }
    else{
        return $num_listings->fetch_row()[0];
    }
}

function marketplace_num_pending_listings(){
    $db = db();
    $num_pending_listing = $db->query("SELECT COUNT(id) FROM marketplace_listings WHERE approved = 0");
    if($db->error){
        return 0;
    }
    else{
        return $num_pending_listing->fetch_row()[0];
    }
}

function marketplace_output_text($content) {
    $tContent = $content;
    $original = $content;
    $content = format_output_text($content);
    if (is_rtl($content)) {
        $content = "<span style='direction: rtl;text-align: right;display: block'>{$content}</span>";

    }
    //too much text solution
    $id = md5($tContent.time());
    $result = "<span id='{$id}' style='font-weight: normal !important'>";
    if (mb_strlen($tContent) > 300) {
        $result .= "<span class='text-full' style='display: none;font-weight: normal'>{$content}</span>";
        $tContent = format_output_text(str_limit($tContent, 300));
        if (is_rtl($tContent)) $tContent = "<span style='direction: rtl;text-align: right;display:block'>{$tContent}</span>";
        $result .= "<span style='font-weight: normal !important'>".$tContent."</span>";
        $result .= '<a href="" onclick=\'return read_more(this, "'.$id.'")\'>'.lang('read-more').'</a>';
    } else {
        $result .= $content;
    }

    $result .= "</span>";
    if (config('enable-bing-translator', false) and !empty($original) and !isEnglish($original)) {
        $trans = lang('see-translation');
        $result .= "<div id='{$id}-translation' class='non-translated'><input name='text' type='hidden' value='{$original}'/><button data-id='{$id}' onclick='return translateText(this)'>{$trans}</button></div>";
    }


    return $result;
}

function marketplace_slugger($str) {
    return trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $str)), '-');
}

function marketplace_unique_slugger($title, $type) {
    $db = db();
    $table =  $type == 'category' ? 'marketplace_categories' : 'marketplace_listings';
    $id = $db->query("SELECT id FROM ".$table." WHERE title = '".mysqli_real_escape_string(db(),$title)."'");
    $id = ($id->num_rows == 0) ? 0 : $id->fetch_row()[0];
    $slug = marketplace_slugger(lang($title));
    if($db->query("SELECT COUNT(id) FROM ".$table." WHERE slug = '".mysqli_real_escape_string(db(),$slug)."' AND id != ".mysqli_real_escape_string(db(),$id))->fetch_row()[0] == 0){
        return $slug;
    }
    else{
        $i = 0;
        while($db->query("SELECT COUNT(id) FROM ".$table." WHERE slug = '".mysqli_real_escape_string(db(),$slug."-".$i)."' AND id != ".mysqli_real_escape_string(db(),$id))->fetch_row()[0] > 0){
            $i++;
        }
        return $slug.'-'.$i;
    }
}

function marketplace_update_slugs($type) {
    $db = db();
    $table =  $type == 'category' ? 'marketplace_categories' : 'marketplace_listings';
    $titles = $db->query("SELECT id, slug, title FROM ".$table);
    while($row_titles = $titles->fetch_assoc()) {
        $db->query("UPDATE ".$table." SET slug = '".mysqli_real_escape_string(db(),marketplace_unique_slugger($row_titles['title'], $type))."' WHERE id = '".mysqli_real_escape_string(db(),$row_titles['id'])."'");
    }
}

function marketplace_get_slug_id($slug, $type){
    $db = db();
    $table =  $type == 'category' ? 'marketplace_categories' : 'marketplace_listings';
    return $db->query("SELECT id FROM ".$table." WHERE slug = '".mysqli_real_escape_string(db(),$slug)."'")->fetch_row()[0];
}

function marketplace_get_slug($id, $type){
    $db = db();
    $table =  $type == 'category' ? 'marketplace_categories' : 'marketplace_listings';
    return '/'.$db->query("SELECT slug FROM ".$table." WHERE id = ".$id)->fetch_row()[0];
}

function marketplace_get_marketplace_slug_link($url) {
    $scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
    $host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
    $path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
    $query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
    $fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
    $variables = array();
    if(!is_null($query)){
        parse_str($query, $variables);
    }
    $category = null;
    $type = null;
    if(isset($variables['c'])) {
        $category = '/category'.marketplace_get_slug(($variables['c']), 'category');
        unset($variables['c']);
    }
    if(isset($variables['t'])) {
        if($variables['t'] == 'm'){
            $type = '/my-listings';
        }
        elseif(is_numeric($variables['t'])){
            $type = '/'.$variables['t'];
        }
        else{
            $type = '';
        }
        unset($variables['t']);
    }
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return $scheme.$s.$host.rtrim($path, '/').$category.$type.$q.http_build_query($variables).$h.$fragment;
}

function marketplace_update_storage(){
    $db = db();
    $old_dir = 'storage/uploads/market_place';
    $new_dir = 'storage/uploads/marketplace';
    if(!is_dir($new_dir) && is_dir($old_dir)){
        rename($old_dir, $new_dir);
        $images = $db->query("SELECT id, image FROM marketplace_listings WHERE image LIKE '%uploads/market_place/listings%'");
        while($image = $images->fetch_assoc()){
            $db->query("UPDATE marketplace_listings SET image = '".mysqli_real_escape_string(db(),preg_replace('#uploads/market_place/listings#i', 'uploads/marketplace/listings', $image['image']))."' WHERE id = ".$image['id']);
        }
        $photos = $db->query("SELECT id, image FROM marketplace_images WHERE image LIKE '%uploads/market_place/listings%'");
        while($photo = $photos->fetch_assoc()){
            $db->query("UPDATE marketplace_images SET image = '".mysqli_real_escape_string(db(),preg_replace('#uploads/market_place/listings#i', 'uploads/marketplace/listings', $photo['image']))."' WHERE id = ".$photo['id']);
        }
    }
}