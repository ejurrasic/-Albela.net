<?php
function post_boost_create($val, $result, $admin = false) {

    $validator = validator($val, array(
        'name' => 'required|unique:post_boost',
        'plan_id' => 'required',
        'link' => 'required'
    ));

    $expected = array(
        'status' => 0,
        'paid' => 0,
        'activate' => 0,
        'page_id' => 0
    );

    /**
     * @var $name
     * @var $link
     * @var $type
     * @var $plan_id
     * @var $country
     * @var $gender
     * @var $activate
     * @var $status
     * @var $enable
     * @var $paid
     */
    extract(array_merge($expected, $val));
    if (validation_passes()) {
        $plan = get_plan($plan_id);
        $quantity = $plan['quantity'];
        $user_id = get_userid();
        $location = serialize($country);

        $time = time();
        $name = sanitizeText($name);
        $post_id = sanitizeText($link);
        $ad_type = sanitizeText($type);
        $plan_id = sanitizeText($plan_id);
        $gender = sanitizeText($gender);

        if($booster_type == 'feed'){

            $ad_type = 'Post';
            db()->query("INSERT INTO post_boost (time,name,user_id,post_id,type,plan_type,plan_id,quantity,target_location,target_gender)VALUES(
            '{$time}','{$name}','{$user_id}','{$post_id}','{$ad_type}','{$type}','{$plan_id}','{$quantity}','{$location}','{$gender}'
        )");

            $result['message'] = lang('booster::post-boost-created-success');
        }else{
            $ad_type = 'Listing';
            db()->query("INSERT INTO post_boost (time,name,user_id,post_id,type,plan_type,plan_id,quantity,target_location,target_gender)VALUES(
            '{$time}','{$name}','{$user_id}','{$post_id}','{$ad_type}','{$type}','{$plan_id}','{$quantity}','{$location}','{$gender}'
        )");
            $result['message'] = lang('booster::listing-boost-created-success');
        }


        $adsId = db()->insert_id;

        if (is_admin()) {
            db()->query("UPDATE `post_boost` SET status='{$status}',paid='{$paid}' WHERE ads_id='{$adsId}'");
        }
        fire_hook('boost.created', null, array(db()->insert_id, $val));
        $result['status']  = 1;


        $result['link'] = ($activate) ? url_to_pager('pb_activate', array('id' => $adsId)) : url_to_pager('manage-boost');
        if ($admin) $result['link'] = url_to_pager('admin-ads-list');
        return json_encode($result);


    } else {

        $result['message'] = validation_first();
        return json_encode($result);

    }
}

function update_booster($val){
    $validator = validator($val, array(
        'name' => 'required'
    ));
    extract($val);
    /**
     * @var $country
     * @var $refresh
     * @var $plan_id
     * @var $quantity
     * @var $name
     * @var $link
     * @var $paid
     * @var $status
     * @var $gender
     * @var $pb_id
     */

    $location = serialize($country);
    if($refresh == 1){
        $plan = get_plan($plan_id);
        $q = $plan['quantity'];
    }else{
        $q = $quantity;
    }
    if(validation_passes()){
       $sql = "UPDATE `post_boost` SET `name`='{$name}',`post_id`='{$link}',`paid`='{$paid}',`status`='{$status}',`quantity`=$q,target_gender='{$gender}',`target_location`='{$location}' WHERE `pb_id`='{$pb_id}'";
        db()->query($sql);
     return redirect(url('admincp/booster/manage'));
    }
}

function find_pb($id){
    $sql = "SELECT * FROM `post_boost` WHERE `pb_id`='{$id}'";
    $result = db()->query($sql);
    return $result->fetch_assoc();
}

function activate_page_boost($bp) {
    $id = $bp['pb_id'];
    $plan = get_plan('plan_id');
    $quantity = $plan['quantity'];
    return db()->query("UPDATE post_boost SET paid='1', status='1',quantity= quantity + {$quantity} WHERE pb_id='{$id}'");
}

