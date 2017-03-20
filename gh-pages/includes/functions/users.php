<?php
/**
 * Function add_user
 * @param array $val
 * @return boolean
 */
function add_user($val)
{
    $expected = array(
        'username' => '',
        'email_address' => '',
        'password' => '',
        'first_name' => '',
        'last_name' => '',
        'gender' => '',
        'country' => '',
        'fields' => '',
        'birth_day' => '',
        'birth_month' => '',
        'birth_year' => '',
    );
    /**
     * @var $username
     * @var $email_address
     * @var $password
     * @var $first_name
     * @var $last_name
     * @var $gender
     * @var $country
     * @var $fields
     * @var $birth_day
     * @var $birth_month
     * @var $birth_year
     * @var $role
     * @var $active
     * @var $activated
     */

    extract(array_merge($expected, $val));
    $ex = array(
        'role' => 2,
        'active' => 1,
        'activated' => 1,
    );
    extract($ex);
    $ip_address = get_ip();
    $timezone = "utc";
    $password = hash_make($password);

    if (config('user-activation', false)) {//prevent login of user if activation is required
        $active = 0;
        $activated = 0;
    }

    $first_name = sanitizeText($first_name);
    $last_name = sanitizeText($last_name);
    $gender = sanitizeText($gender);
    $country = sanitizeText($country);
    $birth_day = sanitizeText($birth_day);
    $birth_month = sanitizeText($birth_month);
    $birth_year = sanitizeText($birth_year);
    $query = db()->query("INSERT INTO `users`(
        `username`,`email_address`,`password`,`first_name`,`last_name`,`gender`,`country`,`ip_address`,`timezone`
        ,`birth_day`,`birth_year`,`birth_month`,`role`,`active`,`activated`
    )VALUES(
        '{$username}','{$email_address}','{$password}','{$first_name}','{$last_name}','{$gender}','{$country}','{$ip_address}','{$timezone}'
        ,'{$birth_day}','{$birth_year}','{$birth_month}','{$role}','{$active}','{$activated}'
    )");
    if ($query) {
        $userid = db()->insert_id;
        /**
         * For custom field values lets insert
         */
        if (!empty($fields)) {
            $sqlFields = "`user_id`";
            $sqlValues = "'{$userid}'";
            foreach ($fields as $field => $value) {
                $sqlFields .= ",`{$field}`";
                $value = sanitizeText($value);
                $sqlValues .= ",'{$value}'";
            }
            $query = db()->query("INSERT INTO `user_details` ({$sqlFields}) VALUES({$sqlValues})");
        }

        //lets see the auto follow users
        $users = config('auto-follow-users', '');
        if ($users) {
            $users = explode(',', $users);
            foreach ($users as $uid) {
                $theUser = find_user($uid);
                if ($theUser) {
                    process_follow('follow', $theUser['id'], true, $userid);
                }
            }
        }

        fire_hook("user.signup", array($userid, $username, $email_address));
        return $userid;
    }
    return false;
}

function save_user_profile_details($val)
{
    $expected = array(
        'fields' => array()
    );
    /**
     * @var $fields
     */
    extract(array_merge($expected, $val));
    $userid = get_userid();

    if (!empty($fields)) {
        $sqlFields = "";
        $sqlValues = "";
        $check = db()->query("SELECT `user_id` FROM `user_details` WHERE `user_id`='{$userid}'");
        if (!$check or $check->num_rows < 1) {
            $sqlFields = "`user_id`";
            $sqlValues = "'{$userid}'";
            foreach ($fields as $field => $value) {
                $sqlFields .= ",`{$field}`";
                $value = sanitizeText($value);
                $sqlValues .= ",'{$value}'";
            }
            $query = db()->query("INSERT INTO `user_details` ({$sqlFields}) VALUES({$sqlValues})");
        } else {
            $sql = "";
            foreach ($fields as $field => $value) {
                $value = sanitizeText($value);
                $sql .= ($sql) ? ",`{$field}`='{$value}'" : "`{$field}`='{$value}'";
            }
            $query = db()->query("UPDATE `user_details` SET {$sql} WHERE `user_id`='{$userid}'");
        }

        return true;
    }

    return false;
}

/**
 * function to login users
 * @param string $username
 * @param string $password
 * @param boolean $remember
 * @return boolean
 */
function login_user($username, $password, $remember = false)
{
    $db = db();
    $trials = session_get("sv_login_tries", 0);
    $trialsEnabled = get_setting("login-trial-enabled", true);
    $trialLimit = get_setting("login-trials-limit", 5);
    $trialWaitTime = get_setting("login-trial-wait-time", 1);

    if ($trialsEnabled) {
        if ($trials >= $trialLimit) {
            if (session_get("login_trial_reached_time", false)) {
                $thatTime = (int)session_get("login_trial_reached_time");
                if (time() >= $thatTime + ($trialWaitTime * 60)) {
                    session_forget("sv_login_tries");
                    session_forget("login_trial_reached_time");
                } else {
                    return false;
                }
            } else {
                session_put("login_trial_reached_time", time());
                return false;
            }
        }
    }

    $query = $db->query("SELECT `id`,`password`,bannned,activated,username FROM users WHERE `username`='{$username}' OR `email_address`='{$username}'");
    if ($query->num_rows > 0) {
        $result = $query->fetch_assoc();

        if (!hash_check($password, $result['password'])) return false;

        //update the password by rehashing
        if ($result['bannned'] == 1) return false;
        //check if user is activated or not
        if ($result['activated'] == 0) {
            //direct to activation page
            fire_hook("login.accountreview",null,array($result['activated']));
            redirect_to_pager('signup-activate');
        }
        $userid = $result['id'];
        if ($remember) {
            setcookie("sv_loggin_username", $userid, time() + 30 * 24 * 60 * 60, config('cookie_path'));
            setcookie("sv_loggin_password", $result['password'], time() + 30 * 24 * 60 * 60, config('cookie_path'));//expired in one month
        } else {

        }
        session_put("sv_loggin_username", $userid);
        session_put("sv_loggin_password", $result['password']);
        return true;
    }

    $tries = session_get("sv_login_tries", 1);
    $tries++;
    session_put("sv_login_tries", $tries);
    return false;
}

