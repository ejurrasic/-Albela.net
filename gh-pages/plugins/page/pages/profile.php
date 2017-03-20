<?php
function page_profile_pager($app) {
    get_menu("page-profile", 'timeline')->setActive();
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => get_page_details('page_title'), 'description' => get_page_details('page_desc'), 'image' => get_page_details('page_logo') ? url_img(get_page_details('page_logo'), 200) : '', 'keywords' => ''));
    return $app->render(view('page::profile/timeline', array('feeds' => get_feeds('page', $app->profilePage['page_id']))));
}

function page_about_profile_pager($app) {
    get_menu("page-profile", 'about')->setActive();
    $type = input('type', 'general');

    //register the about menus
    add_menu('page-profile-about', array('title' => lang('general'), 'link' => page_url('about', $app->profilePage), 'id' => 'general'));
    foreach(get_custom_field_categories('page') as $category) {
        add_menu('page-profile-about', array('title' => lang($category['title']), 'link' => page_url('about?id='.$category['id'].'&type=custom', $app->profilePage), 'id' => 'field-'.$category['id']));
    }

    //allow plugins to hook in
    fire_hook('page-profile-about', null, array($app));
    switch($type) {
        case 'general' :
            get_menu("page-profile-about", "general")->setActive();
            $content = view('page::profile/about/general');
            break;
        case 'custom':
            $id = input('id');
            $category = get_custom_field_category($id);
            if (!$category) return redirect(page_url("about"));
            get_menu("page-profile-about", "field-".$id)->setActive();
            $content = view("page::profile/about/field", array('id' => $id));
            break;
        default:
            $content = fire_hook('page-profile-about', '', array($type, $app));
            break;
    }
    return $app->render(view('page::profile/about/layout', array('content' => $content)));
}

function page_photos_profile_pager($app) {
    get_menu("page-profile", 'photos')->setActive();
    return $app->render(view('page::profile/photos', array('photos' => get_photos($app->profilePage['page_id'], 'page'))));
}

function upload_cover_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    $pageId = input('id');
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
            $result['image'] = url_img($cover);
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

function reposition_cover_pager($app) {
    CSRFProtection::validate(false);
    $pos = input('pos');
    $width = input('width', 623);
    $pageId = input('id');
    $page = find_page($pageId);
    if (!$page) return false;
    if (!is_page_admin($page)  and !is_page_editor($page)) return false;

    $cover = path($page['page_cover']);
    $uploader = new Uploader($cover, 'image', false , true);
    $uploader->setPath($page['page_id'].'/'.date('Y').'/photos/cover/resized/');
    $pos = abs($pos);
    $pos = ($pos / $width);
    $yCordinate = 0;
    $srcWidth = $uploader->getWidth();
    $srcHeight = $srcWidth * 0.4;
    if (!empty($pos) & $pos < $srcWidth) {
        $yCordinate = $pos  * $uploader->getWidth();
    }
    $cover = $uploader->crop(0,  $yCordinate, $srcWidth, $srcHeight)->result();

    //delete old resized image if available
    if ($page['page_cover_resized']) {
        delete_file(path($page['page_cover_resized']));
    }
    update_page_details(array('page_cover_resized' => $cover), $page['page_id']);
    return url_img($cover);
}

function remove_cover_pager($app) {
    CSRFProtection::validate(false);
    $pageId = input('id');
    $page = find_page($pageId);
    if (!$page) return false;
    if (!is_page_admin($page)  and !is_page_editor($page)) return false;

    delete_file(path($page['page_cover_resized']));

    update_page_details(array('page_cover' => '', 'page_cover_resized' => ''), $page['page_id']);
}

function change_logo_pager($app) {
    CSRFProtection::validate(false);
    $pageId = input('id');
    $page = find_page($pageId);
    $result = array(
        'status' => 0,
        'message' => lang('page::page-permission-error'),
        'image' => ''
    );
    if (!$page) return json_encode($result);
    if (!is_page_admin($page)  and !is_page_editor($page)) return json_encode($result);

    if (input_file('logo')) {
        $uploader = new Uploader(input_file('logo'), 'image');
        $uploader->setPath($page['page_id'].'/'.date('Y').'/photos/logo/');
        if ($uploader->passed()) {
            $image = $uploader->resize()->toDB("page-logo", $page['page_id'])->result();

            update_page_details(array('page_logo' => $image), $page['page_id']);
            fire_hook('page.logo.updated', null, array($page['page_id'], $uploader->insertedId, $image));
            $result['status'] = 1;
            $result['image'] = url_img($image, 200);
            $result['id'] = $uploader->insertedId;
            $result['large'] = url_img($image, 920);
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}