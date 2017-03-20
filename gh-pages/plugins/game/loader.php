<?php
load_functions('game::game');
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset("game::css/game.css");
        register_asset("game::js/game.js");

    }
});

register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('game::game-permissions'),
        'description' => lang('game::game-permissions-desc'),
        'roles' => array(
            'can-create-game' => array('title' => lang('game::can-create-game'), 'value' => 1),
            'can-embed-game' => array('title' => lang('game::can-embed-game'), 'value' => 0),
        )
    );
    return $roles;
});

register_hook('username.check', function($result, $value) {
    $game = find_game($value);
    if ($game) $result = false;
    return $result;
});

register_hook('search-dropdown-start', function($content, $term) {
    $games = get_games('search', $term, 5);
    if ($games->total) {
        $content.= view('game::search/dropdown', array('games' => $games));
    }
    return $content;
});

register_hook('register-search-menu', function($term) {
    add_menu("search-menu", array('title' => lang('game::games'), 'id' => 'game', 'link' => form_search_link('game', $term)));
});

register_hook('search-result', function($content, $term, $type) {
    if ($type == 'game') {
        get_menu('search-menu', 'game')->setActive();
        $content = view('game::search/page', array('games' => get_games('search', $term)));
    }
    return $content;
});

register_filter("game-profile", function($app) {
    $slug = segment(0);

    $game = find_game($slug);

    if (!$game) return false;
    $app->profileGame = $game;
    //$app->pageUser = find_user($page['page_user_id']);
    $app->setTitle($game['game_title'])->setLayout("game::profile/layout");

    //register page profile menu
    //add_menu("page-profile", array('id' => 'timeline', 'title' => lang('page::timeline'), 'link' => page_url('', $page)));
    //add_menu("page-profile", array('id' => 'about', 'title' => lang('page::about'), 'link' => page_url('about', $page)));
    //add_menu("page-profile", array('id' => 'photos', 'title' => lang('page::photos'), 'link' => page_url('photos', $page)));
    //add_menu("page-profile-more", array('id' => 'likes', 'title' => lang('page::likes'), 'link' => page_url('likes', $page)));
    fire_hook('game.profile.started', null, array($game));

    return true;
});

register_hook("admin-started", function() {
    get_menu("admin-menu", "plugins")->addMenu(lang("game::games-manager"), "#", "admin-games-manager");
    get_menu("admin-menu", "plugins")->findMenu("admin-games-manager")->addMenu(lang("game::categories"), url_to_pager("admin-game-categories"), 'categories');
    get_menu("admin-menu", "plugins")->findMenu("admin-games-manager")->addMenu(lang("game::add-category"), url_to_pager("admin-game-category-add"), 'add-category');
    get_menu("admin-menu", "plugins")->findMenu("admin-games-manager")->addMenu(lang("game::games"), url_to_pager("admin-game-lists"), 'games');
    register_block_page('games', lang('game::games-pages'));
    register_block_page('game-profile', lang('game::game-profile'));
});

register_pager("admincp/game", array('use' => "game::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admin-game-lists'));
register_pager("admincp/games/categories", array('use' => "game::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admin-game-categories'));
register_pager("admincp/games/category/add", array('use' => "game::admincp@add_category_pager", 'filter' => 'admin-auth', 'as' => 'admin-game-category-add'));
register_pager("admincp/games/category/manage", array('use' => "game::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admin-game-manage-category'));


register_post_pager("game/change/cover", array('use' => 'game::profile@upload_cover_pager', 'filter' => 'user-auth'));
register_pager("game/cover/reposition", array('use' => 'game::profile@reposition_cover_pager', 'filter' => 'user-auth'));
register_pager("game/cover/remove", array('use' => 'game::profile@remove_cover_pager', 'filter' => 'user-auth'));

if (is_loggedIn()) {
    if (user_has_permission('can-create-game')) register_pager("game/add", array('use' => "game::game@add_game_pager", 'filter' => 'user-auth', 'as' => 'game-create'));
}
register_pager("page/mine", array('use' => "page::page@my_pages_pager", 'filter' => 'user-auth', 'as' => 'page-mine'));
register_pager("games", array('use' => "game::game@games_pager", 'as' => 'games'));

register_pager("game/delete/{id}", array('use' => "game::game@game_delete_pager", 'as' => 'game-delete', 'filter' => 'user-auth'))->where(array('id' => '[0-9]+'));

//frontend pager registration
register_hook('system.started', function() {
    register_pager("{slug}", array('use' => "game::profile@game_profile_pager", 'filter' => 'game-profile', 'as' => 'game-profile'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{slug}/play", array('use' => "game::profile@game_play_profile_pager", 'filter' => 'game-profile', 'as' => 'game-profile-play'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    register_pager("{slug}/edit", array('use' => "game::profile@game_edit_profile_pager", 'filter' => 'game-profile', 'as' => 'game-profile-edit'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
    //register_pager("{slug}/photos", array('use' => "page::profile@page_photos_profile_pager", 'filter' => 'page-profile', 'as' => 'page-profile-photos'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
});

register_hook('admin.statistics', function($stats) {
    $stats['games'] = array(
        'count' => count_total_games(),
        'title' => lang('game::games'),
        'icon' => 'ion-ios-game-controller-b',
        'link' => url_to_pager('admin-game-lists'),
    );
    return $stats;
});



register_hook('user.delete', function($userid) {
    $d = db()->query("SELECT * FROM games WHERE user_id='{$userid}'");
    while($game = $d->fetch_assoc()) {
        delete_game($game);
    }
});

register_hook('saved.content', function($content, $type) {
    add_menu('saved', array('title' => lang('game::games'). ' <span style="color:lightgrey;font-size:12px">'.count(get_user_saved('game')).'</span>', 'link' => url('saved/games'), 'id' => 'games'));
    if ($type == 'games') {
        $content = view('game::saved/content', array('games' => get_games('saved')));
    }

    return $content;
});

add_menu_location('game-menu', 'game::game-menu');
add_available_menu('game::game', 'games', 'ion-ios-game-controller-b-outline');
