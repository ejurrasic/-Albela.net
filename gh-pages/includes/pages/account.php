<?php
get_menu("dashboard-main-menu", 'profile')->setActive(true);
register_account_menu();
/**
 * @param $app
 * @return mixed
 */
function general_pager($app) {
    $content = "";
    $action = input("action", "general");
    $app->topMenu = lang('settings');
    $message = "";

    switch($action) {
        case "delete":
            $app->setTitle(lang('delete-my-account'));
            $message = null;
            $password = input('password');
            //delete user account
            if (!hash_check($password, get_user_data('password'))) {
                $message = lang('invalid-password');
            } else {
                delete_user();
                redirect_to_pager('logout');
            }
            $content = view("account/delete", array('message' => $message));
            break;
        case "general":
            $val = input('val');

            if ($val) {
                CSRFProtection::validate();
                $process = true;
                $usernameChanged = false;
                if (input_file('image')) {

                    $uploader = new Uploader(input_file('image'), 'image');
                    $uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/');
                    if ($uploader->passed()) {
                        $avatar = $uploader->resize()->toDB("profile-avatar", get_userid(), 1)->result();
                        update_user_avatar($avatar, null, $uploader->insertedId);
                    } else {
                        $process = false;
                        $message = $uploader->getError();
                    }
                }

                /**
                 * @var $username
                 * @var $email_address
                 */
                extract($val);
                if (config('allow-change-username', true) and isset($username) and $username != get_user_data('username')) {
                    //ok user want to really change username, let check for usage from other users
                    $userNameValidator = validator(array('username' => $username), array('username' => 'required|alphanum|usernameedit'));
                    if (validation_passes()) {

                        $usernameChanged = true;
                        if (config('loose-verify-badge-username', true)) {
                            $val['verified'] = 0;
                        }
                    } else {
                        $process = false;
                        $message = validation_first();
                    }

                }

                if (config('allow-change-email', true) and isset($email_address) and $email_address != get_user_data('email_address')) {
                    $emailValidator = validator(array('email_address' => $email_address), array('email_address' => 'email'));
                    if (validation_passes()) {
                        $userid = get_userid();
                        $check = db()->query("SELECT id FROM users WHERE email_address='{$email_address}' AND id!='{$userid}'");
                        if ($check->num_rows > 0) {
                            $process = false;
                            $message = lang('email-address-is-in-use');
                        }
                    } else {
                        $process = false;
                        $message = validation_first();
                    }

                }

                $nameValidator = validator($val, array(
                    'first_name' => 'required',
                    'last_name' => 'required'
                ));
                if (!validation_passes()) {
                    $message = validation_first();
                    $process = false;
                }
                if ($process) {
                    save_user_general_settings($val);
                    if ($usernameChanged) {
                        login_with_user(find_user(get_userid()), true);
                    }

                    redirect(url_to_pager("account"));
                }
            }
            $app->setTitle(lang('general-settings'));
            $content = view("account/general", array('message' => $message));
            break;
        case 'password':
            $val = input("val");
            $app->setTitle(lang('update-password'));
            $success = "";
            if ($val) {
		        CSRFProtection::validate();
                /**
                 * expected values
                 * @var $new
                 * @var $current
                 * @var $confirm
                 */
                extract($val);
                if ($new and $current and $confirm) {
                    $currentPass = get_user_data("password");
                    //$current = hash_make($current);
                    if (hash_check($current, $currentPass)) {
                        if ($new != $confirm) {
                            $message = "The new password does not match";
                        } else {
                            $password = hash_make($new);
                            update_user(array('password' => $password));
                            $success = "Password has been changed";
                        }
                    } else {
                        $message = "Your current password does not match";
                    }
                } else {
                    $message = "All fields are required";
                }
            }
            $content = view('account/password', array('message' => $message, 'success' => $success));
            break;
        case 'notifications':
            $app->setTitle(lang('notification-settings'));
            if ($val = input('val')) {
                save_privacy_settings($val);
                redirect(url_to_pager('account').'?action=notifications');
            }
            $content = view("account/notifications");
            break;
        case 'privacy':
            $app->setTitle(lang('privacy-settings'));
            if ($val = input('val')) {
                save_privacy_settings($val);
                redirect(url_to_pager('account').'?action=privacy');
            }
            $content = view("account/privacy");
            break;
        case 'blocked':
            $app->setTitle(lang('blocked-members'));
            $content = view('account/block-members', array('users' => get_blocked_members()));
            break;
        case "profile":
            $id = input("id");
            $category = get_custom_field_category($id);
            if (!$category) redirect_to_pager("account");
            $app->setTitle(lang($category['title']));
            $val = input("val");
            if ($val) {
		        CSRFProtection::validate();
                $fieldRules = array();
                foreach(get_custom_fields('user', $id) as $field) {
                    if ($field['required']) {
                        $fieldRules[$field['title']] = 'required';
                    }
                }
                if ($fieldRules) $validator = validator(input('val.fields'), $fieldRules);
                if (validation_passes() && !$message) {
                    save_user_profile_details($val);
                    redirect(url_to_pager("account")."?action=profile&id=".$category['id']);
                } else {
                    $message = validation_first();
                }
            }

            $content = view("account/profile", array("slug" => $id, "category" => $category, 'message' => $message));
            break;
        default:
            $content = fire_hook('account.settings', null, array($action));
            break;
    }
    return $app->render(view("account/layout", array("content" => $content)));
}

function block_user_pager($app) {
    $userid = segment(2);
    block_user($userid);
    return go_to_user_home();
}

function unblock_user_pager() {
    $userid = segment(2);
    unblock_user($userid);
    return redirect_back();
}
