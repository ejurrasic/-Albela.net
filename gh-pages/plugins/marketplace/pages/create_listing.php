<?php
function create_listing_pager($app){
    $app->setTitle(lang("marketplace::create-listing"));
    $message = null;
    $val = input('val');
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
        }
        if (!$message) {
            $val['image_path'] = $image_path;
            $listing_id = marketplace_execute_form($val);
            $slug = marketplace_get_slug($listing_id, 'listing');
            return redirect_to_pager('marketplace-listing-slug', array('appends' => $slug));
        }
    }
    $categories = marketplace_get_categories();
    return $app->render(view('marketplace::create_listing', array('categories' => $categories, 'message' => $message)));
}