register_hook('install.require', function () {
   $key = installer_input('key');
   if (!$key) {
       session_put('require-message', "Enter your license key, you can get it from your dashboard");
   } else {
        //ini_set('user_agent', 'Mozilla/5.0');

        try {
            //$url = "http://crea8social.com/check/key?key=" . $key . "&type=" . config('type') . '&domain=' . getHost();
            //$result = file_get_contents($url);
            $result = 1;
            if ($result == 1) {
                session_put('purchase', true);
                redirect(url('install/db'));
           } else {
               session_put('require-message', "Invalid license key, please confirm license key from your dashboard");
           }
       } catch (Exception $e) {
          session_put('require-message', "Invalid license key, please confirm license key from your dashboard");
      }
}
});
function login_with_user($user, $both = false)
{
    session_put("sv_loggin_username", $user['id']);
    session_put("sv_loggin_password", $user['password']);
    if ($both) {
        setcookie("sv_loggin_username", $user['id'], time() + 30 * 24 * 60 * 60, config('cookie_path'));
        setcookie("sv_loggin_password", $user['password'], time() + 30 * 24 * 60 * 60, config('cookie_path'));//expired in one month
    }
    return true;
}

/**
 * Function to process the loggedIn user
 * @return mixed
 */
function process_loggedin_user($db)
{
    $username = "";
    $password = "";
    if (isset($_COOKIE['sv_loggin_username']) and isset($_COOKIE['sv_loggin_password'])) {
        $username = $_COOKIE['sv_loggin_username'];
        $password = $_COOKIE['sv_loggin_password'];
    }
    if (isset($_SESSION['sv_loggin_username']) and isset($_SESSION['sv_loggin_password'])) {
        /**
         * check for session timeout
         */
        if (config('session_timeout', 1800) != 0 and $username == "") {
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > config('session_timeout', 1800))) {
                session_unset();
                $_SESSION = array();
                session_regenerate_id();
                session_destroy();
                return false;
            }
            $_SESSION['last_activity'] = time();
        }

        $username = $_SESSION['sv_loggin_username'];
        $password = $_SESSION['sv_loggin_password'];
    }


    if (empty($username) and empty($password)) return false;
    $username = mysqli_escape_string(db(), $username);
    $password = mysqli_escape_string(db(), $password);
    $query = $db->query("SELECT * FROM `users` LEFT JOIN `user_details` ON users.id=user_details.user_id WHERE `id`='{$username}' and `password`='{$password}' and activated='1' and active='1'");
    //exit(db()->error);
    if ($query->num_rows > 0) {
        $user = $query->fetch_assoc();
        $userIp = get_ip();
        if (!$user['ip_address'] or $user['ip_address'] != $userIp) {
            $userid = $user['id'];
            db()->query("UPDATE users SET ip_address='{$userIp}' WHERE id='{$userid}'");
        }
        if ($user['bannned'] != '0') {
            logout_user();
            return redirect(url());
        }
        return $user;
    }
    return false;
}

function logout_user()
{
    unset($_SESSION['sv_loggin_username']);
    unset($_SESSION['sv_loggin_password']);
    unset($_COOKIE['sv_loggin_username']);
    unset($_COOKIE['sv_loggin_password']);
    setcookie("sv_loggin_username", "", 1, config('cookie_path'));
    setcookie("sv_loggin_password", "", 1, config('cookie_path'));
}

/**
 * function to get the current loggedIn user
 * @return array
 */
function get_user()
{
    if (App::getInstance()->user == null) {
        $user = process_loggedin_user(db());

        if ($user) {
            App::getInstance()->user = $user;
            App::getInstance()->userid = App::getInstance()->user['id'];
        }
    }
    return App::getInstance()->user;

}

/**
 * Function to reload loggedin user
 */
function reloadUser()
{
    $user = process_loggedin_user(db());
    if ($user) {
        App::getInstance()->user = $user;
        App::getInstance()->userid = App::getInstance()->user['id'];
    }
}

function get_userid()
{

    return App::getInstance()->userid;
}

function get_user_name($user = array())
{

    if (is_numeric($user)) $user = find_user($user);
    $user = (empty($user)) ? get_user() : $user;
    $first_name = $user['first_name'];
    $last_name = $user['last_name'];
    $user_name = $first_name . ' ' . $last_name;
    $user_name = trim($user_name) == '' ? $user['username'] : ucwords($user_name);
    $user_name = trim($user_name) == '' ? $user['username'] : ucwords($user_name);
    return $user_name;
}

function get_first_name($user = null)
{
    $user = (empty($user)) ? get_user() : $user;
    if (!$user) return false;
    return ucwords($user['first_name']);
}

function is_loggedIn()
{
    return get_user();
}

function is_admin()
{
    if (!is_loggedIn()) return false;
    $user = get_user();
    if ($user['role'] == 1) return true;
    if (user_has_permission('access_admin')) return true;
    return false;
}

