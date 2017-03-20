<?php
function user_need_welcome_page($user = null) {
    $user = ($user) ? $user : get_user();
    if (!$user['welcome_passed']) return true;
    return false;
}


register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("getstarted::css/getstarted.css");
        register_asset("getstarted::js/getstarted.js");
    }
});
//add to settings menu for easy navigation
register_hook("admin-started", function() {
    //get_menu("admin-menu", "settings")->addMenu(lang("getstarted::getstarted"), url("admin/plugin/settings/getstarted"));
});

/**Register pagers **/
register_pager("user/welcome", array('use' => 'getstarted::signup@welcome_pager', 'as' => 'signup-welcome', 'filter' => 'auth'));

/**End of registering pagers for getstarted plugin***/