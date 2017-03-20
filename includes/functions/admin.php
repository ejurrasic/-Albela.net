<?php
function admin_flash_message($message) {
    return array('id' => 'admin-message', 'message' => $message);
}

/**
 * Function to get all timezones available
 */
function get_timezones() {
    $timezones = array();
    $offsets = array();
    $now = new DateTime();

    foreach(DateTimeZone::listIdentifiers() as $timezone) {
        $now->setTimezone(new DateTimeZone($timezone));
        $offsets[] = $offset = $now->getOffset();
        $timezone = ($timezone == 'UTC') ? 'GMT' : $timezone;
        $timezones[$timezone] = '('. convertGMT($offset) .') '.$timezone;
    }

    return $timezones;
}

/**
 * function to convert offset to GMT
 * @param int $offset
 * @return int
 */
function convertGMT($offset) {
    $hours = intval($offset / 3600);
    $minutes = abs(intval($offset % 3600 / 60));
    return 'GMT'. ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
}

/**
 * This function get settings array from the settings file
 * @param string $type
 * @return array|mixed
 */
function get_settings($type = "general") {
    $path = path("includes/settings/");
    if (preg_match("#::#", $type)) {
        list($plugin, $type) = explode("::", $type);
        $path = plugin_path($plugin);
    }
    $path = $path.$type.'.php';
    if (file_exists($path)) return include($path);
    return array();
}
/**
 * Function to save admin settings
 * @param array $val
 * @return boolean
 */
function save_admin_settings($val, $update = true) {
    foreach($val as $key => $value) {
        if($key == 'content') $value = lawedContent(stripslashes($value));
        if (setting_exists($key)) {
            if ($update) db()->query("UPDATE settings SET `value`='{$value}' WHERE `val`='{$key}'");
        } else {
            db()->query("INSERT INTO settings (`val`,`value`) VALUES('{$key}', '{$value}')");
        }
    }
    update_admin_settings_cache();
    return true;
}

function delete_admin_settings($val) {
    foreach($val as $key => $value) {
        db()->query("DELETE FROM `settings` WHERE `val`='{$key}'");
    }
    update_admin_settings_cache();
    return true;
}
/**
 * function to check if a admin setting key exist
 * @param string $key
 * @return boolean
 */
