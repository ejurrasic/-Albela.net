<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("videos-manager")->setActive();

function videos_pager($app) {
    $term = input('term');
    $category = input('category');
    $limit = input('limit', 10);
    $videos = get_all_videos($category, $term, $limit);
    $app->setTitle(lang('video::videos'));
    return $app->render(view('video::admincp/lists', array('videos' => $videos)));
}

function videos_manage_pager($app) {
    $app->setTitle(lang('video::videos-manager'));
    $action = input('action');
    $id  = input('id');
    $video = get_video($id);
    if (!$video) return redirect_to_pager('admincp-video-pager');
    $backUrl = (isset($_POST['back_url'])) ? $_POST['back_url'] : $_SERVER['HTTP_REFERER'];
    switch($action) {
        case 'edit':
            $val = input('val');
            if ($val) {
		CSRFProtection::validate();
                save_video($val, $video);
                return redirect($backUrl);
            }
            return $app->render(view('video::admincp/edit', array('video' => $video, 'url' => $backUrl)));
            break;
        case 'delete':
            delete_video($video['id']);
            return redirect($backUrl);
            break;
    }
}
function categories_pager($app) {
    $app->setTitle(lang('video::video-categories'));
    $categories = (input('id')) ? get_video_parent_categories(input('id')): get_video_categories();
    return $app->render(view('video::admincp/categories/list', array('categories' => $categories)));
}

function manage_categories_pager($app) {
    $action = input('action');
    $app->setTitle(lang('video::video-categories'));
    switch($action) {
        case 'order':
            $id = input('id');
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_video_category_order($ids[$i], $i, $id);
            }
            break;
        case 'edit':
            $category = get_video_category(input('id'));
            $val = input('val');
            if ($val) {
		CSRFProtection::validate();
                save_video_category($val, $category);
                $url = ($category['parent_id']) ? url('admincp/videos/categories?id='.$category['parent_id']) : url('admincp/videos/categories');
                redirect($url);
            }
            return $app->render(view('video::admincp/categories/edit', array('category' => $category)));
            break;
        case 'delete':
            $category = get_video_category(input('id'));
            $url = ($category['parent_id']) ? url('admincp/videos/categories?id='.$category['parent_id']) : url('admincp/videos/categories');
            delete_video_category($category);
            redirect($url);
            break;
    }
}

function add_categories_pager($app) {
    $app->setTitle(lang('add-category'));
    $message = null;
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        video_add_category($val);
        /**
         * @var $category
         */
        extract($val);
        $url = ($category) ? url('admincp/videos/categories?id='.$category) : url('admincp/videos/categories');
        return redirect(url($url));
    }
    return $app->render(view('video::admincp/categories/add', array('message' => $message)));
}