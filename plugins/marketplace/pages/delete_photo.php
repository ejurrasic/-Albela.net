<?php
function delete_photo_pager($app){
    $app->setTitle(lang("marketplace::delete-photo"));
    $message = null;
    $val = input('val');
    $listing_id = input('id') ? input('id') : null;
    if ($val) {
		CSRFProtection::validate();
		$listing_images = marketplace_get_listing_images($listing_id)[0];
            marketplace_execute_form($val);
            return redirect_to_pager('marketplace-listing-slug', array('appends' => marketplace_get_slug($listing_id, 'listing')));
        }
    $listing_image = marketplace_get_listing_images($listing_id)[0];
    return $app->render(view('marketplace::delete_photo', array('listing_id' => $listing_id, 'listing_image' => $listing_image, 'message' => $message)));
}