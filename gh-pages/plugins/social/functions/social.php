<?php

function social_register_user($details, $redirect = true) {
    $expected = array(
        'first_name' => '',
        'last_name' => '',
        'email_address' => '',
        'password' => '',
        'username' => '',
        'auth' => '',
        'authId' => '',
        'avatar' => '',
        'gender' => '',
        'country' => '',
        'social_email' => ''

    );

    /**
     * @var $first_name
     * @var $last_name
     * @var $email_address
     * @var $password
     * @var $username
     * @var $auth
     * @var $authId
     * @var $avatar
     * @var $gender
     * @var $country
     * @var $social_email
     */
    extract(array_merge($expected, $details));
    $badWords = config('ban_filters_usernames', '');
    if ($badWords) {
        $badWords = explode(',', $badWords);
        if (in_array($username, $badWords)) return redirect(url('signup'));
    }
    $query = db()->query("SELECT id,password FROM users WHERE email_address='{$email_address}'");
    if ($query->num_rows > 0) {
        //user is coming back
        $user = $query->fetch_assoc();
        session_put("sv_loggin_username", $user['id']);
        session_put("sv_loggin_password", $user['password']);
        if ($redirect) return redirect(go_to_user_home());
    } else {

        $role = 2;
        $active = 1;
        $activated = 1;
        $password = hash_make((String) $password);


        //register the user
        $query = db()->query("INSERT INTO `users`(
        `username`,`email_address`,`password`,`first_name`,`last_name`,`gender`,`country`
        ,`role`,`active`,`activated`,avatar,social_email
        )VALUES(
            '{$username}','{$email_address}','{$password}','{$first_name}','{$last_name}','{$gender}','{$country}'
            ,'{$role}','{$active}','{$activated}','{$avatar}','{$social_email}'
        )");
        if ($query) {
            $userid = db()->insert_id;
            //lets see the auto follow users
            $users = config('auto-follow-users', '');
            if ($users) {
                $users = explode(',', $users);
                foreach($users as $uid) {
                    $theUser = find_user($uid, false);
                    if ($theUser) {
                        process_follow('follow', $theUser['id'], true, $userid);
                    }
                }
            }

            if ($avatar) {
                $uploader = new Uploader($avatar, 'image', false, true, true);
                if ($uploader->passed()) {
                    $uploader->setPath($userid.'/'.date('Y').'/photos/profile/');
                    $avatar = $uploader->resize()->toDB("profile-avatar", $userid, 1)->result();
                    db()->query("UPDATE users SET avatar='{$avatar}' WHERE id='{$userid}'");
                }
            }

            session_put("sv_loggin_username", $userid);
            session_put("sv_loggin_password", $password);
            fire_hook("user.signup", array($userid, $username, $email_address));


            if ($redirect) return redirect(go_to_user_home());
        } {
            //exit(db()->error);
        }
    }

    return false;
}

function get_facebook() {
    require_once(path('includes/libraries/facebook/facebook.php'));
    $facebook = new Facebook(array(
        'appId' => config('facebook-app-id'),
        'secret' => config('facebook-secret-key')
    ));

    return $facebook;
}

function getTwitter($authToken = null, $tokenSecret = null) {
    require_once(path('includes/libraries/twitter/twitteroauth.php'));
    $twitter = new TwitterOAuth(config('twitter-app-id'), config('twitter-secret-key'), $authToken, $tokenSecret);

    return $twitter;
}


function getVK($token = null) {
    require_once(path('includes/libraries/vk/VK.php'));
    require_once(path('includes/libraries/vk/VKException.php'));
    $vk = new VK(config('vk-app-id'), config('vk-secret-key'), $token);

    return $vk;
}

function getGoogle() {
    require_once(path('includes/libraries/Google/autoload.php'));
    $client = new Google_Client();
    $client->setClientId(config('google-oauth-client-id'));
    $client->setClientSecret(config('google-oauth-client-secret'));
    $client->setRedirectUri(url_to_pager('googleplus-auth'));
    $client->addScope('https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile');

    return $client;
}

function social_add_imports($emails, $type) {
    $userid = get_userid();
    $sql = "INSERT INTO social_imports (type,name,email,avatar,user_id)VALUES";
    $inserts = '';
    foreach($emails as $em) {
        /**
         * @var $name
         * @var $email
         * @var $avatar
         */

        extract($em);
        if (!social_import_exists($type, $email)) {
            $name = mysqli_real_escape_string(db(), $name);
            $email =  mysqli_real_escape_string(db(), $email);
            $avatar = mysqli_real_escape_string(db(), $avatar);
            $inserts .= ($inserts) ? ",('{$type}','{$name}','{$email}','{$avatar}','{$userid}')" : "('{$type}','{$name}','{$email}','{$avatar}','{$userid}')";
        }
    }
    $sql .= $inserts.';';
    db()->query($sql);
    //exit(db()->error);
    return true;
}

function social_import_exists($type, $email) {
    $userid = get_userid();
    $q = db()->query("SELECT id FROM social_imports WHERE type='{$type}' AND email='{$email}' AND user_id='{$userid}'");
    return $q->num_rows;
}