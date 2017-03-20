<?php
function manage_pager($app) {
    get_menu("admin-menu", "plugins")->setActive();
    $app->setTitle(lang("manage-plugins"));

    $action = input("action");
    $core = input('core', false);
    switch($action) {
        case 'activate':
                $id = input("id");
                if (!$id) redirect_to_pager('manage-plugins');
                plugin_activate($id);

                return redirect_to_pager("manage-plugins", array(), admin_flash_message(lang("plugin-activated", array('name' => ucwords($id)))));
            break;
        case 'deactivate':
            $id = input("id");
            if (!$id) redirect_to_pager('manage-plugins');
            plugin_deactivate($id);
            return redirect_to_pager("manage-plugins", array(), admin_flash_message(lang("plugin-deactivated", array('name' => ucwords($id)))));
            break;
        case 'update':
            $id = input("id");
            if (!$id) redirect_to_pager('manage-plugins');
            plugin_update($id, true);
            return redirect_to_pager("manage-plugins", array(), admin_flash_message(lang("plugin-updated", array('name' => ucwords($id)))));

            break;
        case 'delete':
            $id = input("id");
            if (!$id) redirect_to_pager('manage-plugins');
            plugin_delete($id);
            return redirect_to_pager("manage-plugins", array(), admin_flash_message(lang("plugin-removed", array('name' => ucwords($id)))));

            break;
    }

    return $app->render(view("plugins/manage", array("plugins" => get_all_plugins(), 'core' => $core)));
}

function plugin_settings_pager($app) {
    get_menu("admin-menu", "settings")->setActive();
    $app->setTitle(lang("manage-plugins"));

    $settings = plugin_get_settings(segment(3));
    if (empty($settings)) redirect_back();

    $val = input("val");
    if ($val) {
		CSRFProtection::validate();
        //we are saving the settings
        save_admin_settings($val);
        admin_flash_message(lang("plugin-settings-saved", array('name' => ucwords(segment(3)))));
    }

    return $app->render(view("settings/content", array(
        'settings' => $settings['settings'],
        'title' => $settings['title'],
        'description' => $settings['description']
    )));
}

