<?php
load_functions("country");
get_menu('admin-menu', 'cms')->setActive();
get_menu('admin-menu', 'cms')->findMenu('admin-country-manager')->setActive();

function country_pager($app) {
    $action = input("action", "list");
    $content = "";

    switch($action) {
        case 'edit':
            $country = get_country(input("id"));
            if (!$country) redirect_to_pager("admin-country-manager");

            $val = input("country");
            if ($val) {
		CSRFProtection::validate();
                save_country(input("id"), $val);
                redirect_to_pager("admin-country-manager");
            }
            $content = view("country/edit", array("id" => input("id"), "country" => $country));
            break;
        case 'delete':
            delete_country(input("id"));
            redirect_to_pager("admin-country-manager");
            break;

        case 'add':
            $message = null;
            $country = input("country");
            $app->setTitle(lang('add-country'));
            if ($country) {
                add_country($country);
                redirect_to_pager("admin-country-manager");
            }
            $content = view("country/add", array("message" => $message));
            break;
        default:
            $app->setTitle(lang('manage-countries'));
            $content = view("country/lists");
            break;
    }

    return $app->render($content);
}

