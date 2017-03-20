<?php
load_functions("help::help");
register_pager("helps", array('use' => 'help::help@help_pager', 'as' => 'helps'));
register_pager("help/{slugs}", array('use' => 'help::help@help_page_pager', 'as' => 'help'))->where(array('slugs' => '[a-zA-Z0-9\-\_]+'));
register_pager("help/{slugs}/{slugs}", array('use' => 'help::help@help_page_pager', 'as' => 'sub-help'))->where(array('slugs' => '[a-zA-Z0-9\-\_]+'));
register_pager("admincp/helps", array('use' => "help::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-helps'));
register_pager("admincp/help/add", array('use' => "help::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-help-add'));
register_pager("admincp/help/manage", array('use' => "help::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-help-manage'));
register_pager("admincp/help/categories", array('use' => "help::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admincp-help-category'));
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset("help::css/help.css");
        register_asset("help::js/help.js");
    }
});
add_menu('header-account-menu', array('id' => 'help', 'title' => lang('help::helps'), 'link' => url('helps')));
register_hook("admin-started", function() {

    add_menu("admin-menu", array('icon' => 'ion-help-buoy', "id" => "admin-help", "title" => lang('help::manage-help'), "link" => '#'));
    get_menu('admin-menu', 'plugins')->addMenu(lang('help::help-manager'), '#', 'admin-help');
    get_menu("admin-menu", "plugins")->findMenu('admin-help')->addMenu(lang("help::helps"), url_to_pager("admincp-helps"), "helps");
    get_menu("admin-menu", "plugins")->findMenu('admin-help')->addMenu(lang("help::categories"), url_to_pager("admincp-help-category"), "categories");

});
add_menu_location('helps-menu', 'help::helps-menu');
add_available_menu('help::helps', 'helps', 'ion-help-buoy');
