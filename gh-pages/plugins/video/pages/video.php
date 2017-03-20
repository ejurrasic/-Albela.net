<?php
function videos_pager($app) {
    $app->setTitle(lang('video::videos'));
    $category = input('category', 'all');
    $term = input('term');
    $type = input('type', 'browse');
    $filter = input('filter', 'all');
    $videos = get_videos($type, $category, $term, null, null, $filter);
    return $app->render(view('video::index', array('videos' => $videos)));
}

function video_page_pager($app) {
    $videoId = segment(1);
    $video = $app->video;
    fire_hook('video.playing', null, array($video));
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $video['title'], 'description' =>  $video['description'], 'image' =>  $video['photo_path'], 'keywords' => ''));
    return $app->render(view('video::page', array('video' => $video)));
}

function video_edit_pager($app) {
    $video = get_video(input('id'));
    if (!$video or !is_video_owner($video)) redirect('videos');
    $app->setTitle(lang('video::edit-video'));
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        save_video($val, $video);
        return redirect(get_video_url($video));
    }
    return $app->render(view('video::edit', array('video' => $video, 'message' => $message)));
}

function video_delete_pager($app) {
    $video = get_video(input('id'));
    if (!$video or !is_video_owner($video)) return  redirect('videos');
    delete_video($video['id']);

    return redirect_to_pager('videos');
}
function create_pager($app) {
    $app->setTitle(lang('video::add-new-video'));
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        //check for video files
        $videoFile = input_file('video_file');
        if ($videoFile and config('video-upload', false)) {
            if (config('video-encoder') == 'none') {
                app()->config['video-file-types'] = "mp4";
            }
            $validator = validator($val, array(
                'title' => 'required',
            ));
            if (validation_fails()) {
                $message = validation_first();
            } else {
                $uploader = new Uploader($videoFile, 'video');
                if ($uploader->passed()) {
                    $added = add_video($val);
                    if ($added) {
                        redirect(get_video_url($added));
                    } else {
                        $message = lang('video::video-add-error-message');
                    }
                } else {
                    $message = $uploader->getError();
                }
            }

        } else {
            /**
             * @var $link
             */
            $link = input('val.link');
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
                    redirect(get_video_url($added));
                } else {
                    $message = lang('video::video-add-error-message');
                }
            } else {
                $message = lang('video::video-not-found-in-link');
                //$message = lang('video::video-not-found-in-link').': '.$link.' ('.$embed->type.')';
            }
        }
    }
    return $app->render(view('video::create', array('message' => $message)));
}