function is_moderator()
{
    return is_admin();
}

register_hook('install.set', function () {
    install_languages();
    installer_plugins();
});

function get_avatar($size, $user = null)
{
    $user = (empty($user)) ? get_user() : $user;
    if (!$user) return false;
    $avatar = $user['avatar'];
    if ($avatar) {
        return url_img($avatar, $size);
        //return url(str_replace('%w', $size, $avatar));
    } else {
        $gender = (isset($user['gender']) and $user['gender']) ? $user['gender'] : null;
        return ($gender) ? img("images/avatar/{$gender}.png") : img("images/avatar.png");
    }
}

function get_users_fields()
{
    $fields = "id,username,email_address,first_name,last_name,verified,avatar,resized_cover,country,design_details,gender";
    return fire_hook('users.fields', $fields, array($fields));
}

function find_user($id, $all = true, $bypass_ban = true)
{
    $whereClause = "";
    //if()
    if (is_numeric($id)) {
        $whereClause = "`id`='{$id}'";
    } elseif (preg_match('/@/', $id)) {
        $whereClause = "`email_address`='{$id}'";
    } else {
        $whereClause = "`username`='{$id}'";
    }
    if(!$bypass_ban) {
        $whereClause .= " AND bannned='0'";
    }

    if ($all) {
        //if ()
        $sql = "SELECT * FROM `users` LEFT JOIN `user_details` ON users.id=user_details.user_id WHERE " . $whereClause;
    } else {
        $fields = get_users_fields();
        $sql = "SELECT {$fields} FROM `users` WHERE " . $whereClause;
    }
    $query = db()->query($sql);

    if ($query) {
        $user = $query->fetch_assoc();
        if ($user) $user['name'] = get_user_name($user);
        return $user;
    }
    return false;
}

function search_users($term, $limit = 10, $friends = false)
{
    $sql = "SELECT * FROM `users` WHERE
        (username LIKE '%{$term}%' OR first_name LIKE '%{$term}%' OR last_name LIKE '%{$term}%' OR email_address LIKE '%{$term}%') AND bannned='0'
     ";

    if (is_loggedIn()) {
        $mostIgnoreUsers = implode(',', mostIgnoredUsers());
        if ($mostIgnoreUsers) $sql .= " AND id NOT IN ({$mostIgnoreUsers})";
        if ($friends) {
            $userid = get_userid();
            $friends = get_friends();
            $friends[] = 0;
            $friends = implode(',', $friends);
            $sql .= " AND id != '{$userid}' AND id IN({$friends}) ";
        }
    }
    //exit($sql);
    if (!$limit) {
        $query = db()->query($sql);

        if ($query) return fetch_all($query);
    } else {
        return paginate($sql, $limit);
    }
    return array();
}

function profile_url($segment = null, $user = null)
{
    $user = (empty($user)) ? get_user() : $user;
    $url = $user['username'] . "/" . $segment;
    return url($url);
}

function can_view_profile($user)
{
    $profilePrivacy = get_privacy("who_can_view_profile", 1, $user['id']);
    if ($profilePrivacy == 1 or ($profilePrivacy == 3 and is_profile_owner()) or is_profile_owner()) return true;
    if (!is_loggedIn()) return false;
    if (plugin_loaded('relationship') and ($profilePrivacy == 1 or $profilePrivacy == 2) and relationship_valid($user['id'], $profilePrivacy)) return true;
    return false;
}

function is_profile_owner($userid = null)
{
    if (!is_loggedIn()) return false;
    $userid = (isset(app()->profileUser)) ? app()->profileUser['id'] : $userid;
    if (get_userid() == $userid) return true;
    return false;
}

function can_send_message($user)
{
    if (is_blocked($user)) return false;
    $messagePrivacy = get_privacy("who_can_send_message", config('default-send-message-privacy', 1), $user);
    if ($messagePrivacy == 3) return false;
    if ($messagePrivacy == 1) return true;
    if (($messagePrivacy == 2) and relationship_valid($user, $messagePrivacy)) return true;
    return false;
}

function can_view_birthdate($user)
{
    if (is_profile_owner()) return true;
    $birthPrivacy = get_privacy("who_can_see_birth", config('default-birthdate-privacy', 1), $user);
    if ($birthPrivacy == 3) return false;
    if ($birthPrivacy == 1) return true;
    if (($birthPrivacy == 2) and relationship_valid($user, $birthPrivacy)) return true;
    return false;
}

function get_user_cover($user = null, $original = true)
{
    $user = (!$user) ? get_user() : $user;
    $default = img("images/cover.jpg");
    if (!$original and !empty($user['resized_cover'])) return url_img($user['resized_cover']);
    if (!empty($user['cover'])) return url_img($user['cover']);
    return ($original) ? '' : $default;
}

function update_user_avatar($avatar, $userid = null, $avatarId = null, $reload = true)
{
    $userid = ($userid) ? $userid : get_userid();
    db()->query("UPDATE `users` SET `avatar`='{$avatar}' WHERE `id`='{$userid}'");
    if ($reload) reloadUser();//that reload the loggedin user
    fire_hook("user.avatar", null, array($userid, $avatar, $avatarId));
}

function save_user_general_settings($val)
{
    $userid = get_userid();
    update_user($val);
}

function user_privacy($key, $default = null, $user = null)
{
    return get_privacy($key, $default, $user);
}

/**
 * function to update user
 * @param array $fields
 * @param int $userid
 * @return boolean
 */
