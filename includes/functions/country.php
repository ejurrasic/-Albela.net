<?php
/**
 * function to add country
 * @param string $name
 * @return boolean
 */
function add_country($name) {
    if (country_exists($name)) return false;
    db()->query("INSERT INTO `countries` (`country_name`) VALUES('{$name}')");
    fire_hook("country_added", $name, array($name));
    forget_cache("countries");
    return true;
}

function country_exists($name) {
    $query = db()->query("SELECT country_name FROM `countries` WHERE `country_name`='{$name}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}
function get_countries() {
    if (cache_exists("countries")) {
        return get_cache("countries");
    } else {
        $query = db()->query("SELECT `country_name`,`id` FROM `countries` ORDER BY `country_name` ASC ");
        if ($query) {
            $r = fetch_all($query);
            $result = array();
            foreach($r as $k) {
                $result[$k['id']] = $k['country_name'];
            }
            set_cacheForever("countries", $result);
            return $result;
        } else {
            return array();
        }
    }
}
function get_country($id) {
    $countries = get_countries();
    if (isset($countries[$id])) return $countries[$id];
    return false;
}
function save_country($id, $name) {
    db()->query("UPDATE `countries` SET `country_name`='{$name}' WHERE `id`='{$id}'");
    forget_cache("countries");
}
function delete_country($id) {
    db()->query("DELETE FROM `countries` WHERE `id`='{$id}'");
    forget_cache("countries");
}
function count_states($id) {
    return count(get_states($id));
}

function get_states($id) {
    $key = "country_".$id."_states";
    if (cache_exists($key)) {
        return get_cache($key);
    } else {
        $query = db()->query("SELECT `id`,`state_name` FROM `country_states` WHERE `country_id`='{$id}' ORDER BY `state_name`");
        if ($query) {
            $r = fetch_all($query);
            $result = array();
            foreach($r as $k) {
                $result[$k['id']] = $k['state_name'];
            }
            set_cacheForever($key, $result);
            return $result;
        } else {
            return array();
        }
    }
}
function add_state($id, $name) {
    $key = "country_".$id."_states";
    if (state_exists($id, $name)) return false;
    $query = db()->query("INSERT INTO `country_states` (country_id,state_name) VALUES('{$id}','{$name}')");
    forget_cache($key);
    fire_hook("state_added", $name, array($id, $name));
    return true;
}
function state_exists($id, $name) {
    $query = db()->query("SELECT state_name FROM `country_states` WHERE `country_id`='{$id}' AND `state_name`='{$name}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}
function get_state($id) {
    $query = db()->query("SELECT state_name,country_id FROM country_states WHERE id='{$id}'");
    if ($query) return fetch_all($query);
    return false;
}
function delete_state($id, $country) {
    db()->query("DELETE FROM `country_states` WHERE `id`='{$id}'");
    $key = "country_".$country."_states";
    forget_cache($key);
}
function save_state($id, $name, $country) {
    db()->query("UPDATE country_states SET state_name='{$name}',country_id='{$country}' WHERE id='{$id}'");
    $key = "country_".$country."_states";
    forget_cache($key);
    return true;
}

function is_valid_country($country) {
    $countries = get_countries();
    if(in_array($country, $countries)) return true;
    return false;
}