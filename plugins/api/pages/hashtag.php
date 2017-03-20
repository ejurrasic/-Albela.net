<?php
function find_hashtags_pager($app) {
    $userid = input('userid');
    $limit = input("limit", 10);
    $offset = input("offset", 0);
    $type = input("type", "feed");
    $typeId = input("hashtag", "");
    $result = array(
        'hashtags' => array(),
        'feeds' => array()
    );
    header("Cache-Control: public");
    $tops = get_top_hashtags(5);
    foreach($tops as $top) {
        $result['hashtags'][] = array('tag' => $top['hashtag']);
    }
    $feeds = get_feeds("hashtag", $typeId, $limit, $offset);
    foreach($feeds as $feed) {

        $result['feeds'][] = api_arrange_feed($feed);
    }
    array_walk_recursive($result, function (&$value, $key) {
        if(is_string($value)) {
            $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;
            $value = preg_replace($regex, '$1', $value);
        }
    });
    return json_encode($result);
}