<?php

if (is_loggedIn()) get_menu('dashboard-main-menu', 'games')->setActive();;

function game_profile_pager($app) {
    return $app->render(view('game::profile/home'));
}

function game_play_profile_pager($app) {
    register_asset("game::js/swfobject.js");
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $app->profileGame['game_title'], 'description' => $app->profileGame['game_description'], 'image' => $app->profileGame['game_logo'] ? url_img($app->profileGame['game_logo'], 200) : '', 'keywords' => ''));
    if (is_loggedIn()) {
        $playedGames = get_privacy('played-games', array());
        if (!in_array($app->profileGame['game_id'], $playedGames)) {
            $playedGames[] = $app->profileGame['game_id'];
            add_game_player(get_userid(), $app->profileGame);
            save_privacy_settings(array('played-games' => $playedGames));
        }
    }
    return $app->render(view('game::profile/play'));
}

function game_edit_profile_pager($app) {
    $message = null;
    $val = input('val');
    if (!is_game_admin($app->profileGame)) return redirect(game_url());
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'title' => 'required',
        ));
        $gameFile = $app->profileGame['game_file'];
        $logoFile = $app->profileGame['game_logo'];

        if (validation_passes()) {
            $file = input_file('file');
            if ($file) {
                $uploader = new Uploader($file, 'file');
                if ($uploader->passed()) {
                    if ($uploader->extension == 'swf') {
                        $uploader->setPath('games/swf/');
                        $gameFile = $uploader->uploadFile()->result();
                    } else {
                        $message = lang('game::only-swf-file-allowed');
                    }
                } else {
                    $message = $uploader->getError();
                }
            }
            if (!$message) {
                if (!$gameFile and !$val['code']) {
                    $message = lang('game::provide-game-file-or-code');
                } else {
                    $logo = input_file('logo');
                    if ($logo) {
                        $uploader = new Uploader($logo);
                        if ($uploader->passed()) {
                            $uploader->setPath('games/logo/');
                            $logoFile = $uploader->resize()->result();
                        } else {
                            $message = $uploader->getError();
                        }
                    }

                    if (!$message) {
                        save_game($val, $gameFile, $logoFile, $app->profileGame);
                        return redirect(game_url(null, $app->profileGame));
                    }
                }
            }
        } else {
            $message = validation_first();
        }

    }
    return $app->render(view('game::profile/edit', array('message' => $message)));
}

function upload_cover_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    $gameId = input('id');
    $game = find_game($gameId);
    if (!$game) return json_encode($result);
    if (!is_game_admin($game)) return json_encode($result);

    if (input_file('image')) {
        $uploader = new Uploader(input_file('image'), 'image');
        $uploader->setPath('games/'.$game['game_id'].'/'.date('Y').'/photos/cover/');
        if ($uploader->passed()) {
            $original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->result();


            //delete the old resized cover
            if ($game['game_cover_resized']) {
                delete_file(path($game['game_cover_resized']));
            }

            //lets now crop this image for the resized cover
            $uploader->setPath('games/'.$game['game_id'].'/'.date('Y').'/photos/cover/resized/');
            $cover = $uploader->crop(0,  0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
            $result['image'] = url_img($cover);
            $result['original'] = url_img($original);
            $result['id'] = $uploader->insertedId;
            update_game_details(array('game_cover' => $original, 'game_cover_resized' => $cover), $game['game_id']);
            $result['status'] = 1;
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function reposition_cover_pager($app) {
    CSRFProtection::validate(false);
    $pos = input('pos');
    $width = input('width', 623);
    $gameId = input('id');
    $game = find_game($gameId);
    if (!$game) return false;
    if (!is_game_admin($game)) return false;

    $cover = path($game['game_cover']);
    $uploader = new Uploader($cover, 'image', false , true);
    $uploader->setPath('games/'.$game['game_id'].'/'.date('Y').'/photos/cover/resized/');
    $pos = abs($pos);
    $pos = ($pos / $width);
    $yCordinate = 0;
    $srcWidth = $uploader->getWidth();
    $srcHeight = $srcWidth * 0.4;
    if (!empty($pos) & $pos < $srcWidth) {
        $yCordinate = $pos  * $uploader->getWidth();
    }
    $cover = $uploader->crop(0,  $yCordinate, $srcWidth, $srcHeight)->result();

    //delete old resized image if available
    if ($game['game_cover_resized']) {
        delete_file(path($game['game_cover_resized']));
    }
    update_game_details(array('game_cover_resized' => $cover), $game['game_id']);
    return url_img($cover);
}

function remove_cover_pager($app) {
    CSRFProtection::validate(false);
    $gameId = input('id');
    $game = find_game($gameId);
    if (!$game) return false;
    if (!is_game_admin($game)) return false;

    delete_file(path($game['game_cover_resized']));

    update_game_details(array('game_cover' => '', 'game_cover_resized' => ''), $game['game_id']);
}
 