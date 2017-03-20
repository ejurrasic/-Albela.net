<?php
if (is_loggedIn()) get_menu('dashboard-main-menu', 'games')->setActive();
function games_pager($app) {
    $type = input('type', 'home');
    $t = 'all';
    $app->setTitle(lang('game::games'));
    switch($type) {
        case 'me':
            $t = 'me';
            $games = get_games('me');
            break;
        case 'cat':
            $games = get_games('cat', input('id'));
            break;
        default:
            $games = get_games();
            break;
    }
    return _render(view('game::browse', array('games' => $games)), $t);
}

function add_game_pager($app) {
    $message = null;
    $app->setTitle(lang('game::add-game'));
    $val = input('val');

    if (!can_create_game()) return redirect_to_pager('games');

    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'title' => 'required',
            'game_name' => 'required|min:2|username',
        ));
        $gameFile = null;
        $logoFile = null;

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
                        $gameId = add_game($val, $gameFile, $logoFile);
                        if ($gameId) {
                            $game = find_game($gameId);
                            return redirect(game_url(null, $game));
                        }
                    }
                }
            }
        } else {
            $message = validation_first();
        }

    }
    return _render(view('game::create', array('message' => $message)), 'create', true);
}

function game_delete_pager($app) {
    $gameId = segment(2);
    $game = find_game($gameId);
    if (!is_game_admin($game)) return redirect_to_pager('games');

    delete_game($game);
    if (input('admin')) redirect_back();
    return redirect_to_pager('games');
}

/**
 * Help function to render page with its layout
 */
function _render($content, $type = "all", $fullWidth = false) {

    return app()->render(view("game::layout", array('content' => $content, 'type' => $type, 'fullWidth' => $fullWidth)));
}


 