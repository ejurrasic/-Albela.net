<?php
include "pagers.php";

/**
 * Extend validation
 */
validation_extend("predefined", lang("validation-predefined-words"), function($value, $field) {
    $predefined = get_setting('predefined-words', '');
    $predefinedArray = explode(',', $predefined);

    if (in_array(strtolower($value), $predefinedArray)) return false;

    return true;
});

validation_extend('username', lang('validation-username'), function($value, $field) {
    $badWords = config('ban_filters_usernames', '');
    if ($badWords) {
        $badWords = explode(',', $badWords);
        if (in_array($value, $badWords)) return false;
    }
    if (is_numeric($value)) return false;
    //$slug = toAscii($value, array);
    //if(!preg_match('/^[A-Za-z][A-Za-z0-9\-_]{2,}$/', $value)) return false;
    if (!preg_match('/^[\pL\pN_-]+$/u', $value)) return false;
    $result = true;
    $q = db()->query("SELECT COUNT(id) as count FROM users WHERE username='{$value}'");
    $fetch = $q->fetch_assoc();
    if ($fetch['count'] > 0) $result = false;
    if ($result) $result = fire_hook('username.check', $result, array($value));
    return $result;
});

validation_extend('usernameedit', lang('validation-username'), function($value, $field) {
    $badWords = config('ban_filters_usernames', '');
    if ($badWords) {
        $badWords = explode(',', $badWords);
        if (in_array($value, $badWords)) return false;
    }
    $result = true;
    $userid = get_userid();
    $q = db()->query("SELECT COUNT(id) as count FROM users WHERE username='{$value}' and id !='{$userid}'");
    $fetch = $q->fetch_assoc();
    if ($fetch['count'] > 0) $result = false;
    if ($result) $result = fire_hook('username.check', $result, array($value));
    return $result;
});

/**
 * Register hook
 */
register_hook("system.shutdown", function() {
   db()->close();
});

/**
 * Register hook for before render js files
 */
register_hook("before-render-js", function($html) {
    $baseUrl = url();
    $loadingImage = img("images/loading.gif");
    $isLoggedIn = (is_loggedIn()) ? 1 : 0;
    $requestToken = CSRFProtection::getToken();
    $html .= "<script>
        var baseUrl = '{$baseUrl}';
        var indicator = '<img src=\'".$loadingImage."\'/>';
        var loggedIn = {$isLoggedIn};
        var requestToken = '{$requestToken}';

        //time_ago translation
            var trans_ago = \"".lang('ago')."\";
            var trans_from_now = \"".lang('from-now')."\";
            var trans_any_moment = \"".lang('any-moment')."\";
            var trans_less_than_minute = \"".lang('less-than-minute')."\";
            var trans_about_minute = \"".lang('about-minute')."\";
            var trans_minutes = \"".strtolower(lang('minutes'))."\";
            var trans_about_hour = \"".lang('about-hour')."\";
            var trans_hours = \"".strtolower(lang('hours'))."\";
            var trans_about = \"".strtolower(lang('about-i'))."\";
            var trans_a_day = \"".lang('a-day')."\";
            var trans_days = \"".lang('days')."\";
            var trans_about_month = \"".lang('about-month')."\";
            var trans_months = \"".lang('months')."\";
            var trans_about_year = \"".lang('about-year')."\";
            var trans_years = \"".lang('years')."\";
    </script>\n";

    if (config('pusher-driver', 'ajax') == 'ajax') {
        $interval = config('ajax-polling-interval', 5000);
        $html .= "<script>
        var ajaxInterval = {$interval};
        </script>";
    }
    return $html;
});


/**
 * Register auth filter
 */
register_filter("auth", function() {
    if (!is_loggedIn()) {
        if (is_ajax()) {
            exit('login');
        }
        redirect(url_to_pager('login').'?redirect_to='.getFullUrl(true));
    }
    fire_hook("user.subscription.hook", null);
    fire_hook('user.loggedin', null);
    return true;
});

register_filter("user-auth", function() {
    if (!is_loggedIn()) {
        if (is_ajax()) {
            exit('login');
        }
        redirect(url_to_pager('login').'?redirect_to='.getFullUrl(true));
    }
    fire_hook("user.subscription.hook", null);
    fire_hook('user.loggedin', null);
    return true;
});




