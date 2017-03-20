<?php
function add_photo_pager($app){
    $app->setTitle(lang("marketplace::add-photos"));
    $message = null;
    $listing_id = input('id') ? input('id') : null;
    $listing_images = marketplace_get_listing_images($listing_id);
    $num_listing_images = count($listing_images);
    $val = input('val');
    if($val && (config('max-num-listing-photos', 5) > $num_listing_images)){
        $image = input_file('image');
        $image_path = '';
        if ($image) {
			$uploader = new Uploader($image);
			if ($uploader->passed()) {
				$uploader->setPath('marketplace/listings/images/');
				$image_path = $uploader->resize()->result();
			}
			else {
				$message = $uploader->getError();
			}
        }
        if (!$message) {
            $val['image_path'] = $image_path;
            marketplace_execute_form($val);
            return redirect_to_pager('marketplace-listing-slug', array('appends' => marketplace_get_slug($listing_id, 'listing')));
        }
    }
    return $app->render(view('marketplace::add_photo', array('listing_id' => $listing_id, 'listing_images' => $listing_images, 'num_listing_images' => $num_listing_images, 'message' => $message)));
}