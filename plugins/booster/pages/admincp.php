<?php

get_menu("admin-menu", "Boosted-Post")->setActive();

function manage_boost_pager($app)
{

    $app->setTitle(lang('booster::boosted-post'));
    // $app->setTitle(lang('booster::manage-boosted'));
    $posts = get_all_boosted_posts();
    return $app->render(view('booster::admincp/manage', array('posts' => $posts)));


}

function activate_pager($app)
{
    $app->setTitle(lang('booster::activated-boosted'));
    $pb_id = segment(3);

    $boost_post = find_pb($pb_id);
    $val = input('val') ? input('val') : null;
    if (!empty($val)) {
        $val['pb_id'] = $pb_id;
        update_booster($val);
    }

    if (!empty($boost_post)) {
       $boost_type = $boost_post['type'];

        $id = $boost_post['post_id'];

        switch($boost_type){
            case 'Post':
                $content = 'mumu';
                $feed = find_feed($id);
                if(!empty($feed)){
                    $content = view('booster::admincp/the_post', array('feed' => $feed));
                    return $app->render(view('booster::admincp/activate', array('content' => $content, 'boost' => $boost_post)));
                }else{
                    $listing = marketplace_get_listing($id);
                    $content = view('booster::listing',array('listing'=>$listing[0]));
                    return $app->render(view('booster::admincp/activate', array('content' => $content, 'boost' => $boost_post)));
                }

                break;
            case 'Listing':
                $listing = marketplace_get_listing($id);
                if(!empty($listing)){
                    $content = view('booster::listing',array('listing'=>$listing[0]));
                    return $app->render(view('booster::admincp/activate', array('content' => $content, 'boost' => $boost_post)));
                }else{ //maybe it is feed
                    $feed = find_feed($id);
                    if(!empty($feed)){
                        $content = view('booster::admincp/the_post', array('feed' => $feed));
                        return $app->render(view('booster::admincp/activate', array('content' => $content, 'boost' => $boost_post)));
                    }
                }
                break;
        }

    }


}

function admin_delete_pb($app)
{
    echo $id = input('id');
    if (!empty($id)) {
        $sql = "DELETE FROM `post_boost` WHERE `pb_id`='{$id}'";
        db()->query($sql);
    }
    //$user = get_userid();
}