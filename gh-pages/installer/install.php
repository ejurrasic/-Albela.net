<?php
function index_pager() {
    $accept = installer_input('accept');

    if (get_request_method() == 'POST' and $accept) {
        redirect(url("install/db"));
    }
    echo view('installer/index');
}

function database_pager() {
    $val = installer_input('val');
    session_put('purchase', true);
    if (!session_get('purchase')) redirect(url("install/db"));
    $message = null;
    if ($val) {
        $installed = installer_db($val);
        if ($installed) {
            fire_hook('install.set');
            redirect(url('install/info'));
        }
        $message = "Failed to connect to database with provided information, please check";
    }
    echo view('installer/database', array('message' => $message));
}

function install_setup_system() {
    add_new_site_page(array('slug' => 'terms-and-condition', 'title' => array('english' => 'Terms and condition'), 'content' => 'Your content here', 'tags' => '', 'footer' => 1, 'explore' => 0), true);
    add_new_site_page(array('slug' => 'privacy-policy', 'title' => array('english' => 'Privacy Policy'), 'content' => 'Your content here', 'tags' => '', 'footer' => 1, 'explore' => 0), true);
    add_new_site_page(array('slug' => 'disclaimer', 'title' => array('english' => 'Disclaimer'), 'content' => 'Your content here', 'tags' => '', 'footer' => 1, 'explore' => 0), true);
    add_new_site_page(array('slug' => 'about', 'title' => array('english' => 'About Us'), 'content' => 'Your content here', 'tags' => '', 'footer' => 1, 'explore' => 0), true);

    load_functions('game::game');
    game_add_category(array('title' => array('english' => 'Action')));
    game_add_category(array('title' => array('english' => 'Racing')));
    game_add_category(array('title' => array('english' => 'Fighting')));
    game_add_category(array('title' => array('english' => 'Sports')));

    load_functions('event::event');
    event_add_category(array('title' => array('english' => 'Wedding')));
    event_add_category(array('title' => array('english' => 'Outing')));
    event_add_category(array('title' => array('english' => 'Party')));

    load_functions('page::page');
    page_add_category(array('title' => array('english' => 'Web Designer'), 'desc' => array('english' => '')));
}

function require_pager()
{
    $message = session_get('require-message');
    echo fire_hook('install.require');echo view("installer/requirements", array('message' => $message));
}


function info_pager() {
    if (!session_get('purchase')) redirect(url("install/requirements"));
    $message = null;
    $val = input('val');
    if ($val) {

        install_setup_system();
        fire_hook('core.info.in');
        $message = "All fields are required and make sure your password match and you provide a valid email address";
    }
    echo view('installer/info', array('message' => $message));
}

function finish_pager() {
    echo view('installer/finish');
}