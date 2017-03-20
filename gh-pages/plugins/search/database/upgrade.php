<?php
function search_upgrade_database() {
    register_site_page("search", array('title' => 'search-page', 'column_type' => TWO_COLUMN_LEFT_LAYOUT ), function() {
        Widget::add(22777843000805,'search','content', 'middle');
        Widget::add(227778430008051,'search','plugin::search|menu', 'left');


    });

}