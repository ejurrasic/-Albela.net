<?php
function update_page($app) {
    /**
     * This pager update the system database tables e.t.c, settings e.t.c
     *
     * load functions for updating the database
     */
    require path("includes/database/install.php");
    require path("includes/database/upgrade.php");
    //core_install_database();
    core_upgrade_database();
    update_core_settings();

    //update plugins
    $plugins = get_all_plugins();

    foreach($plugins as $key => $info) {
        plugin_update($key, true);
    }
    return redirect_back(array('id' => 'admin-message', 'message' => lang('update-system-message')));
    //return $app->render(view("update/content"));
}
 