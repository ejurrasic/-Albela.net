<?php
//load functions and assets
load_functions('restore::restore');
register_asset("restore::css/restore.css");
register_asset("restore::js/restore.js");

register_pager("admincp/backup-files{append}", array(
    'filter'=> 'admin-auth',
    'as' => 'backup-files',
    'use' => 'restore::restore@backup_files'))
    ->where(array('append' => '.*'));

register_pager("admincp/restore-files{append}", array(
    'filter'=> 'admin-auth',
    'as' => 'restore-files',
    'use' => 'restore::restore@restore_files'))
    ->where(array('append' => '.*'));

register_pager("admincp/restore{append}", array(
    'filter'=> 'admin-auth',
    'as' => 'restore-files-categories-list',
    'use' => 'restore::admincp@restore_pager'))
    ->where(array('append' => '.*'));


register_hook("admin-started", function() {
    get_menu("admin-menu", "tools")->addMenu(lang("restore::restore-manager"), "#", "restore-manager");
    get_menu("admin-menu", "tools")->findMenu("restore-manager")->addMenu(lang("restore::restore-title"), url_to_pager("restore-files-categories-list"), 'restore-files-categories-list');
});


