<?php
/**
 * Signup welcome steps pager
 */
function welcome_pager($app) {
    $step = input('step', 'info');
    $content = "";
    $message = null;
    session_put('welcome-page-visited', 'visited');
    //get_menu("dashboard-menu", 'user-welcome')->setActive();
    $app->onHeaderContent = false;
    $app->setTitle(lang('welcome'));

    switch($step) {
        case 'import':
            $content = view("getstarted::import", array('message' => $message));
            break;
        case 'add-people':
            $content = view("getstarted::add-people", array('message' => $message));
            break;
        case 'finish':
            update_user(array('welcome_passed' => 1), null, true);
            return go_to_user_home();
            break;
        default:
            $val = input('val');
            $message = null;
            if (get_user_data('avatar')) return redirect((plugin_loaded('social') ? url_to_pager('signup-welcome').'?step=import' : url_to_pager('signup-welcome').'?step=add-people'));
            if ($val) {
                CSRFProtection::validate();
                if (input_file('avatar')) {
                    $uploader = new Uploader(input_file('avatar'), 'image');
                    $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/');
                    if ($uploader->passed()) {
                        $avatar = $uploader->resize()->toDB("profile-avatar", get_userid(), 1)->result();

                        update_user_avatar($avatar, null, $uploader->insertedId, false);
                    } else {
                        $message = $uploader->getError();
                    }
                }
                if (!$message) {
                    update_user(array('bio' => input('val.bio')));
                    return redirect((plugin_loaded('social') ? url_to_pager('signup-welcome').'?step=import' : url_to_pager('signup-welcome').'?step=add-people'));
                }
            }
            $content = view("getstarted::info", array('message' => $message));
            break;
    }
    //$content = $app->view("getstarted::welcome/layout");
    return $app->render($content);
}

