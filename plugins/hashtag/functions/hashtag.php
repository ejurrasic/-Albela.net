<?php
function hashtag_parse($string) {
    $hashtags= FALSE;
    preg_match_all("/(#\w+)/u", $string, $matches);
    if ($matches) {
        $hashtagsArray = array_count_values($matches[0]);
        $hashtags = array_keys($hashtagsArray);
    }
    return $hashtags;
}

function add_hashtag($hashtag) {
    if (!hashtag_exists($hashtag)) {
        $hashtag = sanitizeText($hashtag);
        db()->query("INSERT INTO hashtags(hashtag)VALUES('{$hashtag}')");
    } else {
        db()->query("UPDATE hashtags SET count = count + 1 WHERE hashtag='{$hashtag}'");
    }
}

function hashtag_exists($hashtag) {
    $query = db()->query("SELECT id FROM hashtags WHERE hashtag='{$hashtag}'");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

function get_top_hashtags($limit) {
    $query = db()->query("SELECT hashtag FROM hashtags ORDER BY count desc LIMIT {$limit}");
    if ($query) return fetch_all($query);
    return false;
}

function search_hashtags($term, $limit = 5) {
    $query = db()->query("SELECT hashtag FROM hashtags WHERE hashtag LIKE '%{$term}%' ORDER BY count desc LIMIT {$limit}");
    if ($query) return fetch_all($query);
    return false;
}