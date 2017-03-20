<?php
register_hook('system.started', function() {
    register_pager("{id}", array("use" => "profile@profile_pager", "as" => "profile", 'filter' => 'profile', 'block' => lang('profile')))
        ->where(array('id' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{id}/about", array("use" => "profile@profile_about_pager", "as" => "profile-about", 'filter' => 'profile'))
        ->where(array('id' => '[a-zA-Z0-9\_\-]+'));
});
/**
 * Register pagers
 */


//Home pager
register_pager("/", array('use' => "home@home_pager", 'as' => 'home'));
register_pager("login", array('use' => "login@login_pager", "as" => "login"));
register_pager("forgot-password", array('use' => "login@forgot_password_pager", "as" => "forgot-password"));
register_pager("reset/password", array('use' => "login@reset_password_pager", "as" => "reset-password"));
if (config('user-signup', true)) {
    register_pager("signup", array('use' => "signup@signup_pager", "as" => "signup"));
    register_pager("signup/activate", array('use' => "signup@signup_activate_pager", "as" => "signup-activate"));
}
register_pager("translate/text", array('use' => "home@translate_pager"));



/**
 * Frontend pager
 */
/*additional pages*/


/**
 * Start of admin pager registration
 */
register_pager("admincp/login", array('use' => "admincp/login@login_pager", 'filter' => 'admin-login', 'as' => 'admin-login'));
register_pager("admincp", array('use' => "admincp/statistic@statistic_page", 'filter' => 'admin-auth', 'as' => 'admin-statistic'));
register_pager("admincp/load/statistics", array('use' => "admincp/statistic@load_pager", 'filter' => 'admin-auth'));
register_pager("admincp/settings", array('use' => "admincp/settings@settings_page", 'filter' => 'admin-auth', 'as' => 'admin-settings'));
register_pager("admincp/update/system", array('use' => "admincp/update@update_page", 'filter' => 'admin-auth', 'as' => 'admin-update'));
register_pager("admincp/custom-fields", array('use' => "admincp/user@custom_fields_pager", 'filter' => 'admin-auth', 'as' => 'admin-user-custom-fields'));
register_pager("admincp/custom-fields/category", array('use' => "admincp/user@custom_fields_category_pager", 'filter' => 'admin-auth', 'as' => 'admin-custom-fields-category'));

register_pager("admincp/posts", array('use' => "admincp/user@posts_pager", 'filter' => 'admin-auth', 'as' => 'admin-posts'));
register_pager("admincp/photos", array('use' => "admincp/user@photos_pager", 'filter' => 'admin-auth', 'as' => 'admin-photos'));


register_pager("admincp/themes/customize/{type}/{theme}", array('use' => "admincp/themes@customize_themes_pager", 'filter' => 'admin-auth', 'as' => 'admin-customize-themes'))
    ->where(array('type' => '[a-zA-Z0-9]+', 'theme' => '[a-zA-Z0-9]+'));

register_pager("admincp/themes/setting", array(
    'use' => 'admincp/themes@setting_pager',
    'filter' => 'admin-auth',
    'as'     => 'admin-theme-settings'
));
register_pager("admincp/themes/{type}", array('use' => "admincp/themes@themes_pager", 'filter' => 'admin-auth', 'as' => 'admin-themes'))
    ->where(array('type' => '[a-zA-Z0-9]+'));

register_pager("admincp/site/editor", array('use' => "admincp/themes@editor_pager", 'filter' => 'admin-auth', 'as' => 'admin-site-editor'));
register_pager("admincp/site/preview", array('use' => "admincp/themes@site_preview_pager", 'filter' => 'admin-auth', 'as' => 'admin-site-preview'));
register_pager("admincp/site/editor/layout", array('use' => "admincp/themes@save_layout_pager", 'filter' => 'admin-auth', 'as' => 'admin-site-editor-layout'));
register_pager("admincp/site/editor/widget/settings/load", array('use' => "admincp/themes@load_widget_settings_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/widget/settings/save", array('use' => "admincp/themes@save_widget_settings_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/layout/page", array('use' => "admincp/themes@layout_load_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/save/settings", array('use' => "admincp/themes@save_theme_settings_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/page/info", array('use' => "admincp/themes@load_page_info_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/save/page/info", array('use' => "admincp/themes@save_page_info_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/save/new/page", array('use' => "admincp/themes@add_page_info_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/menu/add", array('use' => "admincp/themes@add_menu_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/menu/sort", array('use' => "admincp/themes@sort_menu_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/menu/delete", array('use' => "admincp/themes@delete_menu_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/menu/link/add", array('use' => "admincp/themes@add_link_menu_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/menu/edit", array('use' => "admincp/themes@edit_menu_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/menu/save", array('use' => "admincp/themes@save_menu_pager", 'filter' => 'admin-auth'));
register_pager("admincp/site/editor/menu/change", array('use' => "admincp/themes@change_menu_location_pager", 'filter' => 'admin-auth'));


register_pager("admincp/blocks", array('use' => "admincp/blocks@blocks_pager", 'filter' => 'admin-auth', 'as' => 'admin-blocks'));
register_pager("admincp/block/register", array('use' => "admincp/blocks@register_blocks_pager", 'filter' => 'admin-auth'));
register_pager("admincp/block/sort", array('use' => "admincp/blocks@sort_blocks_pager", 'filter' => 'admin-auth'));
register_post_pager("admincp/block/save", array('use' => "admincp/blocks@save_blocks_pager", 'filter' => 'admin-auth'));
register_post_pager("admincp/block/remove", array('use' => "admincp/blocks@remove_blocks_pager", 'filter' => 'admin-auth'));

register_pager("admincp/ban/filters/{type}", array('use' => "admincp/settings@ban_filter_pager",
    'filter' => 'admin-auth', 'as' => 'admin-ban-filters'))->where(array("type" => "[a-zA-Z0-9_\-]+"));

//country manager
register_pager("admincp/countries", array('use' => "admincp/country@country_pager", 'filter' => 'admin-auth', 'as' => 'admin-country-manager'));
register_pager("admincp/add/state", array('use' => "admincp/country@state_pager", 'filter' => 'admin-auth', 'as' => 'admin-add-state'));

//admin language manager
register_pager("admincp/languages", array('use' => "admincp/language@language_pager", 'filter' => 'admin-auth', 'as' => 'admin-languages'));
register_pager("admincp/language/phrases", array('use' => "admincp/language@phrases_pager", 'filter' => 'admin-auth', 'as' => 'admin-languages-phrase'));

//admin plugins
register_pager("admincp/plugins", array("use" => "admincp/plugins@manage_pager", "filter" => "admin-auth", "as" => "manage-plugins"));
register_pager("admincp/plugin/settings/{plugin}", array("use" => "admincp/plugins@plugin_settings_pager", "filter" => "admin-auth", "as" => "plugins-settings"))
->where(array('plugin' => '[a-zA-Z0-9\_\-]+'));

register_pager("admincp/static/pages", array("use" => "admincp/pages@manage_pager", "filter" => "admin-auth", "as" => "manage-statics"));
register_pager("admincp/static/pages/add", array("use" => "admincp/pages@add_pager", "filter" => "admin-auth", "as" => "manage-statics-add"));
register_pager("admincp/static/pages/manage", array("use" => "admincp/pages@edit_pager", "filter" => "admin-auth", "as" => "manage-statics-manage"));

//admin email templates
register_pager("admincp/email/templates", array("use" => "admincp/email@templates_pager", "filter" => "admin-auth", "as" => "admin-email-templates"));
register_pager("admincp/email/settings", array("use" => "admincp/email@settings_pager", "filter" => "admin-auth", "as" => "admin-email-settings"));

register_pager("admincp/mailing", array("use" => "admincp/email@mailing_pager", "filter" => "admin-auth", "as" => "admin-mailing"));
//user manager
register_pager("admincp/user/roles", array("use" => "admincp/user@roles_pager", "filter" => "admin-auth", "as" => "admin-user-roles"));
register_pager("admincp/verify/requests", array("use" => "admincp/user@verify_requests_pager", "filter" => "admin-auth", "as" => "admin-requests"));
register_pager("admincp/verify/action", array("use" => "admincp/user@verify_requests_action_pager", "filter" => "admin-auth", "as" => "admin-requests-action"));
register_pager("admincp/members", array("use" => "admincp/user@members_pager", "filter" => "admin-auth", "as" => "admin-members-list"));
register_pager("admincp/user/action", array("use" => "admincp/user@user_action_pager", "filter" => "admin-auth", "as" => "admin-user-action"));
register_pager("admincp/run/tasks", array("use" => "admincp/task@run_pager", "filter" => "admin-auth", "as" => "admin-run-tasks"));

//end of admin pager registration
//logout pager registration
register_pager("logout", array('use' => "logout@logout_pager", 'as' => 'logout'));
register_pager("change/language/{lang}", array("use" => "language@language_pager", "as" => "change-language"))
    ->where(array('lang' => '[a-zA-Z0-9]+'));

//account settings pager reg
register_pager("account", array("use" => "account@general_pager", "as" => "account", "filter" => "auth"));
register_pager("block/user/{id}", array("use" => "account@block_user_pager", "as" => "block-user", "filter" => "auth"))->where(array('id' => '[0-9]+'));
register_pager("unblock/user/{id}", array("use" => "account@unblock_user_pager", "as" => "unblock-user", "filter" => "auth"))->where(array('id' => '[0-9]+'));
register_pager("user/change/cover", array("use" => "profile@profile_change_cover_pager", "filter" => "auth"));
register_pager("user/cover/remove", array("use" => "profile@remove_cover_pager", "filter" => "auth"));
register_pager("user/change/avatar", array("use" => "profile@change_logo_pager", "filter" => "auth"));
register_pager("user/save", array("use" => "user@save_pager", "filter" => "auth"));
register_pager("saved", array("use" => "user@saved_pager", "filter" => "auth", 'as' => 'saved'));
register_pager("user/verify/request", array("use" => "user@verify_request_pager", "filter" => "auth"));
register_pager("save/design", array("use" => "user@save_design_pager", "filter" => "auth", "as" => 'save-design'));
register_pager("saved/{type}", array("use" => "user@saved_pager", "filter" => "auth"))->where(array('type' => '[a-zA-Z0-9]+'));
register_pager("preview/card", array("use" => "profile@load_preview_pager"));
register_pager("user/profile/cover/reposition", array("use" => "profile@profile_position_cover_pager", "filter" => "auth"));

register_pager("embed/video", array("use" => "video@play_video_pager", "as" => "play-video"));

register_pager("ajax/push/check", array("use" => "ajax@check_pager", "filter" => "auth"));


register_pager("user/tag/suggestion", array('use' => 'user@tag_suggestion_pager', 'filter' => 'auth'));

register_pager("run/tasks", array('use' => 'task@run_pager'));

foreach(get_cache_static_pages() as $page) {
    register_pager($page['slug'], array('use' => 'static@render_pager'));
}

