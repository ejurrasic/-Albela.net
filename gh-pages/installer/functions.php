<?php

function installer_db($val) {
    /**
     * @var $host
     * @var $username
     * @var $name
     * @var $password
     */
    extract($val);

    if (empty($host) or !$username or !$name /*or !$password*/) return false;

    $configContent = file_get_contents(path('config-holder.php'));
    //replace the details
    $configContent = str_replace(array(
        '{localhost}','{root}','{dbname}','{dbpassword}','{installed}'
    ), array($host, $username, $name, $password, '0'), $configContent);
    file_put_contents(path('config.php'), $configContent);

    try{
        app()->db = new mysqli(
            $host,
            $username,
            $password,
            $name
        );
    } catch(Exception $e) {
        return false;
    }

    //now install the database things
    //app()->loadFunctionFile("users");
    db()->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
    require path("includes/database/install.php");
    require path("includes/database/upgrade.php");
    fire_hook('install.db', null, array($val));
    return true;
}

function installer_plugins() {
    $plugins = get_all_plugins();

    foreach($plugins as $key => $info) {
        if ($key != 'membership') plugin_activate($key, false);
    }
    return true;
}

function install_languages() {
    $path = path('languages/packed/');
    update_all_language_phrases(); //for english language
    if ($handle = opendir($path)) {
        while($file = readdir($handle)) {
            if (substr($file, 0, 1) != '.') {
                try{
                    $doc = new DOMDocument();
                    //$doc->validateOnParse = true;
                    $file = $path.$file;

                    $doc->loadXML(file_get_contents($file));
                    $languages = $doc->getElementsByTagName('language');
                    foreach($languages as $l) {
                        $languageName = $l->getAttribute('name');
                        $languageDir = $l->getAttribute('direction');
                        $languageId = $l->getAttribute('id');
                        $phrases = array();
                        foreach($l->getElementsByTagName('translation') as $phrase) {
                            $phrases[$phrase->getAttribute('id')] = $phrase->nodeValue;
                        }

                        //lets create the language if not exists
                        if (!language_exist($languageId)) {
                            add_language(array(
                                'id' => $languageId,
                                'title' => $languageName,
                                'dir' => $languageDir,
                                'from' => ''
                            ));
                        }
                        //ok we are good to update the phrases now

                        $sql = "INSERT INTO `language_phrases`(language_id,phrase_id,phrase_original,phrase_translation,plugin) VALUES";
                        $a = "";
                        foreach($phrases as $id => $phrase) {
                            $phraseId =mysqli_escape_string(db(), $id);
                            $trans = mysqli_escape_string(db(), $phrase);
                            $plugin = 'core';
                            $a .= ($a) ? ",('{$languageId}','{$phraseId}','{$trans}','{$trans}','{$plugin}')" : "('{$languageId}','{$phraseId}','{$trans}','{$trans}','{$plugin}')";
                            //add_language_phrase($result["phrase_id"], $result["phrase_translation"], $id, $result["plugin"]);
                        }
                        $sql .= $a;
                        db()->query($sql);

                        //clear this language phrases incase
                        forget_cache("language-".$languageId."-phrases");

                    }
                } catch(Exception $e) {
                    $message = "Language file not supported, only xml file allowed";
                    $message = $e->getMessage();
                }
            }
        }
    }
    forget_cache("languages");

}

function installer_save_info($val) {
    /**
     * @var $title
     * @var $email
     * @var $username
     * @var $password
     * @var $confirm_password
     * @var $fullname
     */
    extract($val);
    if (!$title or !$email or !$username or !$password or !$confirm_password or $password != $confirm_password or !$fullname) return false;

    $name = explode(' ', $fullname);
    $firstName = $name[0];
    $lastName = (isset($name[1])) ? $name[1] : '';
    add_user(array(
        'username' => $username,
        'email_address' => $email,
        'password' => $password,
        'gender' => 'male',
        'role' => '1',
        'first_name' => $firstName,
        'last_name' => $lastName
    ));
    db()->query("UPDATE users SET role='1' WHERE id='1'");
    save_admin_settings(array('site_title' => $title));

    $val = unserialize(session_get("database-details"));
    /**
     * @var $host
     * @var $username
     * @var $name
     * @var $password
     */
    extract($val);

    if (empty($host) or !$username or !$name ) return false;

    $configContent = file_get_contents(path('config-holder.php'));
    //replace the details
    $configContent = str_replace(array(
        '{localhost}','{root}','{dbname}','{dbpassword}','{installed}'
    ), array($host, $username, $name, $password, 'true'), $configContent);
    file_put_contents(path('config.php'), $configContent);
    return true;
}

function installer_input($name, $default = null) {
    //if (!isset($_POST[$name]) and !isset($_GET[$name])) return $default;
    $index = "";
    if (preg_match("#\.#", $name)) list($name, $index) = explode(".", $name);

    $result = (isset($_GET[$name])) ? $_GET[$name] : $default;
    $result = (isset($_POST[$name])) ? $_POST[$name] : $result;

    if (is_array($result)) {
        if (empty($index)) {
            $nR = array();
            foreach($result as $k => $v) {
                if (is_array($v)) {
                    $newResult = array();
                    foreach($v as $n => $a) {
                        $newResult[$n] = $a;
                    }
                    $nR[$k] = $newResult;
                } else {
                    $nR[$k] = $v;
                }
            }
            $result = $nR;
        } else {
            if(!isset($result[$index])) return $default;
            if (is_array($result[$index])) {
                $newResult = array();
                foreach($result[$index] as $n => $v) {
                    $newResult[$n] = $v;
                }
                $result = $newResult;
            } else {
                $result = $result[$index];
            }

        }
    } else {
        $result = $result;
    }

    return $result;
}
 