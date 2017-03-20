<?php
function add_feed_pager($app) {
    $userid  = input("userid");
    $type = input("type");
    $typeId = input("type_id");
    $entityType = input("entity_type");
    $entityId = input("entity_id");
    $location = input("location");
    $privacy = input("privacy");
    $feeling_type = input("feeling_type");
    $feeling_text = input("feeling_text");
    $to_userid = input("to_user_id");
    $text = input("text");
    $is_poll = input("is_poll");
    $tags = input("tags", array());
    if ($tags) {
        $tags = explode(",", $tags);
    }
    $pollOptions = array(
        input('poll_option_one'), input('poll_option_two'), input('poll_option_three')
    );
    $val = array(
        'type' => $type,
        'type_id' => $typeId,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'location' => $location,
        'privacy' => $privacy,
        'feeling_type' => $feeling_type,
        'feeling_text' => $feeling_text,
        'to_user_id' => $to_userid,
        "images" => "",
        'tags' => $tags,
        "video" => "",
        "files" => "",
        "content" => $text,
        "poll" => $is_poll,
        "poll_options" => $pollOptions,
        "poll_multiple" => input("poll_multiple", 0)
    );



    $result = array(
        'status' => 1,
        'feed' => array(),
        "message" => ""
    );

    if (input_file("image1") or input_file("image2") or input_file("image3") or input_file("image4") or input_file("image5")) {
        $i = 1;
        $images = array();
        while($i <= 5) {
            $image = input_file("image".$i);
            try {
                $uploader = new Uploader($image);
                $path = get_userid().'/'.date('Y').'/photos/posts/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $image = $uploader->noThumbnails()->resize()->toDB($entityType.'-posts', $entityId, $privacy)->result();

                    $images[$uploader->insertedId] = $image;

                } else {
                    //$result['status'] = 0;
                    //$result['message'] = $uploader->getError();
                    //return json_encode($result);
                }
            } catch(Exception $e) {

            }
            $i++;
        }

        if (count($images) > 0) $val['images'] = perfectSerialize($images);

    }

    $videoFile = input_file("video_attach");
    if ($videoFile and empty($val['images'])) {
        $video = "";
        if (!plugin_loaded('video') or !config('video-upload', false) or config('video-encoder') == 'none') {
            app()->config['video-file-types'] = "mp4";
        }
        $uploader = new Uploader(input_file('video_attach'), 'video');
        $path = get_userid().'/'.date('Y').'/videos/posts/';
        $uploader->setPath($path);
        if ($uploader->passed()) {
            $video = $uploader->uploadVideo()->toDB($entityType.'-posts', $entityId, $privacy)->result();
            if (plugin_loaded('video') and config('video-upload', false) and config('video-encoder') != 'none') {
                $videoInsert = add_video(array(
                    'title' => '',
                    'description' => '',
                    'privacy' => $privacy,
                    'source' => 'upload',
                    'status' => 0,
                    'file_path' => $video,
                    'auto_posted' => 1
                ));
                $video = $videoInsert['id'];
            }
            $val['video'] = $video;
        } else {
            $result['status'] = 0;
            $result['message'] = $uploader->getError();
            return json_encode($result);
        }
    }

    $file = input_file('file_attach');
    if ($file) {
        $uploader = new Uploader($file, 'file');
        $path = get_userid().'/'.date('Y').'/files/posts/';
        $uploader->setPath($path);
        if ($uploader->passed()) {
            $file = $uploader->uploadFile()->toDB($entityType.'-posts-files', $entityId, $privacy)->result();
            $uploadedFiles[$uploader->insertedId] = array(
                'path' => $file,
                'name' => $uploader->sourceName,
                'extension' => $uploader->extension
            );
            $val['files'] = perfectSerialize($uploadedFiles);
        } else {
            $result['status'] = 0;
            $result['message'] = $uploader->getError();
            return json_encode($result);
        }
    }

    $feed = add_feed($val, true);
    if ($feed) {
        $result['feed'] = api_arrange_feed($feed);
    } else {
        $result['status'] = 0;
    }

    return json_encode($result);
}

function submit_poll_pager($app) {
    $pollId = input('poll_id');
    $feed = find_feed($pollId);
    $answerId = input("answer_id");
    $answers = str_replace(array("[", "]"), array(",", ""), input("answers"));
    $answers = explode(",", $answers);
    $newAnswers = array();
    foreach($answers as $a) {
        if ($a)  $newAnswers[$a] = $a;
    }


    $val = array(
        'poll_id' => $pollId,
        'answer' => $answerId,
        'answers' => $newAnswers
    );
    feed_submit_poll($val, $feed);
    $feed = find_feed($pollId);
    return json_encode(api_arrange_feed($feed));
}

function action_pager($app) {
    $userid = input("userid");
    $action = input("action");
    $feedId = input("feed_id");
    $feed = find_feed(input("feed_id"));
    $result = array(
        'status' => 1
    );
    switch($action) {
        case 'hide':
            hide_feed($feedId);
            break;
        case 'pin':
            pin_feed($feed);
            break;
        case 'subscribe':
            add_subscriber(get_userid(), 'feed', $feedId);
            break;
        case 'unsubscribe':
            remove_subscriber(get_userid(), 'feed', $feedId);
            break;
        case "remove":
            remove_feed($feedId);
            break;
        case "edit":
            $text = input("text");
            save_feed($feedId, $text);
            break;
        case "share":
            share_feed($feedId);
            break;
    }

    return json_encode($result);
}