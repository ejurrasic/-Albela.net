<?php
function blocks_pager($app) {
    $app->setTitle(lang("blocks-manager"));
    $page = input('page', "all");
    $menu = get_menu("admin-menu", 'appearance');
    $menu->setActive();

    return $app->render(view("blocks/content", array('page' => $page)));
}

function register_blocks_pager($app) {
    CSRFProtection::validate(false);
    $pageId = input('page');
    $id = input('id');
    $view = input('view');
    $settings = perfectUnserialize(input('settings'));
    $s = array();
    foreach($settings as $sd => $d) {
        $s[$sd] = $d['value'];
    }
    add_page_block($view, $pageId, $id, $s);
}

function sort_blocks_pager($app) {
    CSRFProtection::validate(false);
    $data = input('data');
    $page = input('page');
    for($i=0;$i< count($data);$i++){
        list($id, $block) = explode('-', $data[$i]);
        update_blocks_order($page, $id, $i);
    }
}

function save_blocks_pager($app) {
    CSRFProtection::validate(false);
    $page = input('page');
    $id = input('id');
    $vals = input('val', null);
    $newVal = array();
    foreach($vals as $key => $value) {
        //$newVal[$key] = str_replace(array('\"',"\'","\\n"), array('"',"'",''), $value);
    }

    save_block_settings($page, $id, $vals);
}

function remove_blocks_pager($app) {
    CSRFProtection::validate(false);
    remove_block_page(input('id'));
}
 