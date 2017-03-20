<?php
//get_menu("admin-menu", "games-manager")->setActive();
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("admin-games-manager")->setActive();

function lists_pager($app) {
    $type = input('type', 'all');
    $term = input('term', '');
    $cat = input('cat', '');
    if ($term) {
        $type = 'search';
    }

    if ($cat) {
        $type = 'cat';
        $term = $cat;
    }
    $app->setTitle(lang('game::manage-games'));
    return $app->render(view('game::lists', array('games' => get_games($type, $term))));
}

function categories_pager($app) {
//    get_menu("admin-menu", "games-manager")->findMenu('admin-game-categories')->setActive();
    $app->setTitle(lang('game::manage-categories'));
    return $app->render(view('game::category/lists', array('categories' => get_game_categories())));
}


function add_category_pager($app) {
//    get_menu("admin-menu", "games-manager")->findMenu('admin-game-categories')->setActive();
    $app->setTitle(lang('game::add-category'));
    $val = input('val');
    $message  = null;
    if ($val) {
		CSRFProtection::validate();
        $file = input_file('file');

        $image = '';
        if ($file) {
            $uploader = new Uploader($file, 'image');
            if ($uploader->passed()) {
                $uploader->setPath('games/category/cover/');
                $image =$uploader->resize(1000, 400)->result();
            } else {
                $message = $uploader->getError();
            }
        }

        if (!$message) {
            game_add_category($val, $image);
            return redirect_to_pager('admin-game-categories');
        }
        //redirect to category lists
    }

    return $app->render(view('game::category/add', array('message' => $message)));
}

function manage_category_pager($app) {
//    get_menu("admin-menu", "games-manager")->findMenu('admin-game-categories')->setActive();
    $action = input('action', 'order');
    $id = input('id');
    switch($action) {
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_game_category_order($ids[$i], $i);
            }
            break;
        case 'edit':
            $message = null;
            $image = null;
            $val = input('val');
            $app->setTitle(lang('game::edit-category'));
            $category = get_game_category($id);
            if (!$category) return redirect_to_pager('admin-game-categories');
            if ($val) {
		CSRFProtection::validate();
                $file = input_file('file');

                $image = '';
                if ($file) {
                    $uploader = new Uploader($file, 'image');
                    if ($uploader->passed()) {
                        $uploader->setPath('games/category/cover/');
                        $image =$uploader->resize(1000, 400)->result();
                    } else {
                        $message = $uploader->getError();
                    }
                }
                //exit($image);
                if (!$message) {
                    save_game_category($val, $image, $category);
                    return redirect_to_pager('admin-game-categories');
                }
                //redirect to category lists
            }
            return $app->render(view('game::category/edit', array('message' => $message, 'category' => $category)));
            break;
        case 'delete':
            $category = get_game_category($id);
            if (!$category) return redirect_to_pager('admin-game-categories');
            delete_game_category($id, $category);
            return redirect_to_pager('admin-game-categories');
            break;
    }
    return $app->render();
}
 