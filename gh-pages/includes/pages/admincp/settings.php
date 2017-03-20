<?php
//load_functions("admin_settings");


function settings_page($app) {
    $type = input("type", "general");
    $val = input("val");

    get_menu("admin-menu","settings")->setActive();

    if ($val) {
		CSRFProtection::validate();
        //we are saving the settings
        save_admin_settings($val);
        if ($type == 'general') {
            delete_file(path('storage/assets/'));
            redirect(url('admincp/settings?type=general'));
        }
    }
    $settings = get_settings($type);
    if (!$settings) return redirect("settings");

    $app->setTitle($settings['title']);
    return $app->render(view("settings/content", array(
        'settings' => $settings['settings'],
        'title' => $settings['title'],
        'description' => $settings['description']
    )));
}

function ban_filter_pager($app) {
    $type = segment(3);

    $app->setTitle(lang('manage-ban-filters'));

    get_menu('admin-menu', 'tools')->setActive();
    get_menu('admin-menu', 'tools')->findMenu('admin-ban-filters')->setActive();

    $accepted = array("usernames", "emails", "names", "ip", "words");
    if (!in_array(strtolower($type), $accepted)) redirect_to_pager("admin-statistic");
    $var = "ban_filters_".$type;
    $val = input("val");
    if ($val) {
		CSRFProtection::validate();
        save_admin_settings($val);
        redirect_to_pager("admin-ban-filters", array("type" => $type));
    }
    return $app->render(view("settings/ban-filter", array("type" => $type, "var" => $var)));
}
 