register_filter("profile", function() {
    $username = segment(0);
    if ($username == 'me' and is_loggedIn()) $username = get_userid();
    $user = find_user($username);
    Pager::setCurrentPage('profile');
    if (!$user) return false;
    if (is_blocked($user['id'])) return false;
    $app = app();
    $app->profileUser = $user;
    $app->setTitle(get_user_name($app->profileUser));
    $app->setLayout('profile/layout');
    $app->topMenu = lang('profile');
    $design = get_user_design_details($user);
    if (config('design-profile', true) and $design) app()->design = $design;

    //register menu
    add_menu("user-profile", array('title' => lang('timeline'), 'link' => profile_url(null, $user), 'id' => 'timeline'));
    add_menu("user-profile", array('title' => lang('about'), 'link' => profile_url('about', $user), 'id' => 'about'));

    if (is_loggedIn()) get_menu("dashboard-main-menu", 'profile')->setActive(true);

    fire_hook("profile.started", null, array($user));
    return true;
});


register_filter("admin-auth", function($app) {
//    return true;c


    if (!is_loggedIn()) return redirect_to_pager("admin-login");
    if (!is_admin()) return redirect_to_pager("admin-login");

    admin_install_restrictions();

    get_menu("admin-menu", "admin-users")->addMenu(lang("members"), url_to_pager("admin-members-list"), "members");
    //get_menu("admin-menu", "admin-users")->addMenu(lang("add-member"), url_to_pager("admin-add-member"), "add-member");
    get_menu("admin-menu", "admin-users")->addMenu(lang("user-roles"), url_to_pager("admin-user-roles"), "user-roles");
    get_menu("admin-menu", "admin-users")->addMenu(lang("mailing-list"), url('admincp/mailing'), "mailing-list");
    get_menu("admin-menu", "admin-users")->addMenu(lang("verification-requests"), url('admincp/verify/requests'), "admin-verification");



    get_menu('admin-menu', 'admin-custom-field')->addMenu(lang('users'), '#', 'users-custom-fields');
    get_menu('admin-menu', 'admin-custom-field')->findMenu('users-custom-fields')
        ->addMenu(lang("categories"), url_to_pager("admin-custom-fields-category").'?type=user')
        ->addMenu(lang("fields"), url_to_pager("admin-user-custom-fields").'?type=user', "fields")
        ->addMenu(lang("add-new-custom-field"), url_to_pager("admin-user-custom-fields").'?action=add&type=user', "add-new-custom-field")
        ->addMenu(lang("add-category"), url_to_pager("admin-custom-fields-category").'?action=add&type=user', "add-category");



    $settingsMenu = get_menu("admin-menu", "settings");
    foreach(get_settings_menu() as $id => $title) {
        $settingsMenu->addMenu($title, url_to_pager("admin-settings")."?type=".$id);
    }

    //all plugin with settings file
    foreach(get_all_plugins() as $plugin => $info) {
        if (plugin_has_settings($plugin)) {
            $settingsMenu->addMenu($info['title'], url_to_pager("plugins-settings", array("plugin" => $plugin)), $plugin);
        }
    }

    //manage menus
    add_menu("admin-menu", array("id" => "posts", "title" => lang("manage-posts"), "link" => url('admincp/posts'), "icon" => "ion-android-chat"));
    //add_menu("admin-menu", array("id" => "photos", "title" => lang("manage-photos"), "link" => url('admincp/photos'), "icon" => "ion-ios-photos"));

    add_menu("admin-menu", array("id" => "static-pages", "title" => lang("static-pages"), "icon" => "ion-android-clipboard", "link" => "#"));
    get_menu("admin-menu", "static-pages")->addMenu(lang("manage"), url('admincp/static/pages'), "manage");
    get_menu("admin-menu", "static-pages")->addMenu(lang("add-new-page"), url('admincp/static/pages/add'), "add");


    get_menu("admin-menu", "cms")->addMenu(lang("language-manager"), "#", "admin-language");
    get_menu("admin-menu", "cms")->findMenu("admin-language")->addMenu(lang("language-packs"), url_to_pager("admin-languages"));
    get_menu("admin-menu", "cms")->findMenu("admin-language")->addMenu(lang("phrase-manager"), url_to_pager("admin-languages-phrase"));
    //get_menu("admin-menu", "cms")->findMenu("admin-language")->addMenu(lang("add-phrase"), url_to_pager("admin-languages-phrase")."?action=add");
    get_menu("admin-menu", "cms")->findMenu("admin-language")->addMenu(lang("create-language-pack"), url_to_pager("admin-languages")."?action=create");
    get_menu("admin-menu", "cms")->findMenu("admin-language")->addMenu(lang("import-language-pack"), url_to_pager("admin-languages")."?action=import");
    get_menu("admin-menu", "cms")->findMenu("admin-language")->addMenu(lang("update-language-phrases"), url_to_pager("admin-languages-phrase")."?action=update");
    get_menu("admin-menu", "cms")->addMenu(lang("manage-posts"), url('admincp/posts'), "admin-posts");

    //country manager
    get_menu("admin-menu", "cms")->addMenu(lang("country-manager"), url_to_pager("admin-country-manager"), "admin-country-manager");

    //get_menu("admin-menu", "cms")->addMenu(lang("pages-manager"), "#", "pages-manager");


    //emails management
    get_menu("admin-menu", "cms")->addMenu(lang("emails-manager"), "#", "email-manager");
    get_menu("admin-menu", "cms")->findMenu('email-manager')->addMenu(lang("email-templates"), url_to_pager('admin-email-templates'), "admin-email-templates")
        ->addMenu(lang("settings"), url_to_pager('admin-email-settings'), "admin-email-settings");
    /**
     * Admin themes menu
     */

    get_menu("admin-menu", "appearance")->addMenu(lang('themes'), url_to_pager('admin-themes', array('type' => 'frontend')), "admin-manage-themes");
    get_menu("admin-menu", "appearance")->addMenu(lang('layout-editor'), url('admincp/site/editor?type=layout'), "admin-site-editor");
    get_menu("admin-menu", "appearance")->addMenu(lang('menu'), url('admincp/site/editor?type=menu'), "admin-menu-editor");
    get_menu("admin-menu", "appearance")->addMenu(lang('customize'), url('admincp/site/editor?type=customize'), "admin-site-customize");
//    get_menu("admin-menu", "appearance")->findMenu("admin-manage-themes")
//        ->addMenu(lang('frontend'), url_to_pager('admin-themes', array('type' => 'frontend')))
//        ->addMenu(lang('backend'), url_to_pager('admin-themes', array('type' => 'backend')));
        //->addMenu(lang('mobile'), url_to_pager('admin-themes', array('type' => 'mobile')));
     get_menu("admin-menu", "appearance")->addMenu(lang("settings"), url_to_pager("admin-theme-settings"), 'admin-theme-settings');


    get_menu("admin-menu", "plugins")->addMenu(lang("manage"), url_to_pager("manage-plugins"), "manage");
    //get_menu("admin-menu", "plugins")->addMenu(lang("core-plugins"), url_to_pager("manage-plugins").'?core=true', "core");


    //add_menu("admin-menu", array("id" => "annoucement", "title" => lang("annoucement"), "link" => url_to_pager("annoucement") ));
    //add_menu("admin-menu", array("id" => "Help &amp; Support Center", "title" => lang("help-support"), "link" => url_to_pager("support") ));

    //get_menu("admin-menu", "cms")->addMenu(lang("blocks-manager"), url_to_pager("admin-blocks"), "blocks");

    fire_hook("admin-started", null);
    //tools sub-menu

    get_menu("admin-menu", "tools")->addMenu(lang("update-system"), url_to_pager('admin-update'));
    get_menu("admin-menu", "tools")->addMenu(lang("ban-filters"), "#", "admin-ban-filters");
    get_menu("admin-menu", "tools")->addMenu(lang("task-scheduler"), url('admincp/run/tasks'), "admin-task");

    get_menu("admin-menu", "tools")->findMenu("admin-ban-filters")->addMenu(lang("usernames"), url_to_pager("admin-ban-filters", array("type" => "usernames")));
   // get_menu("admin-menu", "tools")->findMenu("admin-ban-filters")->addMenu(lang("emails"), url_to_pager("admin-ban-filters", array("type" => "emails")));
    get_menu("admin-menu", "tools")->findMenu("admin-ban-filters")->addMenu(lang("ip-address"), url_to_pager("admin-ban-filters", array("type" => "ip")));
    //get_menu("admin-menu", "tools")->findMenu("admin-ban-filters")->addMenu(lang("display-names"), url_to_pager("admin-ban-filters", array("type" => "names")));
    get_menu("admin-menu", "tools")->findMenu("admin-ban-filters")->addMenu(lang("words"), url_to_pager("admin-ban-filters", array("type" => "words")));

    //add quick admincp dashboard quick links
    add_menu("admincp-quick-link", array('id' => 'general-settings', 'title' => lang('general-settings'), 'link' => url_to_pager("admin-settings")));
    add_menu("admincp-quick-link", array('id' => 'user-profile-fields', 'title' => lang('add-profile-fields'), 'link' => url_to_pager("admin-user-custom-fields").'?action=add&type=user'));
    add_menu("admincp-quick-link", array('id' => 'update-system', 'title' => lang('update-system'), 'link' => url_to_pager('admin-update')));
    add_menu("admincp-quick-link", array('id' => 'update-phrases', 'title' => lang('update-language-phrases'), 'link' => url_to_pager("admin-languages-phrase")."?action=update"));
    add_menu("admincp-quick-link", array('id' => 'manage-plugins', 'title' => lang('manage-plugins'), 'link' => url_to_pager("manage-plugins")));

    register_block_page('account', lang('user-account-settings'));
    register_block_page('saved', lang('user-saved-page'));
    return true;
});

