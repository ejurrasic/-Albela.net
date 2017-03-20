<?php
function find_mentions($text) {
    $query = db()->query("SELECT id,username,first_name,last_name,avatar FROM `users` WHERE username LIKE '%{$text}%' OR first_name LIKE '%{$text}%' OR last_name LIKE '%{$text}%' LIMIT 5");
    return fetch_all($query);
}

function mention_parse($text) {
    if (empty($text)) return false;
    preg_match_all('/(^|\s)(@\w+)/', $text, $matches);

    return $matches[0];
}