<?php
function game_upgrade_database() {
    $db = db();
    try{$db->query("ALTER TABLE `games` ADD `slug` VARCHAR(64) NOT NULL;");}catch(Exception $e){$error = $e;}

    try{$db->query("ALTER TABLE  `games` ADD  `featured` TINYINT(1) NOT NULL DEFAULT  '0'");}catch(Exception $e){$error = $e;}

    fire_hook("gamewidget.add", null);

    game_dump_site_pages();

}


function game_dump_site_pages() {
    register_site_page('games', array('title' => lang('game::games'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
            Widget::add(null, 'games', 'content', 'middle');
            Widget::add(null, 'games', 'plugin::game|menu', 'left');
            Widget::add(null, 'games', 'plugin::game|featured', 'left');
            Widget::add(null, 'games', 'plugin::game|latest', 'left');
            Widget::add(null, 'games', 'plugin::game|top', 'left');
            Menu::saveMenu('main-menu', 'game::game', 'games', 'manual', true, 'ion-ios-game-controller-b-outline');
        }
    );
    register_site_page('game-create', array('title' => lang('game::add-game'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
            Widget::add(null, 'game-create', 'content', 'middle');
        }
    );
    register_site_page('game-profile', array('title' => lang('game::view-game'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
            Widget::add(null, 'game-profile', 'content', 'middle');
         }
    );
    register_site_page('game-profile-play', array('title' => lang('game::game-play'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
            Widget::add(null, 'game-profile-play', 'content', 'middle');
        }
    );
    register_site_page('game-profile-edit', array('title' => lang('game::edit-game'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
            Widget::add(null, 'game-profile-edit', 'content', 'middle');
        }
    );
}