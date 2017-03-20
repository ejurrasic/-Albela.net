<?php
//get_menu("admin-menu", "marketplace-manager")->setActive();
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("admin-marketplace-manager")->setActive();

function categories_pager($app){
//    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-categories")->setActive();
    $app->setTitle(lang("marketplace::categories"));

    return $app->render(view('marketplace::admincp/category/list', array('categories' => marketplace_get_categories())));
}

function add_category_pager($app){
//    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-categories")->setActive();
    $app->setTitle(lang("marketplace::add-category"));
    $val = input('val');
    $messages = null;
    if ($val) {
		CSRFProtection::validate();
        marketplace_execute_form($val);
        return redirect_to_pager('admin-marketplace-categories-list');
    }
    return $app->render(view('marketplace::admincp/category/add', array('messages' => $messages)));
}

function edit_category_pager($app){
//    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-categories")->setActive();
    $app->setTitle(lang("marketplace::edit-category"));
    $id = input('id');
    $category_id = $id ? $id : 0;
    $val = input('val');
    $messages = null;
    if ($val) {
		CSRFProtection::validate();
        marketplace_execute_form($val);
        return redirect_to_pager('admin-marketplace-categories-list');
    }
    return $app->render(view('marketplace::admincp/category/edit', array('messages' => $messages, 'category_id' => $category_id, 'category' => marketplace_get_category($category_id)[0])));
}

function delete_category_pager($app){
//    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-categories")->setActive();
    $app->setTitle(lang("marketplace::delete-category"));
    $val = input('val');
    $id = input('id');
    $messages = null;
    $category_id = $id ? $id : 0;
    $category = marketplace_get_category($category_id)[0];
    $categories = marketplace_get_categories();
    if(!marketplace_is_category_exist($category_id)){
        $messages = 'The category you want to delete does not exist';
    }
    if ($val) {
		CSRFProtection::validate();
        if(marketplace_is_category_exist($category_id)){
            marketplace_execute_form($val);
            return redirect_to_pager('admin-marketplace-categories-list');
        }
        else {
            $messages = 'The category you want to delete does not exist';
        }
    }
    return $app->render(view('marketplace::admincp/category/delete', array('messages' => $messages, 'category_id' => $category_id, 'category' => $category, 'categories' => $categories)));
}

function listings_pager($app){
//    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-listings")->setActive();
    $app->setTitle(lang("marketplace::manage-listings"));
    $category_id = input('c') ? input('c') : null;
    $type = input('t') ? input('t') : null;
    $search = input('s') ? input('s') : null;
    $page = input('page') ? input('page') : null;
    $limit = 20;
    $categories = marketplace_get_categories();
    $category = $category_id ? lang(marketplace_get_category($category_id)[0]['title']) : lang('marketplace::all-categories');
    $appends = $_GET;
    unset($appends['page']);
    $active_class = array('l' => '', 'm' => '');
    switch($type){
        case 'm':
            $active_class['m'] = ' active';
        break;

        default:
            $active_class['l'] = ' active';
        break;
    }
    $listings = marketplace_get_listings($category_id, $search, $type, $page, $limit, true)->append($appends);
    $message = null;
    $url = (isset(parse_url($_SERVER['REQUEST_URI'])['query'])) ? url_to_pager("admin-marketplace-listings-list").'?'.parse_url($_SERVER['REQUEST_URI'])['query'] : url_to_pager("admin-marketplace-listings-list").'?';
    return $app->render(view('marketplace::admincp/listing/list', array('categories' => $categories, 'category' => $category, 'listings' => $listings, 'url' => $url, 'active_class' => $active_class, 'message' => $message)));
}

function edit_listing_pager($app){
//    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-listings")->setActive();
    $app->setTitle(lang("marketplace::edit-listing"));
    $val = input('val');
    $message = null;
    $categories = marketplace_get_categories();
    $val = input('val');
    $listing_id = input('id') ? input('id') : null;
    $category_id = marketplace_get_listing($listing_id, true)[0]['category_id'];
    if($listing_id){
        if ($val) {
		CSRFProtection::validate();
            $image = input_file('image');
            $image_path = '';
            if ($image) {
                $uploader = new Uploader($image);
                if ($uploader->passed()){
                    $uploader->setPath('marketplace/listings/images/');
                    $image_path = $uploader->resize()->result();
                } else {
                    $message = $uploader->getError();
                }
                $val['image_path'] = $image_path;
            }
            if (!$message){
                marketplace_execute_form($val);
                return redirect_to_pager('admin-marketplace-listings-list');
            }
        }
    $listing = marketplace_get_listing($listing_id, true)[0];
    return $app->render(view('marketplace::admincp/listing/edit', array('message' => $message, 'listing_id' => $listing_id, 'listing' => $listing, 'category_id' => $category_id, 'categories' => $categories)));
    }
}

function delete_listing_pager($app){
//    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-listings")->setActive();
    $listing_id = input('id') ? input('id') : 0;
    $listing = marketplace_get_listing($listing_id, true)[0];
    $val = array('listing_id' => $listing_id, 'type' => 'delete_listing');
    marketplace_execute_form($val);
    return redirect_to_pager('admin-marketplace-listings-list');
}
