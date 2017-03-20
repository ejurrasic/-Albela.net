<?php
function photo_upgrade_database() {
    photo_dump_site_pages();
}

function photo_dump_site_pages() {
    register_site_page("photo", array('title' => lang('photo-directory'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'photo','content', 'middle');
        Widget::add(null,'photo','plugin::photo|menu', 'right');
        Widget::add(null,'photo','plugin::photo|latest', 'right');
        Menu::saveMenu('main-menu', 'photo::photos', 'photos', 'manual', 1, 'ion-images');
    });
    register_site_page("photo-myphotos", array('title' => lang('my-photos'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'photo-myphotos','content', 'middle');
        Widget::add(null,'photo-myphotos','plugin::photo|menu', 'right');
        Widget::add(null,'photo-myphotos','plugin::photo|latest', 'right');
    });
    register_site_page("photo-albums", array('title' => lang('all-albums'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'photo-albums','content', 'middle');
        Widget::add(null,'photo-albums','plugin::photo|menu', 'right');
        Widget::add(null,'photo-albums','plugin::photo|latest', 'right');
    });
    register_site_page("photo-myalbums", array('title' => lang('my-albums'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'photo-myalbums','content', 'middle');
        Widget::add(null,'photo-myalbums','plugin::photo|menu', 'right');
        Widget::add(null,'photo-myalbums','plugin::photo|latest', 'right');
    });
    register_site_page("photo-create-album", array('title' => lang('photo::create-new-album'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'photo-create-album','content', 'middle');
        Widget::add(null,'photo-create-album','plugin::photo|menu', 'right');
        Widget::add(null,'photo-create-album','plugin::photo|latest', 'right');
    });
    register_site_page("photo-edit-album", array('title' => lang('photo::edit-album'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'photo-edit-album','content', 'middle');
        Widget::add(null,'photo-edit-album','plugin::photo|menu', 'right');
        Widget::add(null,'photo-edit-album','plugin::photo|latest', 'right');
    });
    register_site_page("photo-album-photos", array('title' => lang('photo::album-photos'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'photo-album-photos','content', 'middle');
        Widget::add(null,'photo-album-photos','plugin::photo|menu', 'right');
        Widget::add(null,'photo-album-photos','plugin::photo|latest', 'right');
    });
    register_site_page("photo-myalbum-photos", array('title' => lang('photo::my-album-photos'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'photo-myalbum-photos','content', 'middle');
        Widget::add(null,'photo-myalbum-photos','plugin::photo|menu', 'right');
        Widget::add(null,'photo-myalbum-photos','plugin::photo|latest', 'right');
    });
}