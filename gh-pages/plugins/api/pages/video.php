<?php
function browse_pager($app) {
    $categoryId = input("category_id", 'all');
    $term = input("term");
    $type = input("type", "browse");
    $page = input("page");
    $limit = input("limit", 10);
    $filter = input('filter', 'all');
    $videos = get_videos($type, $categoryId, $term, null, null, $filter);
    $result = array(
        'categories' => array(
            array('id' => 'all', 'title' => lang('all'))
        ),
        'videos' => array()
    );

    foreach(get_video_categories() as $category) {
        $result['categories'][] = array(
            'id' => $category['id'],
            'title' => lang($category['title'])
        );
    }

    foreach($videos->results() as $video) {
        $result['videos'][] = api_arrange_video($video);
    }

    return json_encode($result);
}

function get_categories_pager($app) {
    $result = array();
    foreach(get_video_categories() as $category) {
        $result[] = array(
            'id' => $category['id'],
            'title' => lang($category['title']),
        );
    }

    return json_encode($result);
}

function create_pager($app) {
    $category = input('category');
    $link = input('link');
    $privacy = input('privacy');
    $videoFile = input('video');
    $description = input('description');
    $title = input('title');
    $val = array(
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'privacy' => $privacy,
        'link' => $link
    );

    $result = array(
        'status' => 0,
        'message' => ''
    );
    if ($videoFile and config('video-upload')) {
        if (config('video-encoder') == 'none') {
            app()->config['video-file-types'] = "mp4";
        }
        $validator = validator($val, array(
            'title' => 'required',
        ));
        if (validation_fails()) {
            $result['message'] = validation_first();
            return json_encode($result);
        } else {
            $uploader = new Uploader($videoFile, 'video');
            if ($uploader->passed()) {
                $added = add_video($val);
                if ($added) {
                    $result = array_merge($result, api_arrange_video($added));
                    $result['status'] = 1;
                    return json_encode($result);
                } else {
                    $result['message']  = lang('video::video-add-error-message');
                    return json_encode($result);
                }
            } else {
                $result['message']  = $uploader->getError();
                return json_encode($result);
            }
        }

    } else {
        /**
         * @var $link
         */

        //first make use of embera
        require_once(path("includes/libraries/embed/1x/autoloader.php"));
        $embed = null;
        try{
            $embed = Embed\Embed::create($link, array(
                'minImageWidth' => 50,
                'minImageHeight' => 50,
                "resolver" => array(
                    "options" => array(
                        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1'
                    )
                )
            ));
        }
        catch (Exception $e) {
            ///exit(var_dump($e));
            //exit($e->getMessage());
            $embed = null;
        }
        if (($embed and ($embed->type == 'video')) or is_youtube_video($link)) {
            $val['title'] = mysqli_real_escape_string(db(), sanitizeText($embed->title));
            $val['description'] = mysqli_real_escape_string(db(), sanitizeText($embed->description));
            $val['code'] = $embed->code;
            if($embed->image != null) {
                $val['photo_path'] = $embed->image;
            } else {
                $val['photo_path'] = isset($embed->images[0]) ? $embed->images[0] : "";
            }
            $added = add_video($val);

            if ($added) {
                $result = array_merge($result, api_arrange_video($added));
                $result['status'] = 1;
                return json_encode($result);
            } else {
                $result['message'] = lang('video::video-add-error-message');
                return json_encode($result);
            }
        } else {
            $result['message'] = lang('video::video-not-found-in-link');
            return json_encode($result);
            //$message = lang('video::video-not-found-in-link').': '.$link.' ('.$embed->type.')';
        }
    }
}

