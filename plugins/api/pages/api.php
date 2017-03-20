<?php
function get_feeds_pager($app) {
    $userid = input('userid');
    $limit = input("limit", 5);
    $offset = input("offset", 0);
    $type = input("type", "feed");
    $typeId = input("type_id", "");
    $result = array();
    //header("Cache-Control: public");

    if ($type == 'single_feed') {
        $feed = find_feed($typeId);
        $dFeed = api_arrange_feed($feed);
        $result[] = $dFeed;
        return json_encode($result);
    }

    $feeds = get_feeds($type, $typeId, $limit, $offset);
    $index = 1;
    foreach($feeds as $feed) {

        $dFeed = api_arrange_feed($feed);
        if (config('enable-post-inline-ads', true) and plugin_loaded("ads")) {
            if ($index == config('render-ads-after-post-number', 2)) {
                $ads = get_render_ads('website', 1);
                foreach($ads as $ad) {
                    $dFeed['ads'][] = array(
                    'title' => $ad['title'],
                    'description' => $ad['description'],
                    'link' => $ad['link'],
                    'code' => $ad['ads_id'],
                        'image' => $ad['image']
                );
                }
            }
        }
        $result[] = $dFeed;
        $index++;
    }
    //print_r($feed);
    //exit;
    return json_encode($result);
}

function get_menu_pager($app) {
    $result = array();
    $menus = get_menus("app-mobile-menu");
    foreach($menus as $menu) {
        $result[] = array(
            'title' => lang($menu->title),
            'link' => url($menu->link)
        );
    }
    return json_encode($result);
}

function social_pager($app) {

    $details = array(
        'first_name' => input('firstname'),
        'last_name' => input('lastname'),
        'genre' => input('gender'),
        'country' => '',
        'email_address' => input('email'),
        'social_email' => input('email'),
        'password' => time(),
        'username' =>input("username"),
        'auth' => input("type"),
        'authId' => input("id"),
        'avatar' => input("image")
    );

    if (input("type") == "facebook") {
        try{
            ini_set('user_agent', 'Mozilla/5.0');
            $avatar = json_decode(file_get_contents('https://graph.facebook.com/'.input('id').'/picture?redirect=false&width=600&height=600'), true);

            if ($avatar and isset($avatar['data']['url'])) {
                $avatar = $avatar['data']['url'];
                $details['avatar'] = $avatar;
            }
        } catch(\Exception $e){}
    }

    $email_address = input("email");
    $query = db()->query("SELECT * FROM users WHERE email_address='{$email_address}'");
    if ($query->num_rows > 0) {
        //user is coming back
        $user = $query->fetch_assoc();
        //user is a member
    } else {
        social_register_user($details, false);
        $email_address = input("email");
        $query = db()->query("SELECT * FROM users WHERE email_address='{$email_address}'");
        $user = $query->fetch_assoc();
    }

    $result = array(
        'status' => "0",
        "userid" => '',
        'message' => '',
    );

    $result = api_arrange_user($user, true);
    $result2 = array(
        'id' => 6267,
        'cover' => '',
        'status' => 1,
        'name' => 'pRocrea8',
        'password' => '2fabd13bbbb6834f0bf6d2897f9175ab',
        'city' => '',
        'bio' => '',
        'state' => '',
        'avatar' => '',
        'first_name' => 'pRocrea8',
        'last_name' => ''
    );
    return json_encode($result);
}

function login_pager($app) {
    $result = array(
        'status' => "0",
        "userid" => '',
        'message' => '',
    );

    $username = input('username');
    $password = input('password');
    //$result['message'] = $username.$password;
    if ($username and $password) {
        $login = login_user($username, $password);
        if ($login) {
            $user = find_user($username);
            $result = api_arrange_user($user, true);
        }
    }

    return json_encode($result);
}

function check_login_pager($app) {
    $user = api_arrange_user(get_user(), true);
    $user['notifications'] = api_count_notifications();
    $user['friend_requests'] = count_friend_requests();
    $user['messages'] = count_unread_messages();
    return json_encode($user);
}

function set_fcm_pager($app) {
    $token = input("token");
    update_user(array('gcm_token' => $token));
    return json_encode(array("status" => 1));
}


function check_event_pager($app) {
    $user = array();
    $user['notifications'] = api_count_notifications();
    $user['friend_requests'] = count_friend_requests();
    $user['messages'] = count_unread_messages();
    return json_encode($user);
}

 function signup_user($app) {
     $result = array(
         'status' => "0",
         "userid" => '10000',
         'message' => ''
     );
     $val = array(
         'first_name' => input('firstname'),
         'last_name' => input('lastname'),
         'username'  => input('username'),
         'email_address' => input('email_address'),
         'password'  => input('password'),
         'birth_day' => input('day'),
         'birth_month' => input('month'),
         'birth_year' => input('year'),
         'gender' => strtolower(input('gender')),
         'country' => input('country')
     );


     $rules = array(
         'first_name' => 'required|predefined',
         'last_name' => 'required|predefined',
         'username'  => 'required|predefined|alphanum|min:3|username',
         'email_address' => 'required|email|unique:users',
         'password'  => 'required|min:6',
     );

     $validator = validator($val, $rules);
     if (validation_passes()) {
         $added = add_user($val);
         send_user_welcome_email($val['username']);
         $user = find_user($added);
         $result['status'] = "1";
         $result['userid'] = $added;
         $result = api_arrange_user($user, true);
         if(config('user-activation', false)) {
             $time = time();
            db()->query("UPDATE users SET active='1',activated='1', last_active_time='{$time}',online_time='{$time}' WHERE id='{$added}'");
         }
     } else {
         $result['message'] = validation_first();
     }

     return json_encode($result);
 }

function settings_pager($app) {
    $val = array(
        'first_name' => input("first_name"),
        "last_name" => input("last_name"),
        "bio" => input("bio"),
        "city" => input("city"),
        "state" => input("state"),
    );
    save_user_general_settings($val);

    //notifications
    $val = array(
        "notify-following-you" => input("notify-following-you"),
        "notify-site-mention-you" => input("notify-site-mention-you"),
        "notify-site-tag-you" => input("notify-site-tag-you"),
        "notify-site-comment" => input("notify-site-comment"),
        "notify-site-reply-comment" => input("notify-site-reply-comment"),
        "notify-site-like" => input("notify-site-like"),
        "who_can_view_profile" => input("who_can_view_profile"),
        "who_can_post_profile" => input("who_can_post_profile"),
        "who_can_see_birth" => input("who_can_see_birth"),
        "turn_on_friend_requests" => input("turn_on_friend_requests")
    );
    save_privacy_settings($val);

    return json_encode(array("status" => 1));
}

function settings_password_pager($app) {
    $result = array('status'=> 0);
    $val = array(
        'current' => input("current_password"),
        'new' => input("new_password")
    );
    /**
     * expected values
     * @var $new
     * @var $current
     * @var $confirm
     */
    extract($val);
    $currentPass = get_user_data("password");
    //$current = hash_make($current);
    if (hash_check($current, $currentPass)) {
        $password = hash_make($new);
        update_user(array('password' => $password));
        $result['status'] = 1;
        $result['data_one'] = $password;

    } else {
        $message = "Your current password does not match";
    }

    return json_encode($result);
}