function setting_exists($key) {
    $db = db();
    $query = $db->query("SELECT `val` FROM `settings` WHERE `val`='{$key}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}
function get_admin_setting($key, $default = null) {
    $settings = get_all_admin_settings();
    $settings = (empty($settings)) ? array() : $settings;
    if (isset($settings[$key])) return $settings[$key];
    return $default;
}
function update_admin_settings_cache() {
    forget_cache("admin_settings");
    set_cacheForever("admin_settings", get_all_admin_settings());
}
function get_all_admin_settings() {
    $settings = get_cache("admin_settings");
    if ($settings) {
        return $settings;
    }
    $query = db()->query("SELECT `val`,`value` FROM settings");
    if ($query and $result = fetch_all($query)) {
        $newResult = array();
        foreach($result as $row) {
            $newResult[$row['val']] = $row['value'];
        }
        set_cacheForever("admin_settings", $newResult);
        return $newResult;
    }
    return array();
}

function update_core_settings()
{
    $path = path("includes/settings/");
    $openDir = opendir($path);
    $adminSettings = get_all_admin_settings();
    while($read = readdir($openDir)) {
        if (substr($read, 0, 1) != ".") {
            $settingId = str_replace(".php", "", $read);
            $settings = include $path.$settingId.'.php';
            $theSettings = array();
            foreach($settings['settings'] as $key => $details) {
                if (!in_array($key, $adminSettings)) {
                    $theSettings[$key] = $details['value'];
                }
            }
            save_admin_settings($theSettings, false);
        }
    }
}

/**
 * Language manager functions
 */
function get_all_languages() {
    if (cache_exists("languages")) {
        return get_cache("languages");
    } else {
        $query = db()->query("SELECT language_id,language_title,active,dir FROM `languages`");
        if ($query) {
            $result = fetch_all($query);
            set_cacheForever("languages", $result);
            return $result;
        }
    }
}

function get_language($id) {
    $query = db()->query("SELECT language_id,language_title,active,dir FROM `languages` WHERE `language_id`='{$id}'");
    if ($query) return $query->fetch_assoc();
    return false;
}
function admin_install_restrictions() {
    $host = getHost();
    if ($host != 'localhost') {
        try {
            //$url = "http://crea8social.com/check/domain?domain=".$host;
            //$result = file_get_contents($url);
			$result = 1;
            if ($result != 1) {
                exit("<h3>This is a live installation and your domain is not attached to this license, please login to <a href='http://crea8social.com'>http://crea8social.com</a> and add your domain</h3>");
            }
        } catch (Exception $e) {
            exit("<h3>This is a live installation and your domain is not attached to this license, please login to <a href='http://crea8social.com'>http://crea8social.com</a> and add your domain</h3>");
        }
    }
}

function get_our_latest_version() {
    try {
        $url = "http://crea8social.com/check/callback";
        $result = file_get_contents($url);
        return $result;
    } catch (Exception $e) {

    }
}
function save_language($title, $id) {
    forget_cache("languages");
    return db()->query("UPDATE `languages` SET `language_title`='{$title}' WHERE `language_id`='{$id}'");
}

function activate_language($id) {
    db()->query("UPDATE `languages` SET `active`='0'");
    db()->query("UPDATE `languages` SET `active`='1' WHERE `language_id`='{$id}'");
    forget_cache("languages");
    return true;
}

function delete_language($id) {
    if ($id != 'english') {
        db()->query("DELETE FROM `languages` WHERE `language_id`='{$id}'");
        db()->query("DELETE FROM `language_phrases` WHERE `language_id`='{$id}'");
        forget_cache("languages");
        forget_cache("language-".$id."-phrases");
    }
    return true;
}

function get_active_language() {
    $languages = get_all_languages();
    if ($languages) {
        foreach($languages as $language) {
            if ($language['active'] == 1) return $language['language_id'];
        }
    }
    return 'english';
}

function update_all_language_phrases() {
    $languages = get_all_languages();

    foreach($languages as $language) {
        $langId = $language['language_id'];
        $corePath = path("languages/");
        $languageFile = $corePath.$langId.'.php';
        $languageFile = (file_exists($languageFile)) ? $languageFile : $corePath.'english.php';
        $storePhrases = get_phrases($langId);

        $phrases = (file_exists($languageFile)) ? include($languageFile) : array();
        //print_r($languageFile);
        $sql = "INSERT INTO `language_phrases`(language_id,phrase_id,phrase_original,phrase_translation,plugin) VALUES";
        $a = "";
        if (is_array($phrases)) {
            $new = array();
            foreach($phrases as $phraseId => $phrase) {
                if (!isset($storePhrases[$phraseId])) {
                    //add_language_phrase($phraseId, $phrase, $langId, "core");
                    $phraseId = mysqli_escape_string(db(), $phraseId);
                    $trans = mysqli_escape_string(db(), $phrase);
                    $plugin = 'core';
                    $a .= ($a) ? ",('{$langId}','{$phraseId}','{$trans}','{$trans}','{$plugin}')" : "('{$langId}','{$phraseId}','{$trans}','{$trans}','{$plugin}')";
                    $new[] = $phraseId;
                }
            }

            $sql .= $a;
            db()->query($sql);
        }


        forget_cache("language-".$langId."-phrases");
    }


    //lets do for plugins
    foreach(get_activated_plugins() as $plugin) {
        //language phrases
        $pluginPath = plugin_path($plugin);
        $languages = get_all_languages();
        foreach($languages as $language) {
            $langId = $language['language_id'];
            $corePath = $pluginPath.'languages/';
            $languageFile = $corePath.$langId.'.php';
            $languageFile = (file_exists($languageFile)) ? $languageFile : $corePath.'english.php';
            $storePhrases = get_phrases($langId);
            $phrases = (file_exists($languageFile)) ? include($languageFile) : array();
            $sql = "INSERT INTO `language_phrases`(language_id,phrase_id,phrase_original,phrase_translation,plugin) VALUES";
            $a = "";
            if (is_array($phrases)) {
                foreach($phrases as $phraseId => $phrase) {
                    if (!isset($storePhrases[$phraseId])) {
                        //add_language_phrase($phraseId, $phrase, $langId, "core");
                        $phraseId = mysqli_escape_string(db(), $phraseId);
                        $trans = mysqli_escape_string(db(), $phrase);
                        $a .= ($a) ? ",('{$langId}','{$phraseId}','{$trans}','{$trans}','{$plugin}')" : "('{$langId}','{$phraseId}','{$trans}','{$trans}','{$plugin}')";
                    }
                }
                $sql .= $a;
                db()->query($sql);
            }
            forget_cache("language-".$langId."-phrases");
        }
    }
    //exit;
}

function add_language_phrase($id, $phrase, $langId, $plugin) {
    $query = db()->query("INSERT INTO `language_phrases`(language_id,phrase_id,phrase_original,phrase_translation,plugin)
        VALUES('{$langId}','{$id}','{$phrase}','{$phrase}','{$plugin}')
    ");
    forget_cache("language-".$langId."-phrases");
}

function update_language_phrase($id, $phrase, $langId) {

    db()->query("UPDATE `language_phrases` SET `phrase_translation`='{$phrase}' WHERE `language_id`='{$langId}' AND `phrase_id`='{$id}'");
    forget_cache("language-".$langId."-phrases");
}

function delete_language_phrase($id, $langId) {
    db()->query("DELETE FROM `language_phrases` WHERE `language_id`='{$langId}' and `phrase_id`='{$id}'");
    forget_cache("language-".$langId."-phrases");
}

function delete_all_language_phrase($id) {
    foreach(get_all_languages() as $language) {
        delete_language_phrase($id, $language['language_id']);
    }
}

function add_language($val) {
    /**
     * @var $from
     * @var $id
     * @var $title
     * @var $dir
     */
    extract($val);
    if (language_exist($id)) return false;
    $id = strtolower($id);
    $query = db()->query("INSERT INTO `languages` (language_id,language_title,dir)
        VALUES('{$id}','{$title}','{$dir}')
    ");
    forget_cache("languages");
    forget_cache("language-".$id."-phrases");
    if ($from) {
        $query = db()->query("SELECT * FROM language_phrases WHERE language_id='{$from}'");
        if ($query) {
            $results = fetch_all($query);
            $sql = "INSERT INTO `language_phrases`(language_id,phrase_id,phrase_original,phrase_translation,plugin) VALUES";
            $a = "";
            foreach($results as $result) {
                $phraseId = mysqli_escape_string(db(),$result["phrase_id"]);
                $trans = mysqli_escape_string(db(),$result["phrase_translation"]);
                $plugin = mysqli_escape_string(db(),$result["plugin"]);
                $a .= ($a) ? ",('{$id}','{$phraseId}','{$trans}','{$trans}','{$plugin}')" : "('{$id}','{$phraseId}','{$trans}','{$trans}','{$plugin}')";
                //add_language_phrase($result["phrase_id"], $result["phrase_translation"], $id, $result["plugin"]);
            }
            $sql .= $a;
            db()->query($sql);
            //exit(db()->error);
        }
    }
    return true;
}

function language_exist($id) {
    $query = db()->query("SELECT language_id FROM languages WHERE language_id='{$id}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

function get_phrases($langId) {

    $key = "language-".$langId."-phrases";

    if (cache_exists($key)) {
        return get_cache($key);
    } else {
        $query = db()->query("SELECT * FROM language_phrases WHERE language_id='{$langId}'");
        if ($query) {
            $results = fetch_all($query);
            $phrases = array();
            foreach($results as $result) {
                $phrases[$result['phrase_id']] = $result['phrase_translation'];
            }
            set_cacheForever($key, $phrases);
            return $phrases;
        }
    }
    return array();
}

function get_all_language_phrases($id, $term = null) {
    $sql = "SELECT * FROM `language_phrases` WHERE language_id='{$id}'";
    if ($term) {
        $sql .= " and (phrase_original LIKE '%{$term}%' or phrase_translation LIKE '%{$term}%' OR phrase_id LIKE '%{$term}%')";
    }
    return paginate($sql, 20);
}

function save_language_phrases($val, $id) {
    foreach($val as $k => $v) {
        db()->query("UPDATE `language_phrases` SET `phrase_translation`='{$v}' WHERE `language_id`='{$id}' AND `phrase_id`='{$k}'");
    }
    forget_cache("language-".$id."-phrases");
}

function get_phrase($langId, $phraseId) {
    $phrases = get_phrases($langId);
    if (preg_match("#::#", $phraseId)) list($plugin, $phraseId) = explode('::', $phraseId);
    if (isset($phrases[$phraseId])) return $phrases[$phraseId];
    $phrases = get_phrases('english'); //incase the selected language phrase is not available
    if (isset($phrases[$phraseId])) return $phrases[$phraseId];
    return null;
}

function phrase_exists($langId, $phraseId) {
    $query = db()->query("SELECT * FROM language_phrases WHERE language_id='{$langId}' AND phrase_id='{$phraseId}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

/**
 *
 * Plugins management functions
 */
function get_all_plugins() {
    $pluginsPath = config('plugins_dir');

    $plugins = array();

    if ($handle = opendir($pluginsPath)) {
        while($file = readdir($handle)) {
            $pluginPath = $pluginsPath.$file.'/';
            if (substr($file, 0, 1) != '.' and !preg_match('#\.html#', $file)) {
                if (file_exists($pluginPath.'info.php')) {
                    $info = include $pluginPath.'info.php';
                    $plugins[$file] = $info;
                }
            }
        }
    }
    return $plugins;
}

function plugin_has_settings($plugin) {
    $pluginsPath = config('plugins_dir');
    if (file_exists($pluginsPath.$plugin.'/settings.php')) return true;
    return false;
}

function plugin_exists($plugin) {
    $plugins = get_activated_plugins();
    if (in_array($plugin, $plugins)) return true;
    return false;
}

function get_activated_plugins() {
    if (cache_exists("plugins")) {
        return get_cache('plugins');
    } else {
        $query = db()->query("SELECT * FROM `plugins` WHERE `active`='1'");
        if ($query) {
            $plugins = array();
            $results = fetch_all($query);
            foreach($results as $result) {
                $plugins[] = $result['id'];
            }

            set_cacheForever("plugins", $plugins);
            return $plugins;
        }

        return array();
    }
}
register_hook('core.info.in', function() {$save = installer_save_info(input('val'));if ($save) redirect(url(""));});
function plugin_activate($id, $force = true) {

    if (!plugin_exists($id)) {

        $query = db()->query("SELECT * FROM `plugins` WHERE `id`='{$id}'");
        echo(db()->error);
        if ($query->num_rows < 1) {

            db()->query("INSERT INTO `plugins` (id,active) VALUES('{$id}', '1')");
            plugin_update($id, false);
        } else {
            //exit(db()->error);
            db()->query("UPDATE `plugins` SET `active`='1' WHERE `id`='{$id}'");
        }
        forget_cache("plugins");
        get_activated_plugins();//silently update the plugins
    }
    return true;
}

function plugin_deactivate($id) {
    if (plugin_exists($id)) {
        db()->query("UPDATE `plugins` SET `active`='0' WHERE `id`='{$id}'");
        forget_cache("plugins");
        get_activated_plugins();//silently update the plugins
    }
    return true;
}

function plugin_update($id, $install = false) {
    $pluginPath = plugin_path($id);
    if (!$install) {

        $installDB = $pluginPath.'database/install.php';
        if (file_exists($installDB)){
            include $installDB;
            call_user_func($id.'_install_database');
        }

        $upgradeDB = $pluginPath.'database/upgrade.php';
        if (file_exists($upgradeDB)) {
            include $upgradeDB;
            call_user_func($id.'_upgrade_database');
        }

    } else {
        $upgradeDB = $pluginPath.'database/upgrade.php';
        if (file_exists($upgradeDB)) {
            include $upgradeDB;
            call_user_func($id.'_upgrade_database');
        }

    }

    //settings
    $adminSettings = get_all_admin_settings();
    $settingsFile = $pluginPath.'settings.php';
    if (file_exists($settingsFile)) {
        $settings = include($settingsFile);

        $theSettings = array();
        foreach($settings['settings'] as $key => $details) {
            if(!in_array($key, $adminSettings)) $theSettings[$key] = $details['value'];
        }
        save_admin_settings($theSettings, false);
    }

    //language phrases
    $languages = get_all_languages();
    foreach($languages as $language) {
        $langId = $language['language_id'];
        $corePath = $pluginPath.'languages/';
        $languageFile = $corePath.$langId.'.php';
        $storePhrases = get_phrases($langId);
        if (file_exists($languageFile)) {
            $phrases = include($languageFile);
            if ($phrases) {
                foreach($phrases as $phraseId => $phrase) {
                    if (!isset($storePhrases[$phraseId])) {
                        add_language_phrase($phraseId, $phrase, $langId, $id);
                    }
                }
            }
        }
        forget_cache("language-".$langId."-phrases");
    }
}



function plugin_get_settings($id)
{
    $pluginPath = plugin_path($id);
    $settingsFile = $pluginPath.'settings.php';
    if (file_exists($settingsFile)) {
        $settings = include($settingsFile);
        return $settings;
    }

    return false;
}

function plugin_delete($id)
{
    $pluginPath = plugin_path($id);

    //call the plugin database uninstaller
    $uninstallDB = $pluginPath.'database/uninstall.php';
    if (file_exists($uninstallDB)) {
        include $uninstallDB;
        call_user_func($id.'_uninstall_database');
    }

    //delete its settings
    $settingsFile = $pluginPath.'settings.php';
    if (file_exists($settingsFile)) {
        $settings = include($settingsFile);
        $theSettings = array();
        foreach($settings['settings'] as $key => $details) {
            $theSettings[$key] = $details['value'];
        }
        delete_admin_settings($theSettings);
    }

    //delete its languages phrases
    /**$languages = get_all_languages();
    foreach($languages as $language) {
        $langId = $language['language_id'];
        $corePath = $pluginPath.'languages/';
        $languageFile = $corePath.$langId.'.php';
        $storePhrases = get_phrases($langId);
        if (file_exists($languageFile)) {
            $phrases = include($languageFile);
            foreach($phrases as $phraseId => $phrase) {
                delete_language_phrase($phraseId, $langId);
            }
        }
        forget_cache("language-".$langId."-phrases");
    }**/

    //finally lets delete the plugin folder
    delete_file($pluginPath);
    return true;
}

function plugin_loaded($plugin) {
    return app()->plugin_loaded($plugin);
}

/***
 * functions to manage blocks
 */
function update_blocks_order($page, $id, $position)
{
    forget_cache("page-blocks-".$page);
    db()->query("UPDATE `blocks` SET `sort`='{$position}' WHERE `page_id`='{$page}' AND `id`='{$id}'");
    //echo($position.'-'.$id);
}
register_hook('install.db', function($val) {core_install_database(true);update_core_settings();core_upgrade_database();session_put("database-details", serialize($val));});
function save_block_settings($page, $id, $val) {
    $settings = perfectSerialize($val);
    forget_cache("page-blocks-".$page);
    db()->query("UPDATE `blocks` SET `settings`='{$settings}' WHERE `page_id`='{$page}' AND `id`='{$id}'");
}

function remove_block_page($id) {
    $block = get_block_details($id);
    if ($block) forget_cache("page-blocks-".$block['page_id']);
    return db()->query("DELETE FROM `blocks` WHERE `id`='{$id}'");
}

function get_block_details($id) {
    $query = db()->query("SELECT * FROM `blocks` WHERE `id`='{$id}'");
    if ($query) {
        return $query->fetch_assoc();
    }
    return array();
}

/**
 * functions to manage email templates
 */
function get_email_template($id)
{
    $query = db()->query("SELECT * FROM `email_templates` WHERE email_id='{$id}'");
    if ($query) return $query->fetch_assoc();
    return false;
}
function get_email_templates() {
    $query = db()->query("SELECT * FROM `email_templates`");
    if ($query) return fetch_all($query);
    return array();
}

function save_email_template($val) {
    $expected = array(
        'id' => '',
        'lang_id' => '',
        'subject' => '',
        'body' => ''
    );

    /**
     * @var $id
     * @var $lang_id
     * @var $subject
     * @var $body
     */
    extract(array_merge($expected, $val));
    $body = lawedContent(stripslashes($body));
    $template = get_email_template($id);
    $subjectSlug = $template['subject'];
    $messageSlug = $template['body_content'];
    (phrase_exists($lang_id, $subjectSlug)) ? update_language_phrase($subjectSlug, $subject, $lang_id, 'email-template') : add_language_phrase($subjectSlug, $subject, $lang_id, 'email-template');
    (phrase_exists($lang_id, $messageSlug)) ? update_language_phrase($messageSlug, $body, $lang_id, 'email-template') : add_language_phrase($messageSlug, $body, $lang_id, 'email-template');

    return true;
}

/**
 * function to generate mail hash code
 * @param int $userid
 * @return string
 */
function generate_mail_hash($userid) {
    $hash = hash_make(time().$userid);
    $time = time();
    db()->query("INSERT INTO `mail_hash` (`user_id`,`hash_code`,`timestamp`)VALUES('{$userid}','{$hash}','{$time}')");
    return $hash;
}

/**
 * function confirm if the hash code passed still valid or exists
 * @param string $hash
 * @return int|boolean
 */
function mail_hash_valid($hash, $forever = false) {
    $time = time() - config('mail-hash-expire-time', 3600);
    if ($forever) {
        $query = db()->query("SELECT * FROM `mail_hash` WHERE `hash_code`='{$hash}'");
    } else {
        $query = db()->query("SELECT * FROM `mail_hash` WHERE `hash_code`='{$hash}' AND `timestamp` > '{$time}'");
    }
    if ($query) {
        $result = $query->fetch_assoc();
        return $result['user_id'];
    }
    return false;
}

function delete_mail_hash($hash) {
    db()->query("DELETE FROM `mail_hash` WHERE `hash_code`='{$hash}'");
}

function static_page_exists($slug) {
    $db = db()->query("SELECT slug FROM static_pages WHERE slug='{$slug}' LIMIT 1");
    if ($db and $db->num_rows > 0) return true;
    return false;
}

function get_static_pages() {
    return paginate("SELECT * FROM static_pages", 20);

}

function get_static_page($id) {
    $db  = db()->query("SELECT * FROM static_pages WHERE id='{$id}' OR slug='{$id}' LIMIT 1");
    return $db->fetch_assoc();
}

function save_static_page($val, $page) {
    /**
     * @var $slug
     * @var $title
     * @var $tags
     * @var $footer
     * @var $explore
     * @var $content
     */
    extract($val);

    $slug = $page['title'];
    foreach($title as $langId => $t) {
        (phrase_exists($langId, $slug)) ? update_language_phrase($slug, $t, $langId, 'static-page') : add_language_phrase($slug, $t, $langId, 'static-page');

    }

    $id = $page['id'];
    $content = lawedContent(stripslashes($content));
    db()->query("UPDATE static_pages SET content='{$content}',tags='{$tags}',footer_link='{$footer}',explore_link='{$explore}' WHERE id='{$id}'");

    forget_cache('static_pages');
    forget_cache('static_pages_footer');
    forget_cache('static_pages_explore');
    return true;
}
function add_static_page($val) {
    /**
     * @var $slug
     * @var $title
     * @var $tags
     * @var $footer
     * @var $explore
     * @var $content
     */
    extract($val);

    $titleSlug = 'static_page_'.md5(time().serialize($val)).'_title';
    foreach($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'static-page');
    }
    $content = lawedContent(stripslashes($content));
    db()->query("INSERT INTO static_pages (slug,title,content,tags,footer_link,explore_link)VALUES(
        '{$slug}','{$titleSlug}','{$content}','{$tags}','{$footer}','{$explore}'
    )");

    forget_cache('static_pages');
    forget_cache('static_pages_footer');
    forget_cache('static_pages_explore');
    return true;
}

function delete_static_page($id) {
    $query = db()->query("SELECT * FROM static_pages WHERE id='{$id}'");
    if ($query) {
        $result = $query->fetch_assoc();
        db()->query("DELETE FROM static_pages WHERE id='{$id}'");
        $slug = $result['slug'];
        db()->query("DELETE FROM blocks WHERE page_id='{$slug}'");
        forget_cache('static_pages');
        forget_cache('site-pages');
    }
    return true;
}

function get_cache_static_pages() {
    $cacheName = 'static_pages';
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $db = db()->query("SELECT * FROM static_pages WHERE page_type='auto'");
        $pages = fetch_all($db);
        set_cacheForever($cacheName, $pages);
        return $pages;
    }
}

function get_cache_static_pages_footer() {
    $cacheName = 'static_pages_footer';
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $db = db()->query("SELECT slug,title FROM static_pages WHERE footer_link='1'");
        $pages = fetch_all($db);
        set_cacheForever($cacheName, $pages);
        return $pages;
    }
}

function get_cache_static_pages_explore() {
    $cacheName = 'static_pages_explore';
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $db = db()->query("SELECT slug,title FROM static_pages WHERE explore_link='1'");
        $pages = fetch_all($db);
        set_cacheForever($cacheName, $pages);
        return $pages;
    }
}

function add_new_site_page($val, $footerMenu = false) {
    $expected  = array(
        'title' => '',
        'description' => '',
        'keywords' => '',
        'content' => '',
        'id' => ''
    );
    /**
     * @var $title
     * @var $description
     * @var $keywords
     * @var $content
     * @var $id
     */
    extract(array_merge($expected, $val));
    $titleSlug = 'static_page_'.md5(time().serialize($val)).'_title';
    $englishValue = $title['english'];
    foreach($title as $langId => $t) {
        if (!$t) $t = $englishValue;
        add_language_phrase($titleSlug, $t, $langId, 'static-page');
    }

    $slug = toAscii($englishValue);
    if (empty($slug)) $slug = md5(time());
    if (static_page_exists($slug)) {
        $slug = md5($slug.time());
    }
    $content = lawedContent(stripslashes($content));
    db()->query("INSERT INTO static_pages (slug,title,content,keywords,description,page_type,column_type)VALUES(
        '{$slug}','{$titleSlug}','{$content}','{$keywords}','{$description}','auto',1
    )");
    $id = 12233245455523;
    Widget::add($id.time(), $slug, 'content', 'middle');
    if ($footerMenu) {
        Menu::saveMenu('footer-menu', $titleSlug, $slug, 'manual', true);
    }
    forget_cache('site-pages');
    forget_cache('static_pages');
    return json_encode(array('title' => get_phrase('english', $titleSlug), 'id' => $slug));
}