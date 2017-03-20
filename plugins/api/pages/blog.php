<?php
function browse_pager($app) {
    $category = input('category', 'all');
    $term = input('term');
    $type = input('type', 'browse');
    $filter = input('filter', 'all');
    $limit = input("limit", 10);
    $blogs = get_blogs($type, $category, $term, null, $limit, $filter);

    $result = array(
        'categories' => array(
            array('id' => 'all', 'title' => lang('all-categories'))
        ),
        'blogs' => array(),
    );

    foreach(get_blog_categories() as $category) {
        $result['categories'][] = array(
            'id' => $category['id'],
            'title' => lang($category['title'])
        );
    }

    foreach($blogs->results() as $blog) {
        $result['blogs'][]  = api_arrange_blog($blog);
    }

    return json_encode($result);
}

function view_pager($app) {
    $blogId = input("blog_id");
    $blog = get_blog($blogId);
    return json_encode(api_arrange_blog($blog));
}