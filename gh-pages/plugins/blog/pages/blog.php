<?php
function blog_pager($app) {
    $app->setTitle(lang('blog::blogs'));
    $type = input('type', 'all');
    $category = input('category');
    $term = input('term');
    $filter = input('filter', 'all');
    return $app->render(view('blog::lists', array('blogs' => get_blogs($type, $category, $term, null, 10, $filter))));
}

function blog_page_pager($app) {
    $slug = segment(1);
    $blog = get_blog($slug);

    if (!$blog or (!$blog['status'] and !is_blog_owner($blog))) return redirect(url('blogs'));
    $app->blog = $blog;
    if ($blog['status']) db()->query("UPDATE blogs SET views = views + 1 WHERE slug='{$slug}'");

    //exit(strip_tags($blog['content']));
    $app->setTitle($blog['title'])
        ->setKeywords($blog['tags'])
        ->setDescription(str_limit(strip_tags($blog['content']), 100));
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $blog['title'], 'description' => str_limit(strip_tags($blog['content']), 100), 'image' => $blog['image'] ? url_img($blog['image'], 200) : '', 'keywords' => $blog['tags']));
    return $app->render(view('blog::view', array('blog' => $blog)));

}

function manage_pager($app) {
    $action = input('type');
    $app->setTitle(lang('blog::manage-blogs'));
    $id = input('id');
    $blog = get_blog($id);
    if (!is_blog_owner($blog)) redirect(url('blogs'));

    switch($action) {
        case 'delete':

            delete_blog($id);
            return redirect(url('blogs?type=mine'));
            break;
        case 'edit':
            $id = input('id');
            $blog = get_blog($id);
            $val = input('val', null, array('content'));


            $message = null;
            if ($val) {
                CSRFProtection::validate();
                $validate = validator($val, array(
                    'title' => 'required',
                    'content' => 'required'
                ));
                if (validation_passes()) {
                    save_blog($val, $blog);

                    return redirect(url('blog/'.$blog['slug']));
                } else {
                    $message = validation_first();
                }

            }
            return $app->render(view('blog::edit', array('blog' => $blog, 'message' => $message)));
            break;

    }
}

function add_blog_pager($app) {
    $app->setTitle(lang('blog::add-new-blog'));
    $message = null;
    $val = input('val', null, array('content'));
    if (!user_has_permission('can-create-blog') or !config('allow-members-create-blog', true)) return redirect(url('blogs'));
    if ($val) {
		CSRFProtection::validate();
        $validate = validator($val, array(
            'title' => 'required',
            'content' => 'required'
        ));

        if (validation_passes()) {
            $slug = toAscii(input('val.title'));
            if (empty($slug) || blog_slug_exists($slug)) $slug = md5(time().get_userid());
            $val['slug'] = $slug;

            add_blog($val);


            return redirect(url('blogs?type=mine'));
        } else{
            $message = validation_first();
        }
    }
    return $app->render(view("blog::add", array('message' => $message)));
}

function blog_api_pager($app) {
    $blogs = get_blogs(true);
    $b = array();
    foreach($blogs->results() as $blog) {
        $b[] = $blog;
    }

    return json_encode($b);
}