register_filter("admin-login", function($app) {
    $app->setThemeType("backend");
    return true;
});


/**
 * Frontend menu for Frontend page
 */
foreach(get_cache_static_pages_footer() as $page){
//add_menu("footer-menu", array("id" => "static-".$page['slug'], "title" => lang($page['title']), "link" => url($page['slug'])));
}

/**
 * Register blocks
 */
//register_block('account/profile-card', lang('user-profile-card'));
register_block('block/html', 'Html Block', null, array(
    'title' => array(
        'title' => lang('box-title'),
        'description' => lang('box-title-desc'),
        'type' => 'text',
        'value' => ''
    ),

    'content' => array(
        'title' => lang('html-content'),
        'description' => lang('html-content-desc'),
        'type' => 'textarea',
        'value' => ''
    )
));

register_hook('system.started', function($app) {
    if (config('pusher-driver', 'ajax') == 'ajax') {
        setPusher(new AjaxPusher());
        if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
            register_asset("js/ajax-pusher.js");
        }
    }

    //register_hook('admin-started', function() {admin_install_restrictions(); });
});

/**
 * Add menus for admin
 */

add_menu("admin-menu", array('icon' => 'ion-arrow-graph-up-right', "id" => "admin-statistic", "title" => lang("dashboard"), "link" => url_to_pager("admin-statistic")));
add_menu("admin-menu", array('icon' => 'ion-android-people', "id" => "admin-users", "title" => lang("user-manager"), "link" => "#"));
add_menu("admin-menu", array('icon' => 'ion-ios-information-outline', "id" => "admin-custom-field", "title" => lang('profile-questions'), "link" => "#"));
add_menu("admin-menu", array("id" => "settings", "title" => lang("settings"), "icon" => "ion-android-settings", "link" => "#"));
add_menu("admin-menu", array("id" => "cms", "title" => lang("site-manager"), "link" => "#", "icon" => "ion-android-options"));
add_menu("admin-menu", array("id" => "appearance", "title" => lang("appearance"), "link" => "#", "icon" => "ion-android-color-palette"));
add_menu("admin-menu", array("id" => "plugins", "title" => lang("plugins-manager"), "link" => "#", "icon" => "ion-map"));
add_menu("admin-menu", array("id" => "tools", "title" => lang("tools"), "link" => "#", "icon" => "ion-ios-settings"));
Pager::offMenu('admin-menu');

//menu locations
add_menu_location('main-menu', 'main-menu');
//add_menu_location('account-menu', 'account-menu');
add_menu_location('header-account-menu', 'account-menu');
add_menu_location('footer-menu', 'footer-menu');

//add available menus
add_available_menu('home', '', 'ion-home');
add_available_menu('profile', 'me', 'ion-ios-contact-outline');
add_available_menu('find-friends', 'suggestions', 'ion-android-person-add');
add_available_menu('saved', 'saved');

foreach(get_cache_static_pages() as $page) {
    if(isset($page['title'])) add_available_menu(lang(strtolower($page['title'])), $page['slug']);

}

//hook to prevent profile preview
register_hook('site-preview-profile', function($result) {
    $result['url'] = url('me');
    return $result;
});

