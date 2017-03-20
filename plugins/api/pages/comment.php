<?php
function get_api_comment($comment) {
    $likeCount = count_likes('comment', $comment['comment_id']);
    $likeCount = ($likeCount) ? $likeCount : 0;
    $dislikeCount = count_dislikes('comment', $comment['comment_id']);
    $dislikeCount = ($dislikeCount) ? $dislikeCount : 0;
   return  array(
        'id' => $comment['comment_id'],
        'entity_type' => $comment['entity_type'],
        'entity_id' => $comment['entity_id'],
        'name' => $comment['publisher']['name'],
        'avatar' => $comment['publisher']['avatar'],
        'text' => api_format_output_text($comment['text']),
        'image' => ($comment['image']) ? url_img($comment['image'], 600) : '',
        'time' => apiTimeAgo($comment['time']),
        'replies' => $comment['replies'],
        'has_dislike' => (has_disliked('comment', $comment['comment_id'])) ? true : false,
        'has_like' => (has_liked('comment', $comment['comment_id'])) ? true : false,
        'like_count' => $likeCount,
        'dislike_count' => $dislikeCount,
        'can_edit' => (can_edit_comment($comment)) ? true : false,
    );
}

function comment_load_pager($app) {
    $userid = input("userid");
    $type = input("type");
    $typeId = input('type_id');
    $limit = input("limit");
    $offset = input("offset", 0);
    $app->userid = $userid;
    api_temporary_login_user($userid);
    $comments = get_comments($type, $typeId, $limit, $offset);
    $result = array();
    foreach($comments as $comment) {
        $dComment = get_api_comment($comment);
        $result[] = $dComment;
    }

    return json_encode($result);
}

function comment_add_pager($app) {
    $userid = input("userid");
    $type = input("type");
    $type_id = input('type_id');
    $entity_id = input('entity_id');
    $entity_type = input('entity_type');
    $text = input("text");
    $app->userid = $userid;
    api_temporary_login_user($userid);

    if (!is_numeric($entity_id)) return false;
    $imagePath = "";
    $image = input_file('image');
    if ($image) {
        $uploader = new Uploader($image);
        if ($uploader->passed()) {
            $path = get_userid().'/'.date('Y').'/photos/comments/';
            $uploader->setPath($path);
            $imagePath = $uploader->resize()->toDB('posts')->result();
        } else {
            $result['status'] = 0;
            $result['message'] = $uploader->getError();
        }
    }

    $userid = get_userid();
    $time = time();
    $text = sanitizeText($text);
    $entity_id = sanitizeText($entity_id);
    $entity_type = sanitizeText($entity_type);
    $type_id = sanitizeText($type_id);

    $query = db()->query("INSERT INTO `comments` (user_id,entity_id,entity_type,type,type_id,text,image,time)VALUES(
        '{$userid}','{$entity_id}','{$entity_type}','{$type}','{$type_id}','{$text}','{$imagePath}','{$time}'
    )");

    if ($query) {
        $commentId = db()->insert_id;
        refresh_comment_cache($type, $type_id);
        add_subscriber($userid, $type, $type_id);
        if ($type != 'comment') add_subscriber($userid, 'comment', $commentId);
        fire_hook('comment.add', null, array($type, $type_id, $text));
        $comment = find_comment($commentId);
        return json_encode(get_api_comment($comment));
     }

}

function comment_remove_pager($app) {
    $userid = input("userid");
    $id = input("id");
    $app->userid = $userid;
    api_temporary_login_user($userid);
    $delete = delete_comment(input('id'));
    if ($delete) {
        return json_encode(array("status" => 1));
    }
    return json_encode(array("status" => 0));
}