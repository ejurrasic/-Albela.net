<?php
//register assets for frontend
register_asset("css/bootstrap.min.css");
register_asset("css/ionicons.min.css");
register_asset("css/spectrum.css");
register_asset("css/style.css");
if (app()->langDetails['dir'] == 'rtl' and !isMobile()) {
    register_asset("css/bootstrap-rtl.css");
    register_asset("css/style-rtl.css");
}

register_asset("js/jquery.js");
register_asset("js/jquery-ui.js");
register_asset("js/jquery.timeago.js");
register_asset("js/jquery.slimscroll.min.js");
register_asset("js/tether.min.js");
register_asset("js/bootstrap.min.js");
register_asset("js/spectrum.js");
register_asset('js/t-text.js');
register_asset('js/RTLText.js');
register_asset('js/sticky.js');
register_asset("js/geocomplete.js");
register_asset("js/script.js");



function theme_language_selection() {
    if (isset($_COOKIE['sv_language'])) return true;
    return false;
}

function get_default_design_template() {
    return array(
        'image' => '',
        'repeat' => 'no-repeat',
        'color' => config('plus-main-body-bg-color','#e9eaed'),
        'position' => '',
        'link' => config('plus-link-color','#4C4C4E'),
        'container' => ''
    );
}

app()->design = get_default_design_template();
function get_design_template() {
    return array(
        'default' => array(
            'preview' => img('images/design/preview/default.png'),
            'image' => '',
            'repeat' => 'no-repeat',
            'color' => config('main-body-bg-color','#e9eaed'),
            'position' => 'left',
            'link' => config('link-color','#4C4C4E'),
            'container' => ''
        ),

        'greenard' => array(
            'preview' => img('images/design/preview/greenard.png'),
            'image' => 'images/design/bg/greenard.jpg',
            'repeat' => 'repeat',
            'color' => '#93c47d',
            'position' => '',
            'link' => '#93c47d',
            'container' => 'rgba(147, 196, 125, 0.5)'
        ),
        'floral' => array(
            'preview' => img('images/design/preview/floral.jpg'),
            'image' => 'images/design/bg/floral.jpg',
            'repeat' => 'repeat',
            'color' => '#C0C0C0',
            'position' => 'left',
            'link' => '#FF5733',
            'container' => 'rgba(192,192,192,0.5)'
        ),
        'pentagon' => array(
            'preview' => img('images/design/preview/pentagon.png'),
            'image' => 'images/design/bg/pentagon.png',
            'repeat' => 'repeat',
            'color' => '#C0C0C0',
            'position' => 'left',
            'link' => '#FF5733',
            'container' => 'rgba(147, 196, 125, 0.5)'
        ),
        'paisley' => array(
            'preview' => img('images/design/preview/paisley.png'),
            'image' => 'images/design/bg/paisley.png',
            'repeat' => 'repeat',
            'color' => '#C0C0C0',
            'position' => 'left',
            'link' => '#93c47d',
            'container' => 'rgba(147, 196, 125, 0.5)'
        ),
        'nature' => array(
            'preview' => img('images/design/preview/nature.jpg'),
            'image' => 'images/design/bg/nature.jpg',
            'repeat' => 'repeat-x',
            'color' => '#285a0e',
            'position' => 'left',
            'link' => '#285a0e',
            'container' => ''
        ),
        'redhat' => array(
            'preview' => img('images/design/preview/redhat.jpg'),
            'image' => 'images/design/bg/redhat.jpg',
            'repeat' => 'repeat-x',
            'color' => '#26680a',
            'position' => 'left',
            'link' => '#26680a',
            'container' => 'rgba(38, 104, 10, 0.2)'
        ),
        'bluestack' => array(
            'preview' => img('images/design/preview/bluestack.jpg'),
            'image' => 'images/design/bg/bluestack.jpg',
            'repeat' => 'repeat',
            'color' => '#1349a1',
            'position' => 'center',
            'link' => '#1349a1',
            'container' => 'rgba(19, 73, 161, 0.5)'
        ),
        'mildflower' => array(
            'preview' => img('images/design/preview/mildflower.jpg'),
            'image' => 'images/design/bg/mildflower.jpg',
            'repeat' => 'repeat',
            'color' => '#041f4c',
            'position' => 'center',
            'link' => '#041f4c',
            'container' => 'rgba(4, 31, 76, 0.5)'
        ),
        'army3' => array(
            'preview' => img('images/design/preview/army3.jpg'),
            'image' => 'images/design/bg/army3.jpg',
            'repeat' => 'repeat',
            'color' => '#041f4c',
            'position' => 'center',
            'link' => '#041f4c',
            'container' => 'rgba(4, 31, 76, 0.5)'
        ),
        'pattern8' => array(
            'preview' => img('images/design/preview/pattern8.png'),
            'image' => 'images/design/bg/pattern8.png',
            'repeat' => 'repeat',
            'color' => '#041f4c',
            'position' => 'center',
            'link' => '#041f4c',
            'container' => 'rgba(4, 31, 76, 0.5)'
        ),
        'pattern22' => array(
            'preview' => img('images/design/preview/pattern22.jpg'),
            'image' => 'images/design/bg/pattern22.jpg',
            'repeat' => 'repeat',
            'color' => '#041f4c',
            'position' => 'center',
            'link' => '#041f4c',
            'container' => 'rgba(4, 31, 76, 0.5)'
        ),
    );
}
