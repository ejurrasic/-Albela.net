<?php
function browse_pager($app) {
    $category = input('category_id');
    $search = input("term");
    $type = input("type", "browse");
    $page = input("page");
    $limit = input("limit", 10);
    $filter = input('filter', 'all');
    $pages = get_pages($type, $search, $limit, $category, $filter);
    $result = array(
        'categories' => array(
            array(
                'id' => 'all',
                'title' => lang('all-categories')
            )
        ),
        'pages' => array()
    );

    foreach(get_page_categories() as $category) {
        $result['categories'][] = array(
            'id' => $category['category_id'],
            'title' => lang($category['category_title'])
        );
    }

    foreach($pages->results() as $page) {
        $result['pages'][] = api_arrange_page($page);
    }

    return json_encode($result);
}

function get_categories_pager($app) {
    $result = array();
    foreach(get_page_categories() as $category) {
        $result[] = array(
            'id' => $category['category_id'],
            'title' => lang($category['category_title']),
        );
    }

    return json_encode($result);
}

function create_pager($app) {

    $title = input('title');
    $description = input('description');
    $category = input('category');
    $slug = toAscii($title);
    if (empty($slug)) $slug = md5(time());
    $val = array(
        'name' => $title,
        'description' => $description,
        'page_url' => $slug,
        'category' => $category
    );

    $pageId = page_add($val);
    $page = find_page($pageId);
    $page = api_arrange_page($page);
    $page = array_merge(array('status' => 1), $page);
    return json_encode($page);
}

function edit_pager($app) {

}

function delete_pager($app) {

}

function like_pager($app) {
    like_item("page", input('page_id'), input('action'));
    return json_encode(array('status' => 1));
}

function cover_pager($app) {
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    $pageId = input('page_id');
    $page = find_page($pageId);
    if (!$page) return json_encode($result);
    if (!is_page_admin($page)  and !is_page_editor($page)) return json_encode($result);

    if (input_file('image')) {
        $uploader = new Uploader(input_file('image'), 'image');
        $uploader->setPath($page['page_id'].'/'.date('Y').'/photos/cover/');
        if ($uploader->passed()) {
            $original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->toDB("page", $page['page_id'])->result();


            //delete the old resized cover
            if ($page['page_cover_resized']) {
                delete_file(path($page['page_cover_resized']));
            }

            //lets now crop this image for the resized cover
            $uploader->setPath($page['page_id'].'/'.date('Y').'/photos/cover/resized/');
            $cover = $uploader->crop(0,  0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
            $result['data_one'] = url_img($cover);
            $result['original'] = url_img($original);
            $result['id'] = $uploader->insertedId;
            update_page_details(array('page_cover' => $original, 'page_cover_resized' => $cover), $page['page_id']);
            $result['status'] = 1;
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function logo_pager($app) {
    $pageId = input('page_id');
    $page = find_page($pageId);
    $result = array(
        'status' => 0,
        'message' => lang('page::page-permission-error'),
        'image' => ''
    );
    if (!$page) return json_encode($result);
    if (!is_page_admin($page)  and !is_page_editor($page)) return json_encode($result);

    if (input_file('image')) {
        $uploader = new Uploader(input_file('image'), 'image');
        $uploader->setPath($page['page_id'].'/'.date('Y').'/photos/logo/');
        if ($uploader->passed()) {
            $image = $uploader->resize()->toDB("page-logo", $page['page_id'])->result();

            update_page_details(array('page_logo' => $image), $page['page_id']);
            fire_hook('page.logo.updated', null, array($page['page_id'], $uploader->insertedId, $image));
            $result['status'] = 1;
            $result['data_one'] = url_img($image, 200);
            $result['id'] = $uploader->insertedId;
            $result['large'] = url_img($image, 920);
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}