function get_boosted_posts(){
    $userid = get_userid();
    $sql = "SELECT * FROM `post_boost` WHERE `user_id`='{$userid}' ORDER BY `pb_id` DESC";
    $result = db()->query($sql);
    $posts = array();
    while($r = $result->fetch_assoc()){
        $posts[] = $r;
    }

  return $posts;

}

function get_render_boosted_post($type = 'all', $limit) {
    $fields = "pb_id,post_id,type,plan_type,plan_id,quantity,views,impression_stats";
    $sql = "SELECT {$fields} FROM `post_boost` WHERE pb_id!='' ";
    if (is_loggedIn()) {
        $country = get_user_data('country');
        $gender = get_user_data('gender');
        $sql .= " AND (target_location LIKE '%{$country}%' AND (target_gender ='all' or target_gender='{$gender}')) ";
    }

    $qLimit = config('ads-quantity-deduction-per-impression', 5);
    $sql .= " AND quantity>={$qLimit} AND paid='1' AND status='1' ORDER BY rand() LIMIT {$limit}";
    $q = db()->query($sql);
    $result = array();
    while($fetch = $q->fetch_assoc()) {
       $views = $fetch['views'] + 1;
        $impressions = $fetch['impression_stats'];
        $quantity = $fetch['quantity'];
        //print_r($fetch['impression_stats']); die();
        if (is_loggedIn()) {
            $userImpressions = get_privacy('boost-impressions', array());
            if (!in_array($fetch['pb_id'], $userImpressions)) {
                $impressions +=1;
                $userImpressions[] = $fetch['pb_id'];
                if ($fetch['plan_type'] == 2) {
                    $quantity -= config('ads-quantity-deduction-per-impression', 5);
                }
                save_privacy_settings(array('boost-impressions' => $userImpressions));
            }
        }
        $adsId = $fetch['pb_id'];

        db()->query("UPDATE `post_boost` SET views='{$views}',impression_stats='{$impressions}',quantity='{$quantity}' WHERE pb_id='{$adsId}'");
        $result[] = $fetch;
    }

    return $result;
}

function count_user_boost_total($type) {
    $sql = "";
    switch($type) {
        case 'impressions':
            $sql = "SELECT SUM(impression_stats) as size FROM `post_boost` ";
            break;
        case 'views':
            $sql = "SELECT SUM(views) as size FROM `post_boost` ";
            break;
        case 'clicks':
            $sql = "SELECT SUM(clicks) as size FROM `post_boost` ";
            break;
    }
    $userid = get_userid();
    $sql .= " WHERE user_id='{$userid}'";
    $q = db()->query($sql);
    $fetch = $q->fetch_assoc();
    return $fetch['size'];
}

function get_all_boosted_posts($limit = 10){
    $sql = "SELECT * FROM `post_boost`";
  return  paginate($sql,$limit);
}

function count_all_boosted_posts(){
    $sql = "SELECT COUNT(*) as c FROM `post_boost`";
    $r = db()->query($sql);
    $r = $r->fetch_assoc();
    return $r['c'];
}
function boost_ids(){
    $bp = get_render_boosted_post('all',10);
$boosted_ids = array();
    foreach($bp as $b){

        $boosted_ids[] = $b['post_id'];
    }
 return $boosted_ids;
}
function delete_boost($id){
    $user = get_userid();
    $sql = "DELETE FROM `post_boost` WHERE `pb_id`='{$id}' AND `user_id`='{$user}'";
    db()->query($sql);
}

function get_boosted_listings(){
    $sql = "SELECT `post_id` FROM `post_boost` WHERE `type` = 'Listing'";
    $result = db()->query($sql);
    $posts = array();
    while($r = $result->fetch_assoc()){
        $posts[] = $r['post_id'];
    }

    return $posts;
}