<?php
function login_pager($app) {
    $app->setLayout("layouts/login");
    $val = input('val');
    $message = null;
    $app->setTitle(lang('login'));
    register_asset("css/login.css");

    if ($val) {
		CSRFProtection::validate();
        load_functions("users");
        /**
         * @var $username
         * @var $password
         */
        extract($val);
        $login = login_user($username, $password, input("val.remember"));
        //exit($login);
        if ($login) redirect(url('admincp'));
        $message = "Failed to login into admin panel";
    }

    return $app->render(view("login/content", array("message" => $message)));
}