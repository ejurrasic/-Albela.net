<?php
load_functions("feed::feed");

function feed_pager($app) {
    $app->setTitle(lang("news-feed"));
    $app->topMenu = lang('news-feed');
    $type = input('type', 'all');
    $sortby = input('sortby',null);
    session_put(md5($type.'all'), time());
    if (is_loggedIn()) get_menu("dashboard-main-menu", 'news-feed')->setActive(true);
    $content =  view("feed::content", array('feeds' => get_feeds('feed', $type,null,0,false,true,$sortby), 'type' => $type));
    $content = fire_hook('feed.type',$content,array($type));
    return $app->render($content);
}

function add_feed_pager($app) {
    CSRFProtection::validate(false);
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        return add_feed($val);
    } else {
        return json_encode(array(
           'status' => 0,
           'message' => lang("failed-to-upload-file")
        ));
    }
}

function save_feed_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $text = input('text');
    $save = save_feed($id, $text);
    if ($save) {
        return output_text(sanitizeText($text));
    } else {
        return '0';
    }
}

function remove_feed_pager($app) {
    CSRFProtection::validate(false);
    $removed = remove_feed(input('id'));
    if (is_ajax()) {
        if ($removed) return 1;
        return '0';
    } else {
        return redirect_back();
    }
}

function feed_more_pager($app) {
    CSRFProtection::validate(false);
    $limit = config('feed-limit', 10);
    $offset = input('offset');
    $offset = ($offset) ? $offset : $limit;
    $type = input('type');
    $typeId = input('type_id');
    $sortby = input('sortby',null);
    $newOffset = (int) $offset + $limit;
    $feeds = get_feeds($type, $typeId, $limit, $offset,false,true,$sortby);
    $content = view('feed::paginate', array('feeds' => $feeds));
    $content = fire_hook('feed.type',$content,array($type));

    return json_encode(array(
        'offset' => $newOffset,
        'feeds' => $content,
    ));
}

function check_new_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $typeId = input('typeId');
    $container = input('container');
    $feeds = get_feeds($type, $typeId, null, 0, true, $container);
    $content = '';
    if (input('container')) {
        foreach($feeds as $feed) {
            $content .= view('feed::feed', array('feed' => $feed));
        }
    }

    return json_encode(array(
        'count' => count($feeds),
        'feeds' => $content,
    ));

}

function share_feed_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $count = share_feed($id);
    return json_encode(array(
        'count' => $count,
        'message' => lang('feed::feed-success-shared')
    ));
}

function feed_notification_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $id = input('id');
    if ($type == 1) {
        add_subscriber(get_userid(), 'feed', $id);
    } else {
        remove_subscriber(get_userid(), 'feed', $id);
    }
}

function feed_page_pager($app) {
    $feed = find_feed(segment(1));
    if (!$feed) return redirect_to_pager('feed');
    if($feed['shared']) {$title = isset($feed['shared-feed']['publisher']['page_title']) ? $feed['shared-feed']['publisher']['page_title'] : $feed['shared-feed']['publisher']['name'];} else {$title = isset($feed['publisher']['page_title']) ? $feed['publisher']['page_title'] : $feed['publisher']['name'];};
    $app->setTitle($title);
    $image = null;
    if ($feed['photos']) {
        $photos = perfectUnserialize($feed['photos']);
        foreach($photos as $photId => $path) {
            $image =  url_img($path, 600);
        }
    }
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $title, 'description' => $feed['feed_content'], 'image' => $image));
    if ($feed['entity_type'] == 'user') {
        $design = get_user_design_details($feed['publisher']);
        if ($design) app()->design = $design;
    }
    return $app->render(view('feed::page', array('feed' => $feed)));
}

function update_editor_privacy_pager($app) {
    CSRFProtection::validate(false);
    save_privacy_settings(array('feed-editor-privacy' => sanitizeText(input('v'))));
}

function get_link_pager($app) {
    CSRFProtection::validate(false);
    $linkDetails = feed_process_link(perfect_url(input('link')));
    if(!$linkDetails) return false;
    return view('feed::link', array('details' => $linkDetails, 'editor' => true));
}

function update_privacy_pager($app) {
    CSRFProtection::validate(false);
    $feed = input('id');
    $privacy = input('privacy');
    feed_update_privacy($feed, $privacy);
}

function hide_feed_pager($app) {
    CSRFProtection::validate(false);
    $feed = input('id');
    hide_feed($feed);
}

function pin_feed_pager($app) {
    CSRFProtection::validate(false);
    $id = segment(2);
    $feed = find_feed($id);

    if(!can_pin_post($feed)) redirect_back();
    pin_feed($feed);
    redirect_back();
}

function unhide_feed_pager($app) {
    $feed = input('id');
    unhide_feed($feed);
}

function feed_download_pager($app) {
    $file = input('file');
    $name = input('name');

    return download_file($file, $name);
}

function search_media_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $term = input('term');

    if ($type == 'listening-to') {
        $soundcloud = 'http://api.soundcloud.com/tracks.json?client_id=e8d2797b62ce47938f3baa699a725864&limit=5&q=' . urlencode($term);

        $soundcloud = @file_get_contents($soundcloud);


        $results = json_decode($soundcloud, true);


        $a = array();
        if (is_array($results) and count($results) > 1) {
            foreach($results as $s) {

                if ($s['kind'] == 'track') {
                    $a[] = array(
                        'title' => $s['title'],
                        'description' => $s['description'],
                        'link' => $s['uri'],
                        'image' => $s['artwork_url']
                    );
                }
            }

            return view('feed::media-search', array('medias' => $a));
        }
    } else {
        $libPath = path('includes/libraries/Google/src/Google/');

        require_once $libPath.'Client.php';
        require_once $libPath.'Service/YouTube.php';

        $client = new \Google_Client();
        $client->setDeveloperKey(config('google-api-key'));

        $youtube = new \Google_Service_YouTube($client);

        $maxLimit = 5;

        try {
            $searchResult = $youtube->search->listSearch('id,snippet', array(
                'q' => $term,
                'maxResults' => $maxLimit
            ));

            $a = array();
            foreach ($searchResult['items'] as $result) {
                switch($result['id']['kind']) {
                    case 'youtube#video':
                        $l = "https://www.youtube.com/embed/".$result['id']['videoId'];
                        $a[] = array(
                            'title' => $result['snippet']['title'],
                            'link' => (String) $l,
                            'image' => $result['snippet']['thumbnails']['medium']['url']
                        );

                        break;
                }
            }

            return view('feed::media-search', array('medias' => $a));
        } catch(\Exception $e){/*var_dump($e->getMessage())*/;}
    }
}

function submit_poll_pager($app) {
    CSRFProtection::validate(false);
    $pollId = input('val.poll_id');
    $feed = find_feed($pollId);
    feed_submit_poll(input('val'), $feed);
    return view("feed::poll-result", array('feed' => find_feed($pollId)));
}

function poll_voters_pager($app) {
    CSRFProtection::validate(false);
    $id = input('answer_id');
    $limit = input('limit') ? input('limit') : 4;
    $page = input('page') ? input('page') : 1;
    $voters = get_poll_answers_user($id, $limit, $page);
    $total_pages = get_num_poll_voter_pages($id, $limit);
    return view("feed::poll-voters", array('voters' => $voters, 'answer_id' => $id, 'total_pages' => $total_pages, 'page' => $page));
}