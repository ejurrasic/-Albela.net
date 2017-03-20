<?php
function marketplace_upgrade_database() {
    $db = db();
    if($db->query("SHOW TABLES LIKE 'market_place_categories'")->num_rows > 0){
        $db->query("RENAME TABLE `market_place_categories` TO `marketplace_categories`" );
    }

    if($db->query("SHOW TABLES LIKE 'market_place_images'")->num_rows > 0){
        $db->query("RENAME TABLE `market_place_images` TO `marketplace_images`" );
    }

    if($db->query("SHOW TABLES LIKE 'market_place_listings'")->num_rows > 0){
        $db->query("RENAME TABLE `market_place_listings` TO `marketplace_listings`" );
    }

    $db->query("CREATE TABLE IF NOT EXISTS `marketplace_categories` (
        `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
        `slug` varchar(64) NOT NULL,
        `title` varchar(64) NOT NULL,
        PRIMARY KEY (`id`)
    )
    ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    try{$db->query("ALTER TABLE `marketplace_categories` ADD `slug` VARCHAR(64) NOT NULL AFTER `id`");}catch(Exception $e){$error = $e;}

    if($db->query("SELECT COUNT(id) FROM marketplace_categories")->fetch_row()[0] == 0){
        $preloaded_categories = array('Electronics', 'Property Sales, Rentals', 'Services', 'Auto, Cars, Trucks', 'Home, Office, Garden', 'Jobs, Employment', 'Accommodation, Travel', 'Dating, Friends');
        foreach($preloaded_categories as $preloaded_category){
            foreach(get_all_languages() as $language){
                $post_vars['title'][$language['language_id']] = $preloaded_category;
            }
            $expected = array('title' => '');
            extract(array_merge($expected, $post_vars));
            $titleSlug = 'marketplace_category_'.md5(time().serialize($post_vars)).'_title';
            foreach($title as $langId => $t){
                add_language_phrase($titleSlug, $t, $langId, 'marketplace');
            }
            foreach($title as $langId => $t){
                (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'marketplace') : add_language_phrase($titleSlug, $t, $langId, 'marketplace');
            }
            $db->query("INSERT INTO marketplace_categories(slug, title) VALUES('".trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', lang($titleSlug))), '-')."', '".$titleSlug."')");
        }
    }



    $db->query("CREATE TABLE IF NOT EXISTS `marketplace_images` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `image` varchar(256) NOT NULL,
        `listing_id` bigint(20) NOT NULL,
        PRIMARY KEY (`id`)
    )
    ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    $db->query("CREATE TABLE IF NOT EXISTS `marketplace_listings` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `slug` varchar(64) NOT NULL,
        `title` varchar(64) NOT NULL,
        `description` text NOT NULL,
        `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        `category_id` tinyint(2) NOT NULL,
        `lister_id` bigint(20) NOT NULL,
        `tags` varchar(64) NOT NULL,
        `image` varchar(256) NOT NULL,
        `address` varchar(256) NOT NULL,
        `link` varchar(128) NOT NULL,
        `price` varchar(32) NOT NULL DEFAULT '0.00',
        `featured` tinyint(1) NOT NULL,
        `approved` tinyint(1) NOT NULL,
        `active` tinyint(1) NOT NULL DEFAULT '1',
        `nov` bigint(20) unsigned NOT NULL,
        `last_viewed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (`id`)
    )
    ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    try{$db->query("ALTER TABLE `marketplace_listings` ADD `slug` VARCHAR(64) NOT NULL AFTER `id`");}catch(Exception $e){$error = $e;}

    try{$db->query("ALTER TABLE `marketplace_listings` CHANGE `contact` `link` VARCHAR(128)");}catch(Exception $e){$error = $e;}

    marketplace_dump_site_pages();

}

function marketplace_dump_site_pages(){
    load_functions("marketplace::marketplace");
    register_site_page("marketplace-slug", array('title' => lang('marketplace::marketplace'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'marketplace-slug', 'content', 'middle');
        Widget::add(null, 'marketplace-slug', 'plugin::marketplace|menu', 'left');
        Widget::add(null, 'marketplace-slug', 'plugin::marketplace|categories', 'left');
        marketplace_update_slugs('category');
        marketplace_update_slugs('listing');
        marketplace_update_storage();
        Menu::saveMenu('main-menu', 'marketplace::marketplace', 'marketplace', 'manual', 1, 'ion-android-cart');
    });
    register_site_page("marketplace-listing-slug", array('title' => lang('marketplace::listing'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'marketplace-listing-slug', 'content', 'middle');
        Widget::add(null, 'marketplace-listing-slug', 'plugin::marketplace|menu', 'left');
        Widget::add(null, 'marketplace-listing-slug', 'plugin::marketplace|categories', 'left');
    });
    register_site_page("marketplace-create-listing", array('title' => lang('marketplace::create-listing'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'marketplace-create-listing', 'content', 'middle');
        Widget::add(null, 'marketplace-create-listing', 'plugin::marketplace|menu', 'left');
        Widget::add(null, 'marketplace-create-listing', 'plugin::marketplace|categories', 'left');
    });
    register_site_page("marketplace-edit-listing", array('title' => lang('marketplace::edit-listing'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'marketplace-edit-listing', 'content', 'middle');
        Widget::add(null, 'marketplace-edit-listing', 'plugin::marketplace|menu', 'left');
        Widget::add(null, 'marketplace-edit-listing', 'plugin::marketplace|categories', 'left');
    });
    register_site_page("marketplace-delete-listing", array('title' => lang('marketplace::delete-listing'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'marketplace-delete-listing', 'content', 'middle');
        Widget::add(null, 'marketplace-delete-listing', 'plugin::marketplace|menu', 'left');
        Widget::add(null, 'marketplace-delete-listing', 'plugin::marketplace|categories', 'left');
    });
    register_site_page("marketplace-add-photo", array('title' => lang('marketplace::add-listing-picture'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'marketplace-add-photo', 'content', 'middle');
        Widget::add(null, 'marketplace-add-photo', 'plugin::marketplace|menu', 'left');
        Widget::add(null, 'marketplace-add-photo', 'plugin::marketplace|categories', 'left');
    });
    register_site_page("marketplace-delete-photo", array('title' => lang('marketplace::delete-listing-picture'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'marketplace-delete-photo', 'content', 'middle');
        Widget::add(null, 'marketplace-delete-photo', 'plugin::marketplace|menu', 'left');
        Widget::add(null, 'marketplace-delete-photo', 'plugin::marketplace|categories', 'left');
    });
}