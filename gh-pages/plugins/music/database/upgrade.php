<?php
function music_upgrade_database() {
    register_site_page("musics", array('title' => 'music::musics-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'musics', 'content', 'middle');
        Widget::add(null,'musics', 'plugin::music|menu', 'right');
        Widget::add(null,'musics', 'plugin::music|featured', 'right');
        Widget::add(null,'musics', 'plugin::music|top', 'right');
        Widget::add(null,'musics', 'plugin::music|latest', 'right');
        Widget::add(null,'profile', 'plugin::music|profile-recent', 'right');
        Widget::add(null,'feed', 'plugin::music|latest', 'right');
        Menu::saveMenu('main-menu', 'music::musics', 'musics', 'manual', true, 'ion-music-note');
    });
    register_site_page("music-playlists", array('title' => 'music::playlists-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'music-playlists', 'content', 'middle');
        Widget::add(null,'music-playlists', 'plugin::music|playlistsmenu', 'right');
        Widget::add(null,'music-playlists', 'plugin::music|featuredplaylists', 'right');
        Widget::add(null,'music-playlists', 'plugin::music|topplaylists', 'right');
        Widget::add(null,'music-playlists', 'plugin::music|latestplaylists', 'right');
    });
    register_site_page("music-create", array('title' => 'music::musics-create-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'music-create', 'content', 'middle');
        Widget::add(null,'music-create', 'plugin::music|menu', 'right');
    });
    register_site_page("music-playlist-create", array('title' => 'music::playlist-create-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'music-playlist-create', 'content', 'middle');
        Widget::add(null,'music-playlist-create', 'plugin::music|playlistsmenu', 'right');
    });
    register_site_page("music-edit", array('title' => 'music::music-edit-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'music-edit', 'content', 'middle');
        Widget::add(null,'music-edit', 'plugin::music|menu', 'right');
    });
    register_site_page("music-playlist-edit", array('title' => 'music::playlist-edit-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'music-playlist-edit', 'content', 'middle');
        Widget::add(null,'music-playlist-edit', 'plugin::music|playlistsmenu', 'right');
    });
    register_site_page("music-page", array('title' => 'music::view-music-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'music-page', 'content', 'middle');
        Widget::add(null,'music-page', 'plugin::music|menu', 'right');
        Widget::add(null,'music-page', 'plugin::music|related', 'right');
        Widget::add(null,'music-page', 'plugin::music|latest', 'right');
    });
    register_site_page("music-playlist-page", array('title' => 'music::view-playlist-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(null,'music-playlist-page', 'content', 'middle');
        Widget::add(null,'music-playlist-page', 'plugin::music|playlistsmenu', 'right');
        Widget::add(null,'music-playlist-page', 'plugin::music|relatedplaylists', 'right');
        Widget::add(null,'music-playlist-page', 'plugin::music|latestplaylists', 'right');
    });
}