<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-blogs')->setActive();
function lists_pager($app) {
    $app->setTitle('Manage Blogs');
    return $app->render(view('blog::lists', array('blogs' => admin_get_blogs(input('term')))));
}


function manage_pager($app) {
    $action = input('action', 'order');
    $app->setTitle(lang('blog::manage-blogs'));
    switch($action) {
        case 'delete':
            $id = input('id');
            delete_blog($id);
            return redirect_back();
            break;
        case 'edit':
            $id = input('id');
            $blog = get_blog($id);
            if (!$blog) return redirect_back();
            $val = input('val', null, array('content'));
            if ($val) {
	    	CSRFProtection::validate();
                save_blog($val, $blog, true);

                return redirect_to_pager('admincp-blogs');

            }
            return $app->render(view('blog::edit', array('blog' => $blog)));
            break;
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_help_category_order($ids[$i], $i);
            }
            break;
    }
}

function add_pager($app) {
    $app->setTitle(lang('blog::add-new-blog'));
    $message = null;
    $val = input('val', null, array('content'));
    if ($val) {
		CSRFProtection::validate();
        $validate = validator($val, array(
            'title' => 'required',
            'content' => 'required'
        ));

        if (validation_passes()) {
            $slug = toAscii(input('val.title'));
            if (empty($slug)) $slug = md5(time().get_userid());
            $val['slug'] = $slug;

            add_blog($val);


            return redirect_to_pager('admincp-blogs');
        } else{
            $message = validation_first();
        }
    }
    return $app->render(view('blog::add', array('message' => $message)));
}

function categories_pager($app) {
    $app->setTitle(lang('blog::manage-categories'));

    return $app->render(view('blog::categories/lists', array('categories' => get_blog_categories())));
}

function categories_add_pager($app) {
    $app->setTitle(lang('blog::add-category'));
    $message = null;

    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        blog_add_category($val);
        return redirect_to_pager('admincp-blog-categories');
        //redirect to category lists
    }

    return $app->render(view('blog::categories/add', array('message' => $message)));
}

function manage_category_pager($app) {
    $action = input('action', 'order');
    $id = input('id');
    switch($action) {
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_blog_category_order($ids[$i], $i);
            }
            break;
        case 'edit':
            $message = null;
            $image = null;
            $val = input('val');
            $app->setTitle(lang('blog::edit-category'));
            $category = get_blog_category($id);
            if (!$category) return redirect_to_pager('admincp-blog-categories');
            if ($val) {
		CSRFProtection::validate();
                $file = input_file('file');

                save_blog_category($val, $category);
                return redirect_to_pager('admincp-blog-categories');
                //redirect to category lists
            }
            return $app->render(view('blog::categories/edit', array('message' => $message, 'category' => $category)));
            break;
        case 'delete':
            $category = get_blog_category($id);
            if (!$category) return redirect_to_pager('admincp-blog-categories');
            delete_blog_category($id, $category);
            return redirect_to_pager('admincp-blog-categories');
            break;
    }
    return $app->render();
}