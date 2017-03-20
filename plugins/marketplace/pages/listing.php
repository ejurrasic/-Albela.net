<?php
function listing_pager($app){
    $message = null;
    $listing_id = input('id') ? input('id') : null;
    $val = input('val');
	if ($val) {
		CSRFProtection::validate();
		return add_comment($val);
	}
    if($listing_id){
        $listing = marketplace_get_listing($listing_id)[0];
        if(marketplace_get_listing($listing_id)[0]){
            $listing = marketplace_get_listing($listing_id)[0];
        }
        else {
            return MyError::error404();
        }
        marketplace_view_listing($listing_id);
        $listing_images = marketplace_get_listing_images($listing_id);
        $num_listing_images = count($listing_images);
        $app->setTitle($listing['title']);
        set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => get_setting("site_title", "crea8socialPRO").' - '.$listing['title'], 'description' => $listing['description'], 'image' => $listing['image'] ? url_img($listing['image'], 200) : img('marketplace::images/no_image.jpg', 75), 'keywords' => $listing['tags']));
        $description = trim($listing['description']) == '' ? '<em>'.lang('marketplace::no-description').'</em>' : $listing['description'];
        $price = is_numeric($listing['price']) ? config('currency', '$').$listing['price'] : $listing['price'];
        $price = trim($listing['price']) == '' ? '<div class="listing-price listing-price-free listing-detail">'.lang('marketplace::free').'</div>' : '<div class="listing-price listing-price-paid listing-detail">'.$price.'</div>';
        $address = trim($listing['address']) == '' ? '<em>'.lang('marketplace::no-address').'</em>' : $listing['address'];
        $avatar = is_loggedIn() ? get_avatar(75) : null;
		$entityId = is_loggedIn() ? get_userid() : null;
		$entityType = 'user';
		$num_listing_comments = marketplace_get_num_listing_comments($listing_id);
        return $app->render(view('marketplace::listing', array('listing' => $listing, 'description' => $description, 'price' => $price, 'address' => $address, 'listing_images' => $listing_images, 'num_listing_images' => $num_listing_images, 'entityType' => $entityType, 'entityId' => $entityId, 'avatar' => $avatar, 'num_listing_comments' => $num_listing_comments, 'message' => $message)));
    }
}


function listing_slug_pager(){
    $path = (isset(parse_url($_SERVER['REQUEST_URI'])['path']) && parse_url($_SERVER['REQUEST_URI'])['path'] != '/') ? parse_url($_SERVER['REQUEST_URI'])['path'] : null;
    if(preg_match('/\/listing\/(.*?)(\/|$|\?|#)/i', $path)) {
        preg_match('/\/listing\/(.*?)(\/|$|\?|#)/i', $path, $matches);

        $_GET['id'] = marketplace_get_slug_id($matches[1], 'listing');
        return listing_pager(app());
    }
}