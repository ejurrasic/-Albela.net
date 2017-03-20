<?php
function signup_pager($app) {
    $val = input("val");
    $message = null;
    load_functions('country');
    $app->setTitle(lang("join-network"));

    if (is_loggedIn()) redirect_to_pager('feed');
    extract(fire_hook("signup.form.check", array(), array('status' => true, 'message' => null)));
    if ($val) {
		CSRFProtection::validate();
        if (config('enable-captcha') == 2) {
            require_once path('includes/libraries/recaptcha/autoload.php');
            $recaptcha = new \ReCaptcha\ReCaptcha(config('recaptcha-secret'));
            $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        }
        if (!config("enable-captcha") or
            (config('enable-captcha') == 1 and strtolower(session_get("sv_captcha")) == strtolower(input('captcha')))
            or(config('enable-captcha') == 2 and $resp->isSuccess())) {
            $rules = array(
                'first_name' => 'required|predefined',
                'last_name' => 'required|predefined',
                'username'  => 'required|predefined|alphanum|min:3|username',
                'email_address' => 'required|email|unique:users',
                'password'  => 'required|min:6',
                'birth_day' => 'required',
                'birth_month'  => 'required',
                'birth_year' => 'required',
                'country' => 'required'
            );
            if(input('val.password') != input('val.cpassword')) {
                $message = 'Password do not match';
            }
            $gender = input('val.gender');
            if(!($gender == 'male' || $gender == 'female' || empty($gender))) {
                $message = 'Invalid Gender';
            }
            if(!is_valid_country(input('val.country'))) {
                $message = 'Invalid Country';
            }
            if(!is_numeric(input('val.birth_day'))) {
                $message = 'Invalid Birth Day';
            }
            $months = config('months');
            if(!array_key_exists(input('val.birth_month'), $months)) {
                $message = 'Invalid Birth Month';
            }
            $currentYear = date('Y');
            $minAge = config("birthdate-min-age", 10);
            $maxYear = $currentYear - $minAge;
            if(input('val.birth_year') > $maxYear) {
                $message = 'Year of birth exceeds the minimum allowed';
            }
            $fieldRules = array();
            foreach(get_form_custom_fields('user') as $field) {
                if ($field['required']) {
                    $fieldRules[$field['title']] = 'required';
                }
            }

            $validator = validator($val, $rules);
            if ($fieldRules) $validator = validator(input('val.fields'), $fieldRules);

            if (validation_passes() && !$message) {
                $added = add_user($val);
                if ($added) {
                    fire_hook("signup.completed", null, array($added, $val));
                    if(config('user-activation', false)) {
                        //direct
                        send_user_welcome_email($val['username']);
                        send_user_activation_link($val['username']);
                        redirect_to_pager('signup-activate');
                    }
                    send_user_welcome_email($val['username']);
                    $user = find_user($added);
                    session_put("sv_loggin_username", $user['id']);
                    session_put("sv_loggin_password", $user['password']);
                    return go_to_user_home(null, $user);
                }
            } else {
                $message = $message ? $message : validation_first();
            }

        } else {
            $message = lang('invalid-captcha-code');
        }
    }
    return $app->render(view("signup/content", array('message' => $message)));
}

function signup_activate_pager($app) {
    if (is_loggedIn()) redirect_to_pager('feed');
    $app->setTitle(lang('activate-email'));

    $hash = input('code');
    if ($hash) {
        $verifyHash = mail_hash_valid($hash, true);
        if ($verifyHash) {
            activate_user($verifyHash);
            $user = find_user($verifyHash);
            login_with_user($user);
            delete_mail_hash($hash);

            return go_to_user_home();
        }
    }
    $message = "";
    $email = input('email');
    if ($email) {
        $send = send_user_activation_link($email);
        if (!$send) {
            $message = lang('failed-to-send-activation-link');
        }
    }
    return $app->render(view('signup/activate', array('message' => $message)));
}