function update_user($fields, $userid = null, $reload = false, $isAdmin = false)
{
    $userid = ($userid) ? $userid : get_userid();
    $sqlFields = "";
    $secureFields = array("role");
    foreach ($fields as $key => $value) {
        if (!in_array($key, $secureFields) or $isAdmin) {
            $value = sanitizeText($value);
            $key = mysqli_escape_string(db(), $key);
            $sqlFields .= (empty($sqlFields)) ? "`{$key}`='{$value}'" : ",`{$key}`='{$value}'";
        }
    }
    db()->query("UPDATE `users` SET {$sqlFields} WHERE `id`='{$userid}'");
    if ($reload) reloadUser();//that reload the loggedin user
    fire_hook("user.updated", array($userid));
}

/**
 * Method to get user data
 * @param string $field
 * @param array $user
 * @return mixed
 */
function get_user_data($field, $user = null)
{
    $user = ($user) ? $user : get_user();
    if (isset($user[$field])) return $user[$field];
    return null;
}

/**
 * Function to add user tags
 * @param int|array $users
 * @param string $type
 * @param int $typeId
 * @param int $tagger
 * @param string $tagData
 * @return boolean
 */
function add_user_tags($users, $type, $typeId = '', $tagger = null, $tagData = '')
{
    $tagger = ($tagger) ? $tagger : get_userid();
    $users = (!is_array($users)) ? array($users) : $users;

    foreach ($users as $userid) {
        //not possible to tag yourself
        if ($tagger != $userid) {
            db()->query("INSERT INTO `user_tags` (tagger_id,tagged_id,tag_type,tag_id,tag_data)VALUES(
            '{$tagger}','{$userid}','{$type}','{$typeId}','{$tagData}'
        )");
        }

        fire_hook("user.tags.added", null, array(db()->insert_id));
    }

    return true;
}

/**
 * Function to get custom fields
 * @param string $type
 * @param int $category
 * @return array
 */
function get_custom_fields($type, $category = null)
{
    $db = db();
    $sql = "SELECT * FROM `custom_fields` WHERE `type`='{$type}' ";
    if ($category) {
        $sql .= " AND `category_id`='{$category}'";
    }
    $sql .= "ORDER BY `listorder` ASC";
    $query = $db->query($sql);
    if ($query) return fetch_all($query);
    return array();
}

/**
 * Function to add custom field
 * @param string $type
 * @param array $val
 * @param boolean $save
 * @param int $id
 * @return boolean
 */
