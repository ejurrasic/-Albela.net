<?php
load_functions('marketplace::marketplace');

register_asset("marketplace::css/marketplace.css");

register_asset("marketplace::js/marketplace.js");

register_pager("admincp/marketplace/categories", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-marketplace-categories-list',
    'use' => 'marketplace::admincp@categories_pager'));

register_pager("admincp/marketplace/category/add", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-marketplace-category-add',
    'use' => 'marketplace::admincp@add_category_pager'));

register_pager("admincp/marketplace/category/edit", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-marketplace-category-edit',
    'use' => 'marketplace::admincp@edit_category_pager'));

register_pager("admincp/marketplace/category/delete", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-marketplace-category-delete',
    'use' => 'marketplace::admincp@delete_category_pager'));

register_pager("admincp/marketplace/listing/list", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-marketplace-listings-list',
    'use' => 'marketplace::admincp@listings_pager'));

register_pager("admincp/marketplace/listing/list?t=p", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-marketplace-listings-list-pending',
    'use' => 'marketplace::admincp@listings_pager'));

register_pager("admincp/marketplace/listing/edit", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-marketplace-listing-edit',
    'use' => 'marketplace::admincp@edit_listing_pager'));

register_pager("admincp/marketplace/listing/delete", array(
    'filter'=> 'admin-auth',
    'as' => 'admin-marketplace-listing-delete',
    'use' => 'marketplace::admincp@delete_listing_pager'));

register_pager("marketplace/create-listing", array(
    'filter'=> 'user-auth',
    'as' => 'marketplace-create-listing',
    'use' => 'marketplace::create_listing@create_listing_pager'));

register_pager("marketplace/edit-listing", array(
    'filter'=> 'user-auth',
    'as' => 'marketplace-edit-listing',
    'use' => 'marketplace::edit_listing@edit_listing_pager'));

register_pager("marketplace/delete-listing", array(
    'filter'=> 'user-auth',
    'as' => 'marketplace-delete-listing',
    'use' => 'marketplace::delete_listing@delete_listing_pager'));

register_pager("marketplace/add-photo", array(
    'filter'=> 'user-auth',
    'as' => 'marketplace-add-photo',
    'use' => 'marketplace::add_photo@add_photo_pager'));

register_pager("marketplace/delete-photo", array(
    'filter'=> 'user-auth',
    'as' => 'marketplace-delete-photo',
    'use' => 'marketplace::delete_photo@delete_photo_pager'));

register_pager("marketplace/listing{appends}", array(
    'as' => 'marketplace-listing-slug',
    'use' => 'marketplace::listing@listing_slug_pager'))->where(array('appends' => '.*'));

register_pager("marketplace{appends}", array(
    'as' => 'marketplace-slug',
    'use' => 'marketplace::marketplace@marketplace_slug_pager'))->where(array('appends' => '.*'));

/*register_hook("admin-started", function() {
    add_menu("admin-menu", array("id" => "marketplace-manager", "title" => lang("marketplace::marketplace-manager"), "link" => "#", "icon" => "ion-ios-albums"));
    get_menu("admin-menu", "marketplace-manager")->addMenu(lang("marketplace::manage-categories"), "#", "admin-marketplace-categories");
    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-categories")->addMenu(lang("marketplace::list"), url_to_pager("admin-marketplace-categories-list"), 'list');
    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-categories")->addMenu(lang("marketplace::add-category"), url_to_pager("admin-marketplace-category-add"), 'add-category');
    get_menu("admin-menu", "marketplace-manager")->addMenu(lang("marketplace::manage-listings"), "#", "admin-marketplace-listings");
    get_menu("admin-menu", "marketplace-manager")->findMenu("admin-marketplace-listings")->addMenu(lang("marketplace::list"), url_to_pager("admin-marketplace-listings-list"), 'list');
});*/

register_hook("admin-started", function() {
    get_menu("admin-menu", "plugins")->addMenu(lang("marketplace::marketplace-manager"), "#", "admin-marketplace-manager");
    get_menu("admin-menu", "plugins")->findMenu("admin-marketplace-manager")->addMenu(lang("marketplace::categories"), url_to_pager("admin-marketplace-categories-list"), 'categories');
    get_menu("admin-menu", "plugins")->findMenu("admin-marketplace-manager")->addMenu(lang("marketplace::add-category"), url_to_pager("admin-marketplace-category-add"), 'add-category');
    get_menu("admin-menu", "plugins")->findMenu("admin-marketplace-manager")->addMenu(lang("marketplace::listings"), url_to_pager("admin-marketplace-listings-list"), 'listings');
});

register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'listing') {
        $listing = marketplace_get_listing($typeId)[0];
		if ($listing['lister_id'] != get_userid()) {
			send_notification($listing['lister_id'], 'listing.comment', $typeId, $listing, '', $text);
		}
    }
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'listing.comment') {
		return view('marketplace::notifications/listing_comment', array('notification' => $notification, 'data' => unserialize($notification['data'])));
        delete_notification($notification['notification_id']);
    }
});

register_hook('search-dropdown-start', function($content, $term) {
    $listings = marketplace_get_listings(null, $term, null, 1, 5);
    if ($listings->total) {
        $content.= view('marketplace::search/dropdown', array('listings' => $listings));
    }
    return $content;
});

register_hook('register-search-menu', function($term) {
    add_menu("search-menu", array('title' => lang('marketplace::listings'), 'id' => 'listings', 'link' => form_search_link('listings', $term)));
});

register_hook('search-result', function($content, $term, $type) {
    if ($type == 'listings') {
        get_menu('search-menu', 'listings')->setActive();
		$listings = marketplace_get_listings(null, $term, null, 1, config('pagination-limit-listings', 20));
        $content = view('marketplace::search/page', array('listings' => $listings));
    }
    return $content;
});


register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM FROM marketplace_listings WHERE lister_id = ".$userid);
});

register_hook("after-render-css", function($html) {
    $html .= "
	<style>
		.marketplace .marketplace-featured-listing {
		background-color: ".config('featured-badge-bg-color', '#FF0000')." !important;
		color: ".config('featured-badge-text-color', '#FFCCCC')." !important;
		}
    </style>\n";
    return $html;
});

register_hook('admin.statistics', function($stats) {
    $stats['marketplace'] = array(
        'count' => marketplace_num_listings(),
        'title' => lang('marketplace::marketplace'),
        'icon' => 'ion-android-cart',
        'link' => url_to_pager('admin-marketplace-listings-list'),
    );
    $stats['pendinglistings'] = array(
        'count' => marketplace_num_pending_listings(),
        'title' => lang('marketplace::pending-listings'),
        'icon' => 'ion-android-cart',
        'link' => url('admincp/marketplace/listing/list?t=p'),
    );
    return $stats;
});

register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('marketplace::marketplace-permissions'),
        'description' => '',
        'roles' => array(
            'can-create-listing' => array('title' => lang('marketplace::can-create-listing'), 'value' => 1),
        )
    );
    return $roles;
});


add_menu_location('marketplace-menu', 'marketplace::marketplace-menu');
add_available_menu('marketplace::marketplace', 'marketplace', 'ion-android-cart');