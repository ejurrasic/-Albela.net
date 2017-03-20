<?php
/**
 * function to get themes from its directory
 *
 * @param string $type
 * @return array
 */
function get_all_themes($type = "frontend") {
    $themesPath = config("themes_dir").$type.'/';
    $handle = opendir($themesPath);
    $themes = array();
    while($folder = readdir($handle)) {
        if (substr($folder, 0, 1) != '.' and !preg_match("#\.#", $folder)) {
            $themes[$folder] = array();
            $theThemePath = $themesPath.$folder.'/';
            $themes[$folder]['info'] = include $theThemePath.'info.php';
            $themes[$folder]['preview'] = url(config('themes_folder').'/'.$type.'/'.$folder.'/preview.png');
        }
    }

    return $themes;
}
/**
 * Function to activate theme
 * @param string $type
 * @param string $theme
 * @return boolean
 */
function activate_theme($type, $theme) {
    if (empty($theme) or theme_not_exists($theme, $type)) return false;
    db()->query("UPDATE `themes` SET `theme`='{$theme}' WHERE `type`='{$type}'");
    fire_hook("theme.activate", null, array($type, $theme));
    /**
     * Reload the themes list cache
     */
    forget_cache("themes");
    $query = db()->query("SELECT `type`,`theme` FROM `themes`");
    if ($query) {
        $themes = array();
        $result = fetch_all($query);
        foreach($result as $row) {
            $themes[$row['type']] = $row['theme'];
        }

        set_cacheForever("themes", $themes);
        return true;
    }
    return false;
}

function theme_not_exists($theme, $type) {
    $themesPath = config("themes_dir").$type.'/'.$theme.'/';
    if (is_dir($themesPath)) return false;
    return true;
}

function save_theme_settings($val) {
    $logo = input_file('logo');
    if ($logo) {
        $uploader = new Uploader($logo, 'file', true);
        if ($uploader->passed()) {
            //since upload pass validation lets delete previous
            $previousLogo = config('site-logo');
            if ($previousLogo) {
                delete_file(path($previousLogo));
            }
            $uploader->setPath("logo/")->uploadFile();
            $val['site-logo'] = $uploader->result();
        } else {
            return false;
        }
    }
    $favicon = input_file('favicon');
    if ($favicon) {
        $uploader = new Uploader($favicon, 'file', true);
        if ($uploader->passed()) {
            //since upload pass validation lets delete previous
            $previousLogo = config('site-favicon');
            if ($previousLogo) {
                delete_file(path($previousLogo));
            }
            $uploader->setPath("logo/")->uploadFile();

            $val['site-favicon'] = $uploader->result();
        } else {
            return false;
        }
    }
    save_admin_settings($val);
    return true;
}

function get_current_theme_settings() {
    $theme = get_active_theme('fontend');
    $type = 'frontend';
    $themesPath = config("themes_dir").$type.'/'.$theme.'/';
    $optionFile = $themesPath.'/options.php';
    if (theme_not_exists($theme, $type) or !file_exists($optionFile)) {
        return array();
    }
    $settings = include_once $optionFile;
    return $settings['settings'];
}
 