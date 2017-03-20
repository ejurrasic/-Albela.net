<?php
function browse_pager($app) {
    $categoryId = input("category_id");
    $search = input("term");
    $type = input("type");
    $page = input("page");
    $limit = input("limit", 10);
   $listings = marketplace_get_listings($categoryId, $search, $type, $page, $limit);

    $result = array(
        'categories' => array(),
        'listings' => array()
    );

    foreach(marketplace_get_categories() as $category) {
        $result['categories'][] = array(
            'id' => $category['id'],
            'title' => lang($category['title'])
        );
    }

    foreach($listings->results() as $listing) {
        $result['listings'][] = api_arrange_listing($listing);
    }

    return json_encode($result);
}

function get_categories_pager($app) {
    $result = array();
    foreach(marketplace_get_categories() as $category) {
        $result[] = array(
            'id' => $category['id'],
            'title' => lang($category['title']),
        );
    }

    return json_encode($result);
}

function marketplace_create_pager($app) {
    $val = array(
        'title' => input('title'),
        'description' => input('description'),
        'category_id' => input('category_id'),
        'tags' => input('tags'),
        'address' => input('address'),
        'link' => input('link'),
        'price' => input('price'),
        'type' => "create_listing"
    );
    $result = array(
        'status' => 0,
        'message' => ''
    );

    if ($val) {
        $image = input_file('image');
        $image_path = '';
        if ($image) {
            $uploader = new Uploader($image);
            if ($uploader->passed()) {
                $uploader->setPath('marketplace/listings/images/');
                $image_path = $uploader->resize()->result();
            } else {
                $result['message'] = $uploader->getError();
                return json_encode($result);
            }
        }
        $val['image_path'] = $image_path;

        $listing_id = marketplace_execute_form($val);

        $listing = marketplace_get_listing($listing_id)[0];
        $result['status'] = 1;
        $result = array_merge($result, api_arrange_listing($listing));
        return json_encode($result);
    }

    return json_encode($result);
}

function marketplace_edit_pager($app) {
    $listingId = input("listing_id");
    $listing = marketplace_get_listing($listingId);
    $val = array(
        'title' => input('title'),
        'description' => input('description'),
        'category_id' => $listing['category_id'],
        'tags' => input('tags'),
        'address' => input('address'),
        'link' => input('link'),
        'price' => input('price'),
        'type' => "edit_listing",
        'listing_id' => $listingId
    );
    $result = array(
        'status' => 0,
        'message' => ''
    );
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
                $result['message'] = $uploader->getError();
                return json_encode($result);
            }
        }
        $val['image_path'] = $image_path;
        marketplace_execute_form($val);
        $listing = marketplace_get_listing($listingId)[0];
        $result['status'] = 1;
        return json_encode(array_merge($result, api_arrange_listing($listing)));
    }

    return json_encode($result);
}

function marketplace_delete_pager($app) {
    $val = array(
        'type' => 'delete_listing',
        'listing_id' => input("listing_id")
    );
    marketplace_execute_form($val);
    return json_encode(array('status' => 1));
}

function marketplace_page_pager($app) {
    $listingId = input("listing_id");
    $listing = marketplace_get_listing($listingId);

    return json_encode(api_arrange_listing($listing));
}