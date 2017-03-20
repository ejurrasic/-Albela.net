<?php
function edit_listing_pager($app){
    $app->setTitle(lang("marketplace::edit-listing"));
    $message = null;
    $categories = marketplace_get_categories();
    $val = input('val');
    $listing_id = input('id') ? input('id') : null;
    if($listing_id){
        if ($val) {
    		CSRFProtection::validate();
            $image = input_file('image');
            $image_path = '';
            if ($image) {
                $uploader = new Uploader($image);
                if ($uploader->passed()) {
                    $uploader->setPath('marketplace/listings/images/');
                    $image_path = $uploader->resize()->result();
                } else {
                    $message = $uploader->getError();
                }
            $val['image_path'] = $image_path;
            }
            if (!$message) {
                marketplace_execute_form($val);
                return redirect_to_pager('marketplace-listing-slug', array('appends' => marketplace_get_slug($listing_id, 'listing')));
            }
        }
    $listing = marketplace_get_listing($listing_id)[0];
    return $app->render(view('marketplace::edit_listing', array('categories' => $categories, 'listing' => $listing, 'listing_id' => $listing_id, 'message' => $message)));
    }
}