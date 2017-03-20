<?php
function delete_listing_pager($app){
    $app->setTitle(lang("marketplace::delete-listing"));
    $message = null;
    $categories = marketplace_get_categories();
    $listing_id = input('id') ? input('id') : null;
    $val = input('val');
    if($listing_id){
        if ($val) {
		CSRFProtection::validate();
            marketplace_execute_form($val);
            return redirect_to_pager('marketplace-slug', array('appends' => '/m'));
        }
        $listing = marketplace_get_listing($listing_id)[0];
        return $app->render(view('marketplace::delete_listing', array('categories' => $categories, 'listing' => $listing, 'message' => $message)));
    }
}