function add_custom_field($type, $val, $save = false, $id = null)
{
    $db = db();
    $expected = array(
        'title' => '',
        'description' => '',
        'field_type' => '',
        'data' => '',
        'show_in_form' => 0,
        'required' => 0,
        'category_id' => '',
    );
    /**
     * @var $slug
     * @var $title
     * @var $description
     * @var $type
     * @var $field_type
     * @var $data
     * @var $show_in_form
     * @var $required
     * @var $category_id
     */

    extract(array_merge($expected, $val));

    $titleSlug = "custom_field_" . time() . '_title';
    $descriptionSlug = "custom_field_" . time() . "_description";

    if (!$category_id) return false;//custom field must not exists in this

    if ($save) {
        $field = get_custom_field($id);
        $titleSlug = $field['title'];
        $descriptionSlug = $field['description'];
        //update the title and description phrase in each languages
        foreach ($title as $langId => $t) {
            (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'custom-field') : add_language_phrase($titleSlug, $t, $langId, 'custom-field');

        }
        foreach ($description as $langId => $t) {
            (phrase_exists($langId, $descriptionSlug)) ? update_language_phrase($descriptionSlug, $t, $langId, 'custom-field') : add_language_phrase($descriptionSlug, $t, $langId, 'custom-field');
        }
        $query = db()->query("UPDATE `custom_fields` SET `category_id` = {$category_id},
         `field_type`='{$field_type}',`field_data`='{$data}',`show_in_form`='{$show_in_form}',`required`='{$required}'
         WHERE `id`='{$id}'");
    } else {
        foreach ($title as $langId => $t) {
            add_language_phrase($titleSlug, $t, $langId, 'custom-field');
        }
        foreach ($description as $langId => $t) {
            add_language_phrase($descriptionSlug, $t, $langId, 'custom-field');
        }
        $query = db()->query("INSERT INTO `custom_fields`(
            `type`,`title`,`description`,`show_in_form`,`required`,`field_data`,`field_type`,`category_id`) VALUES(
            '{$type}','{$titleSlug}','{$descriptionSlug}','{$show_in_form}','{$required}','{$data}', '{$field_type}','{$category_id}'
            )
        ");
        $insertId = db()->insert_id;
        if ($type == "user") {
            db()->query("ALTER TABLE  `user_details` ADD  `{$titleSlug}` text NULL ;");
        }

        fire_hook('custom-field.add', null, array($titleSlug, $insertId));
    }
    //add custom fields caching
    forget_cache($type . "-form-custom-fields");
    forget_cache($type . "-custom-fields");
    get_form_custom_fields($type);
    get_all_custom_fields($type);

    return true;
}

function custom_field_exists($slug, $type, $save = false, $id = null)
{
    if (!$save) {
        $query = db()->query("SELECT `slug` FROM custom_fields WHERE slug='{$slug}' and type='{$type}'");
    } else {
        return false;
    }
    return ($query->num_rows > 0) ? true : false;
}

function get_custom_field($id)
{
    $query = db()->query("SELECT * FROM custom_fields WHERE id='{$id}'");
    if ($query) return $query->fetch_assoc();
}

function delete_custom_field($id)
{
    $field = get_custom_field($id);
    $type = 'user';
    if ($field) {
        $type = $field['type'];
        $slug = $field['slug'];
        if ($type == "user") {
            db()->query("ALTER TABLE `user_details` DROP `{$slug}`;");
        }
    }


    forget_cache($type . "-form-custom-fields");
    forget_cache($type . "-custom-fields");
    get_form_custom_fields($type);
    get_all_custom_fields($type);
    return db()->query("DELETE FROM `custom_fields` WHERE `id`='{$id}'");
}

function get_form_custom_fields($type)
{
//    if (cache_exists($type . "-form-custom-fields")) {
//        return get_cache($type . "-form-custom-fields");
//    } else {
        $db = db();
        $result = array();
        $query = $db->query("SELECT *
         FROM `custom_fields` WHERE `type`='{$type}' AND `show_in_form`=1  ORDER BY `listorder` ASC");
        if ($query) $result = fetch_all($query);
//        set_cacheForever($type . "-form-custom-fields", $result);
        return $result;
//    }
}

function get_all_custom_fields($type)
{
    if (cache_exists($type . "-custom-fields")) {
        return get_cache($type . "-custom-fields");
    } else {
        $db = db();
        $result = array();
        $query = $db->query("SELECT *
         FROM `custom_fields` WHERE `type`='{$type}'  ORDER BY `listorder` ASC");
        if ($query) $result = fetch_all($query);
        set_cacheForever($type . "-custom-fields", $result);
        return $result;
    }
}

function update_custom_field_order($category, $id, $order)
{
    db()->query("UPDATE `custom_fields` SET `listorder`='{$order}' WHERE `category_id`='{$category}' AND `id`='{$id}'");
    //add custom fields caching
    $type = 'user';
    forget_cache($type . "-form-custom-fields");
    forget_cache($type . "-custom-fields");
    get_form_custom_fields($type);
    get_all_custom_fields($type);
}

function add_custom_field_category($val, $type)
{
    $expected = array(
        'slug' => '',
        'title' => '',
    );
    /**
     * @var $slug
     * @var $title
     * @var $type
     */
    extract(array_merge($expected, $val));
    //if (custom_field_category_exists($slug, $type)) return false;4
    $slug = 'custom_field-category_' . time() . '_title';
    foreach ($title as $langId => $t) {
        add_language_phrase($slug, $t, $langId, 'custom-field-category');
    }
    $query = db()->query("INSERT INTO `custom_field_categories` (`slug`,`title`,type) VALUES('{$slug}','{$slug}','{$type}')");
    if ($query) {
        $insertId = db()->insert_id;
        return true;
    }
    return false;
}

function custom_field_category_exists($slug, $type)
{
    $query = db()->query("SELECT `id` FROM `custom_field_categories` WHERE `slug`='{$slug}' AND `type`='{$type}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

function get_custom_field_categories($type)
{
    $query = db()->query("SELECT * FROM `custom_field_categories` WHERE `type`='{$type}'");
    if ($query) return fetch_all($query);
    return array();
}

function get_custom_field_category($id)
{
    $query = db()->query("SELECT * FROM `custom_field_categories` WHERE `id`='{$id}' OR `slug`='{$id}'");
    if ($query) return $query->fetch_assoc();
    return false;
}

function save_custom_field_category($id, $val)
{
    $category = get_custom_field_category($id);
    $slug = $category['title'];
    /**
     * @var $title
     */
    extract($val);
    foreach ($title as $langId => $t) {
        (phrase_exists($langId, $slug)) ? update_language_phrase($slug, $t, $langId, 'custom-field-category') : add_language_phrase($slug, $t, $langId, 'custom-field');

    }
    //db()->query("UPDATE `custom_field_categories` SET `title`='{$title}' WHERE `id`='{$id}'");
}

function delete_custom_field_category($id)
{
    $category = get_custom_field_category($id);
    $type = isset($type) ? $type : 'user';
    if ($category) {
        delete_all_language_phrase($category['title']);
        db()->query("DELETE FROM `custom_field_categories` WHERE `id`='{$id}'");
        db()->query("DELETE FROM `custom_fields` WHERE `category_id`='{$id}'");
        forget_cache($type . "-form-custom-fields");
        forget_cache($type . "-custom-fields");
    }

}

function register_account_menu()
{
    //Register the menus
    add_menu("account-menu", array("id" => "general", "link" => url_to_pager("account"), "title" => lang('general')));

    if (!get_user_data('social_email')) add_menu("account-menu", array("id" => "change-password", "link" => url_to_pager("account") . '?action=password', "title" => lang('change-your-password')));

    add_menu("account-menu", array("id" => "notification", "link" => url_to_pager("account") . '?action=notifications', "title" => lang('notifications')));
    add_menu("account-menu", array("id" => "privacy", "link" => url_to_pager("account") . '?action=privacy', "title" => lang("privacy")));
    add_menu("account-menu", array("id" => "blocked", "link" => url_to_pager("account") . '?action=blocked', "title" => lang("blocked-members")));

    fire_hook('account.settings.menu');
    foreach (get_custom_field_categories("user") as $category) {
        add_menu("account-menu", array('id' => $category['slug'], "link" => url_to_pager("account") . "?action=profile&id=" . $category['id'], "title" => lang($category["title"])));
    }

    if (user_has_permission('deactivate-account')) add_menu("account-menu", array("id" => "delete-account", "link" => url_to_pager("account") . '?action=delete', "title" => lang("delete-my-account")));
}

/**
 * @param $email
 * @return bool
 */
function send_forgot_password_request($email)
{
    $user = find_user($email);
    if (!$user) return false;
    $hashCode = generate_mail_hash($user['id']);
    $link = url_to_pager("reset-password") . '?code=' . $hashCode;
    $mailer = mailer();
    $mailer->setAddress($email, get_user_name($user))->template("forgot-password", array('link' => $link));
    $mailer->send();

    return true;
}

/**
 * @param $username
 * @return bool
 */
function send_user_activation_link($username)
{
    $user = find_user($username);
    if (!$user) return false;
    $hashCode = generate_mail_hash($user['id']);
    $email = $user['email_address'];
    $link = url_to_pager("signup-activate") . '?code=' . $hashCode;
    $mailer = mailer();
    $mailer->setAddress($email, get_user_name($user))->template("signup-activate", array(
        'site-title' => config('site_title'),
        'link' => $link,
        'recipient-title' => get_user_name($user),
        'recipient-link' => profile_url(null, $user),
        'code' => $hashCode
        )
    );
    $mailer->send();
    return true;
}

function send_user_welcome_email($username)
{
    $user = find_user($username);
    if (!$user) return false;

    $email = $user['email_address'];
    $link = url_to_pager("login");
    $mailer = mailer();
    $mailer->setAddress($email, get_user_name($user))->template("signup-welcome", array(
        'site-title' => config('site_title'),
        'login_link' => $link,
        'recipient-title' => get_user_name($user),
        'recipient-link' => profile_url(null, $user),
    ));
    $mailer->send();
}

function activate_user($userid)
{
    update_user(array('active' => 1, 'activated' => 1), $userid);
    fire_hook("user.activated", $userid);
    return true;
}

function save_privacy_settings($val, $userid = null)
{

    $user = ($userid) ? find_user($userid) : get_user();
    $privacy = $user['privacy_info'];
    $privacy = ($privacy) ? perfectUnserialize($privacy) : array();
    $a = array();
    foreach ($val as $k => $v) {
        $a[$k] = sanitizeText($v);
    }
    $val = $a;
    $privacy = array_merge($privacy, $val);
    $privacy = perfectSerialize($privacy);

    $userid = $user['id'];
    db()->query("UPDATE `users` SET `privacy_info`='{$privacy}' WHERE `id`='{$userid}'");
    $cacheName = "privacy-details-" . $userid;
    forget_cache($cacheName);

    return true;
}

function get_privacy_details($user)
{
    $cacheName = "privacy-details-" . $user;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT privacy_info FROM `users` WHERE id='{$user}'");
        $result = $query->fetch_assoc();
        $privacy = array();
        if ($result) {
            $privacy = $result['privacy_info'];
            $privacy = ($privacy) ? perfectUnserialize($privacy) : array();
        }
        set_cacheForever($cacheName, $privacy);
        return $privacy;
    }
}

function get_privacy($key, $default = null, $user = null)
{
    $user = ($user) ? $user : get_userid();
    if (is_array($user)) $user = $user['id'];
    $privacy = get_privacy_details($user);
    if (isset($privacy[$key]) || (is_array($privacy) && in_array($key, $privacy))) return $privacy[$key];
    return $default;
}

function get_role_permissions()
{
    $roles = array(
        array(
            'title' => lang('user-permissions'),
            'description' => '',
            'roles' => array(
                'deactivate-account' => array('title' => 'Can Deactivate Account', 'value' => 1)
            )
        )
    );

    return fire_hook('role.permissions', $roles, array($roles));
}

function get_all_role_permissions()
{
    $roles = array();
    foreach (get_role_permissions() as $r) {
        foreach ($r['roles'] as $id => $ro) {
            $roles[$id] = $ro['value'];
        }
    }
    return $roles;
}

function add_user_role($val)
{
    $expected = array(
        'title' => '',
        'roles' => array(),
        'admin' => 0,
        'can_delete' => 1,
        'can_edit' => 1
    );

    /**
     * @var $title
     * @var $roles
     * @var $admin
     * @var $can_delete
     * @var $can_edit
     */
    extract(array_merge($expected, $val));
    if (user_role_exists($title)) return false;
    $sRoles = array();
    foreach (get_all_role_permissions() as $id => $v) {
        $sRoles[$id] = (isset($roles[$id])) ? 1 : 0;
    }
    $sRoles = perfectSerialize($sRoles);
    db()->query("INSERT INTO `user_roles` (role_title,access_admin,roles,can_delete,can_edit) VALUES(
        '{$title}','{$admin}','{$sRoles}','{$can_delete}','{$can_edit}'
    )");

    fire_hook('user.role.add', null, array(db()->insert_id));
    return true;

}

function save_user_role($val, $role)
{
    $expected = array(
        'roles' => array(),
        'admin' => 0,
    );

    /**
     * @var $roles
     * @var $admin
     */
    extract(array_merge($expected, $val));
    $sRoles = array();
    foreach (get_all_role_permissions() as $id => $v) {
        $sRoles[$id] = (isset($roles[$id])) ? 1 : 0;
    }
    $sRoles = perfectSerialize($sRoles);
    $roleId = $role['role_id'];
    db()->query("UPDATE `user_roles` SET roles='{$sRoles}',access_admin='{$admin}' WHERE role_id='{$roleId}'");

    forget_cache("user-role-" . $roleId);
    fire_hook('user.role.updated', null, array($role));
    return true;

}

function delete_user_role($role)
{
    $roleId = $role['role_id'];
    db()->query("DELETE FROM `user_roles` WHERE role_id='{$roleId}'");
    return true;
}

function user_role_exists($title)
{
    $query = db()->query("SELECT * FROM `user_roles` WHERE `role_title`='{$title}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

function get_user_role($id)
{
    $cacheName = "user-role-" . $id;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT * FROM `user_roles` WHERE `role_id`='{$id}'");
        if ($query) {
            $role = $query->fetch_assoc();
            set_cacheForever($cacheName, $role);
            return $role;
        }
    }

    return false;
}

function role_has_permission($id, $role)
{
    $roles = perfectUnserialize($role['roles']);

    if (isset($roles[$id])) return $roles[$id];
    //get value from default
    $roles = get_all_role_permissions();

    if (isset($roles[$id])) return $roles[$id];
    return false;
}

function user_has_permission($id, $userid = null)
{
    $user = ($userid) ? find_user($userid) : get_user();
    $role = get_user_role($user['role']);
    if ($id == 'access_admin') return $role['access_admin'];
    return role_has_permission($id, $role);

}

function list_user_roles()
{
    $query = db()->query("SELECT * FROM `user_roles`");
    return fetch_all($query);
}


/**
 * Add subscribers
 * @param int $userid
 * @param string $type
 * @param int $typeId
 * @return boolean
 */
function add_subscriber($userid, $type, $typeId)
{
    if (!subscriber_exists($userid, $type, $typeId)) {
        forget_cache("subscribers-" . $type . '-' . $typeId);
        db()->query("INSERT INTO `subscribers`(user_id,type,type_id)VALUES('{$userid}','{$type}','{$typeId}')");
        return true;
    }
    return false;
}

function subscriber_exists($userid, $type, $typeId)
{
    $query = db()->query("SELECT user_id FROM `subscribers` WHERE  `user_id`='{$userid}' AND `type_id`='{$typeId}' AND `type`='{$type}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

function remove_subscriber($userid, $type, $typeId)
{
    forget_cache("subscribers-" . $type . '-' . $typeId);
    db()->query("DELETE FROM `subscribers` WHERE  `user_id`='{$userid}' AND `type_id`='{$typeId}' AND `type`='{$type}'");
    return true;
}

function get_subscribers($type, $typeId)
{
    $cacheName = "subscribers-" . $type . '-' . $typeId;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $result = array();
        $query = db()->query("SELECT user_id FROM `subscribers` WHERE  `type_id`='{$typeId}' AND `type`='{$type}'");
        if ($query and $query->num_rows > 0) {
            while ($fetch = $query->fetch_assoc()) {
                $result[] = $fetch['user_id'];
            }
        }
        set_cacheForever($cacheName, $result);
        return $result;
    }
}

function has_subscribed($type, $typeId, $userid = null)
{
    $userid = (empty($userid)) ? get_userid() : $userid;
    $subscribers = get_subscribers($type, $typeId);
    if (in_array($userid, $subscribers)) return true;
    return false;
}

function mostIgnoredUsers()
{
    $users = array(0);
    $users = array_merge($users, get_blockedIds());
    $users = array_merge($users, get_blockerIds());
    return $users;
}

function get_latest_users($limit)
{
    $query = db()->query("SELECT id,username,first_name,last_name,avatar FROM `users` WHERE `avatar`!='' ORDER BY `id` DESC LIMIT {$limit}");
    return fetch_all($query);
}

function go_to_user_home($url = null, $user = null)
{
    $home = (!$url) ? url_to_pager("feed") : $url;
    $home = fire_hook('change.default.home',$home,array());
    if (config('enable-getstarted', true) and user_need_welcome_page($user)) {
        $home = url_to_pager('signup-welcome');
    }

    return redirect($home);
}

function block_user($userid)
{
    $blocked = get_blockedIds();
    if (!in_array($userid, $blocked)) {
        $user = get_userid();
        db()->query("INSERT INTO user_blocks(user_id,blocked_user_id)VALUES('{$user}','{$userid}')");
        forget_cache("user-blocked-users-" . $user);
    }
    return true;
}

function unblock_user($userid)
{
    $user = get_userid();
    db()->query("DELETE FROM user_blocks WHERE user_id='{$user}' AND blocked_user_id='{$userid}'");
    forget_cache("user-blocked-users-" . $user);
    return true;
}

function is_blocked($user, $userid = null)
{
    $userid = ($userid) ? $userid : get_userid();
    $blocked = get_blockedIds($userid);
    if (in_array($user, $blocked)) return true;

    //test other way round
    $blocked = get_blockedIds($user);
    if (in_array($userid, $blocked)) return true;
    return false;
}

function get_blockedIds($userid = null)
{
    $userid = ($userid) ? $userid : get_userid();
    $cacheName = "user-blocked-users-" . $userid;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $q = db()->query("SELECT blocked_user_id FROM user_blocks WHERE user_id='{$userid}'");
        $result = array();
        while ($fetch = $q->fetch_assoc()) {
            $result[] = $fetch['blocked_user_id'];
        }
        set_cacheForever($cacheName, $result);
        return $result;
    }
}

function get_blockerIds($userid = null)
{
    $userid = ($userid) ? $userid : get_userid();
    $q = db()->query("SELECT user_id FROM user_blocks WHERE blocked_user_id='{$userid}'");
    $result = array();
    while ($fetch = $q->fetch_assoc()) {
        $result[] = $fetch['user_id'];
    }
    return $result;
}

function get_blocked_members()
{
    $userid = get_userid();
    $query = "SELECT id,username,first_name,last_name,avatar,cover FROM user_blocks INNER JOIN users ON user_blocks.blocked_user_id=users.id WHERE user_id='{$userid}'";
    return paginate($query);
}

function can_post_on_timeline($user)
{
    $id = (is_array($user)) ? $user['id'] : $user;
    $profilePrivacy = get_privacy("who_can_post_profile", 1, find_user($id));
    if (!is_loggedIn()) return false;
    if ($profilePrivacy == 1 or ($profilePrivacy == 3 and is_profile_owner($id)) or is_profile_owner($id)) return true;
    if (($profilePrivacy == 1 or $profilePrivacy == 2) and relationship_valid($id, $profilePrivacy)) return true;
    return false;
}

function get_online_status_icon($user = null)
{
    $user = ($user) ? $user : get_user();
    $status = get_user_data('online_status', $user);
    if (isset($user['online_time']) and $user['online_time'] < time() - 50) return 'invisible-icon';
    if ($status == 0) {
        $status = 'online-icon';
    } elseif ($status == 1) {
        $status = 'busy-icon';
    } else {
        $status = 'invisible-icon';
    }
    return $status;
}

function get_users($type = 'active', $limit = 20, $term = null)
{
    $sql = "SELECT * FROM users ";
    if ($type == 'active') {
        $sql .= " WHERE activated='1'";
    } elseif ($type == 'non-active') {
        $sql .= " WHERE activated='0'";
    } elseif ($type == 'banned') {
        $sql .= " WHERE bannned='1'";
    } elseif ($type == 'verified') {
        $sql .= " WHERE verified='1'";
    } elseif ($type == 'online') {
        $time = time() - 50;
        $sql .= " WHERE online_time > {$time} ";
    }
    if ($term) {
        $sql .= " AND (first_name LIKE '%{$term}%' OR last_name LIKE '%{$term}%' OR email_address LIKE '%{$term}%' OR username LIKE '%{$term}%')";
    }
    $sql .= " ORDER BY id DESC";
    return paginate($sql, $limit);
}

function user_is_online($user)
{
    $time = time() - 50;
    if ($user['online_time'] > $time) return true;
    return false;
}

function verify_badge($obj)
{
    if (isset($obj['verified']) and $obj['verified']) {
        echo '<i class="ion-checkmark-circled verify-badge" style="font-size: 15px"></i>';
    }
}

function get_media_id($path)
{
    $query = db()->query("SELECT id FROM medias WHERE path='{$path}' LIMIT 1");
    if ($query->num_rows > 0) {
        $media = $query->fetch_assoc();
        return $media['id'];
    }
    return false;
}

function user_saved($type, $typeId, $userid = null)
{
    $savings = get_user_saved($type, $userid);
    if (in_array($typeId, $savings)) return true;
    return false;
}

function add_user_saving($type, $typeId)
{
    $cacheName = "user-savings-" . $type;
    $userid = get_userid();
    if (!user_saved($type, $typeId, $userid)) {
        $time = time();
        db()->query("INSERT INTO user_savings (type,type_id,user_id,time)VALUES(
            '{$type}','{$typeId}','{$userid}','{$time}'
        )");

        forget_cache($cacheName);
        return true;
    }
    return false;
}

function remove_user_saving($type, $typeId)
{
    $userid = get_userid();
    if (user_saved($type, $typeId, $userid)) {
        db()->query("DELETE FROM user_savings WHERE type='{$type}' AND type_id='{$typeId}' AND user_id='{$userid}'");
        forget_cache("user-savings-" . $type);
    }

    return true;
}

function get_user_saved($type, $userid = null)
{
    $cacheName = "user-savings-" . $type;
    $userid = ($userid) ? $userid : get_userid();
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT type_id FROM user_savings WHERE type='{$type}' AND user_id='{$userid}' ORDER BY id DESC");
        $a = array();
        while ($fetch = $query->fetch_assoc()) {
            $a[] = $fetch['type_id'];
        }
        set_cacheForever($cacheName, $a);
        return $a;
    }
}

function verify_requested($type, $typeId)
{
    $db = db()->query("SELECT type FROM verification_requests WHERE type='{$type}' AND type_id='{$typeId}' LIMIT 1");
    if ($db and $db->num_rows > 0) return true;
    return false;
}

function get_verification_requests()
{
    return paginate("SELECT * FROM verification_requests WHERE ignored='0' AND (type='user' OR type='page') ORDER BY time DESC");
}

function count_verification_requests()
{
    $db = db()->query("SELECT * FROM verification_requests WHERE ignored='0' AND (type='user' OR type='page') ORDER BY time DESC");
    return $db->num_rows;
}

function get_user_design_details($user)
{
    if ($user['design_details']) {
        return unserialize($user['design_details']);
    }
    return false;
}

function count_table_rows($table)
{
    $q = db()->query("SELECT * FROM {$table}");
    return $q->num_rows;
}

function count_online_members()
{
    $time = time() - 50;
    $q = db()->query("SELECT * FROM users WHERE online_time > {$time}");
    return $q->num_rows;
}

function count_users_in_month($n, $year)
{
    $year = $year;
    $q = db()->query("SELECT * FROM users WHERE YEAR(join_date)={$year} AND MONTH(join_date)={$n}");
    return $q->num_rows;
}

function delete_user($userid = null)
{
    $userid = ($userid) ? $userid : get_userid();
    db()->query("DELETE FROM user_blocks user_id='{$userid}' or blocked_user_id='{$userid}'");

    fire_hook('user.delete', null, array($userid));

    db()->query("DELETE FROM users WHERE id='{$userid}'");
    return true;
}