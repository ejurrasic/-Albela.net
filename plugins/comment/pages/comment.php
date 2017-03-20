<?php
load_functions("comment::comment");
function comment_add_pager($app) {
    CSRFProtection::validate(false);
    $val = input('val');
    return add_comment($val);
}
function comment_delete_pager($app) {
    CSRFProtection::validate(false);
    $delete = delete_comment(input('id'));
    if ($delete) return 1;
    return '0';
}

function comment_save_pager($app) {
    CSRFProtection::validate(false);
    $save = save_comment(input('text'), input('id'));
    if ($save) {
        $comment = find_comment(input('id'));
        echo $comment['text'];
    } else {
        echo '0';
    }
}

function comment_more_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $typeId = input('type_id');
    $offset = input('offset');
    $limit = input('limit');

    $newOffset = $offset + $limit;
    $comments = get_comments($type, $typeId, $limit, $offset);
    $commentContent = '';
    foreach($comments as $comment) {
        $commentContent .= view('comment::display', array('comment' => $comment));
    }
    return json_encode(array(
        'offset' => $newOffset,
        'comments' => $commentContent
    ));
}

function load_replies_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    return view('comment::replies', array('comments' => get_comments('comment', $id, config('comment-replies-limit', 3))));
}