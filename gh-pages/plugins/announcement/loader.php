<?php
load_functions("announcement::announcement");



register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset("announcement::js/announcement.js");
        //register_asset("ads::css/ads.css");
    }

});



register_hook("admin-started", function() {
    get_menu("admin-menu", "cms")->addMenu(lang("announcement::announcements"), url_to_pager('admin-announcement'), "admin-announcement");
    //quick links
    add_menu("admincp-quick-announcement", array('id' => 'create-announcement', 'title' => lang('announcement::manage-announcement'), 'link' =>  url_to_pager('admin-announcement')));


});

register_hook("main.extend", function() {
   if (segment(0) != null) echo view('announcement::render');
});


register_pager("admincp/announcements", array('use' => "announcement::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admin-announcement'));
register_pager("admincp/announcement/create", array('use' => "announcement::admincp@create_pager", 'filter' => 'admin-auth', 'as' => 'admin-announcement-create'));
register_pager("admincp/announcement/manage", array('use' => "announcement::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admin-announcement-manage'));

register_pager("announcement/close", array('use' => 'announcement::announcement@close_pager', 'filter' => 'auth'));

register_pager("announcements", array('use' => 'announcement::announcement@list_pager', 'filter' => 'auth'));

add_available_menu('announcement::announcement', url('announcements'), null);



register_hook('admin-started', function() {
    register_block_page('announcement', lang('announcement::announcements'));
});

