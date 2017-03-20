<?php
function forum_upgrade_database() {
    $db = db();

    if($db->query("SHOW TABLES LIKE 'forum_categories'")->num_rows == 0){
        $db->query("CREATE TABLE IF NOT EXISTS `forum_categories` (
            `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(64) CHARACTER SET utf8 NOT NULL,
            PRIMARY KEY (`id`),
            KEY `title` (`title`),
            KEY `title_2` (`title`),
            KEY `title_3` (`title`)
            )
        ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

        $preloaded_categories = array('General', 'Entertainment', 'Science and Technology');
        foreach($preloaded_categories as $preloaded_category){
            foreach(get_all_languages() as $language){
                $post_vars['title'][$language['language_id']] = $preloaded_category;
            }
            $expected = array('title' => '');
            extract(array_merge($expected, $post_vars));
            $titleSlug = 'forum_category_'.md5(time().serialize($post_vars)).'_title';
            foreach($title as $langId => $t){
                add_language_phrase($titleSlug, $t, $langId, 'forum');
            }
            $db->query("INSERT INTO `forum_categories` (`title`) VALUES('".$titleSlug."')");
            foreach($title as $langId => $t){
                (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'forum') : add_language_phrase($titleSlug, $t, $langId, 'forum');
            }
        }
    }
    $db->query("CREATE TABLE IF NOT EXISTS `forum_followed_threads` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`thread_id` bigint(20) unsigned NOT NULL,
		`follower_id` int(10) unsigned NOT NULL,
		`last_check_nor` smallint(5) unsigned NOT NULL DEFAULT '0',
		`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		KEY `thread_id` (`thread_id`),
		KEY `follower_id` (`follower_id`),
		KEY `last_check_nor` (`last_check_nor`)
	)
	ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    $db->query("CREATE TABLE IF NOT EXISTS `forum_likes` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`reply_id` bigint(20) unsigned NOT NULL,
		`liker_id` bigint(20) unsigned NOT NULL,
		PRIMARY KEY (`id`),
		KEY `liker_id` (`liker_id`),
		KEY `reply_id` (`reply_id`)
	)
	ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    $db->query("CREATE TABLE IF NOT EXISTS `forum_replies` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`post` text NOT NULL,
		`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`last_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
		`thread_id` bigint(20) unsigned NOT NULL,
		`replied_id` bigint(20) NOT NULL,
		`replier_id` int(10) unsigned NOT NULL,
		`hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		KEY `hidden` (`hidden`),
		KEY `replier_id` (`replier_id`),
		KEY `replied_id` (`replied_id`),
		KEY `thread_id` (`thread_id`)
	)
	ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    $db->query("CREATE TABLE IF NOT EXISTS `forum_tags` (
		`id` tinyint(2) NOT NULL AUTO_INCREMENT,
		`title` varchar(64) NOT NULL,
		`color` varchar(16) NOT NULL,
		PRIMARY KEY (`id`),
		KEY `title` (`title`)
	)
	ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    $db->query("CREATE TABLE IF NOT EXISTS `forum_threads` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`subject` varchar(128) NOT NULL,
		`tags` varchar(128) NOT NULL,
		`category_id` tinyint(2) unsigned NOT NULL,
		`op_id` int(10) unsigned NOT NULL,
		`rp_id` int(10) unsigned NOT NULL,
		`nov` int(10) unsigned NOT NULL,
		`nor` smallint(5) unsigned NOT NULL,
		`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`last_viewed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
		`last_replied` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
		`last_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
		`pinned` tinyint(1) NOT NULL,
		`hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
		`active` tinyint(1) unsigned NOT NULL DEFAULT '1',
		`closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		KEY `closed` (`closed`),
		KEY `active` (`active`),
		KEY `pinned` (`pinned`),
		KEY `nor` (`nor`),
		KEY `nov` (`nov`),
		KEY `rp_id` (`rp_id`),
		KEY `op_id` (`op_id`),
		KEY `category_id` (`category_id`)
	)
	ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    $db->query("CREATE TABLE IF NOT EXISTS `forum_viewing_threads` (
		`id` bigint(20) NOT NULL AUTO_INCREMENT,
		`viewer_id` bigint(20) unsigned NOT NULL,
		`ip` varchar(15) NOT NULL,
		`thread_id` bigint(20) unsigned NOT NULL,
		`last_viewed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`bot` tinyint(1) unsigned NOT NULL,
		PRIMARY KEY (`id`),
		KEY `bot` (`bot`),
		KEY `last_viewed` (`last_viewed`),
		KEY `thread_id` (`thread_id`),
		KEY `viewer_id` (`viewer_id`)
	)
	ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    try{$db->query("ALTER TABLE `forum_threads` ADD `slug` VARCHAR(64) NOT NULL;");}catch(Exception $e){$error = $e;}

    try{$db->query("ALTER TABLE  `forum_threads` ADD  `featured` TINYINT(1) NOT NULL DEFAULT  '0'");}catch(Exception $e){$error = $e;}

    try{$db->query("ALTER TABLE `forum_viewing_threads` MODIFY `viewer_id` bigint(20) unsigned NULL DEFAULT NULL; ");}catch(Exception $e){$error = $e;}

    forum_dump_site_pages();
}

function forum_dump_site_pages(){
    register_site_page("forum-slug", array('title' => lang('forum::forum'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'forum-slug', 'content', 'middle');
        Widget::add(null, 'forum-slug', 'plugin::forum|menu', 'left');
        Widget::add(null, 'forum-slug', 'plugin::forum|categories', 'left');
        Widget::add(null, 'forum-slug', 'plugin::forum|tags', 'left');
        Menu::saveMenu('main-menu', 'forum::forum', 'forum', 'manual', 1, 'ion-chatboxes');
    });
    register_site_page("forum-thread-slug", array('title' => lang('forum::thread'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'forum-thread-slug', 'content', 'middle');
        Widget::add(null, 'forum-thread-slug', 'plugin::forum|menu', 'left');
        Widget::add(null, 'forum-thread-slug', 'plugin::forum|followed', 'left');
        Widget::add(null, 'forum-thread-slug', 'plugin::forum|featured', 'left');
    });
    register_site_page("forum-create-thread", array('title' => lang('forum::create-thread'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'forum-create-thread', 'content', 'middle');
        Widget::add(null, 'forum-create-thread', 'plugin::forum|menu', 'left');
    });
    register_site_page("forum-edit-thread", array('title' => lang('forum::edit-thread'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'forum-edit-thread', 'content', 'middle');
        Widget::add(null, 'forum-edit-thread', 'plugin::forum|menu', 'left');
    });
    register_site_page("forum-reply-thread", array('title' => lang('forum::reply-thread'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'forum-reply-thread', 'content', 'middle');
    });
    register_site_page("forum-edit-post", array('title' => lang('forum::edit-post'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'forum-edit-post', 'content', 'middle');
    });
}