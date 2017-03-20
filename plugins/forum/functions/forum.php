<?php
function forum_get_categories(){
    $db = db();
    $categories = $db->query("SELECT * FROM forum_categories ORDER BY id");
    return fetch_all($categories);
}

function forum_is_category_exist($category_id){
    $db = db();
    if($db->query("SELECT id FROM forum_categories WHERE id = ".mysqli_real_escape_string(db(), $category_id))->num_rows == 0){
        return false;
    }
    else{
        return true;
    }
}

function forum_get_category($category_id){
    $db = db();
    $category = $db->query("SELECT id, title FROM forum_categories WHERE id = ".mysqli_real_escape_string(db(), $category_id));
    if($category->num_rows > 0){
        return fetch_all($category);
    }
    else{
        return false;
    }
}

function forum_get_tags(){
    $db = db();
    $tags = $db->query("SELECT * FROM forum_tags ORDER BY title");
    return fetch_all($tags);
}

function forum_get_tag_names(){
    $tags = forum_get_tags();
    $tag_names = array();
    foreach($tags as $tag){
        $tag_names[] = $tag['title'];
    }
    return $tag_names;
}

function forum_is_tag_exist($tag_id){
    $db = db();
    if($db->query("SELECT id FROM forum_tags WHERE id = ".mysqli_real_escape_string(db(), $tag_id))->num_rows == 0){
        return false;
    }
    else{
        return true;
    }
}

function forum_get_tag($tag){
    $db = db();
    $tag = $db->query("SELECT id, title, color FROM forum_tags WHERE id = '".mysqli_real_escape_string(db(), $tag)."'");
    if($tag->num_rows > 0){
        return fetch_all($tag);
    }
    else{
        return false;
    }
}

function forum_get_replies($thread_id, $page, $limit){
    $db = db();
    $query = "SELECT id, post, replier_id, thread_id, date FROM forum_replies WHERE replied_id = 0 AND thread_id = ".mysqli_real_escape_string(db(), $thread_id);
	$replies = paginate($query, $limit);
    return $replies;
}

function forum_get_sub_replies($thread_id, $replied_id, $page, $limit){
    $db = db();
    $order = config('forum-sub-replies-order', 'DESC');
    $query = "SELECT id, post, replier_id, thread_id, date FROM forum_replies WHERE replied_id = ".mysqli_real_escape_string(db(), $replied_id)."  AND thread_id = ".mysqli_real_escape_string(db(), $thread_id)." ORDER BY date ".$order;
	$sub_replies = paginate($query, $limit);
    return $sub_replies;
    }

function forum_get_num_sub_replies($thread_id, $replied_id){
    $db = db();
    return $db->query("SELECT COUNT(id) FROM forum_replies WHERE replied_id = ".mysqli_real_escape_string(db(), $replied_id)."  AND thread_id = ".mysqli_real_escape_string(db(), $thread_id))->fetch_row()[0];
    }

function forum_is_reply_exist($reply_id){
    $db = db();
    if($db->query("SELECT id FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $reply_id))->num_rows == 0){
        return false;
    }
    else{
        return true;
    }
}

function forum_get_reply($reply_id){
    $db = db();
    $reply = $db->query("SELECT id, post, replier_id, thread_id, date FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $reply_id));
    return fetch_all($reply);
}

function forum_get_threads($category_id, $tag, $search, $order, $page, $limit){
    $db = db();
    $category_id_sql = $category_id ? ' AND forum_threads.category_id = '.mysqli_real_escape_string(db(), $category_id) : '';
    $tag_sql = $tag ? " AND forum_threads.tags LIKE '% ".mysqli_real_escape_string(db(), $tag)." %'" : '';
    $search_sql = $search ? " AND forum_threads.subject LIKE '%".mysqli_real_escape_string(db(), $search)."%'" : '';
    $top_sql = $order == 't' ? ' AND UNIX_TIMESTAMP(forum_viewing_threads.last_viewed) > '.mysqli_real_escape_string(db(), (time() - 86400)) : '';
    $featured_sql = $order == 'ft' ? ' AND forum_threads.featured = 1' : '';
    $followed_sql = $order == 'f' && is_loggedIn() ? ' AND forum_followed_threads.follower_id = '.mysqli_real_escape_string(db(), get_userid()) : '';
    $where_sql = $category_id_sql.$tag_sql.$top_sql.$featured_sql.$followed_sql.$search_sql;
    switch($order){
        case 'l':
            $order_sql = 'forum_threads.last_replied DESC';
			$from_sql = 'FROM forum_threads';
			$left_join_sql = '';
        break;

        case 't':
        	$order_sql = 'forum_threads.last_viewed DESC';
			$from_sql = 'FROM forum_threads';
			$left_join_sql = '';
        break;

        case 'f':
        	$order_sql = 'forum_threads.last_replied DESC';
			$from_sql = 'FROM forum_followed_threads';
			$left_join_sql = 'LEFT JOIN forum_threads ON forum_followed_threads.thread_id = forum_threads.id';
        break;

        default:
            $order_sql = 'forum_threads.date DESC';
			$from_sql = 'FROM forum_threads';
			$left_join_sql = '';
        break;
    }
	$query = "
		SELECT DISTINCT forum_threads.id, forum_threads.subject, forum_threads.date, forum_threads.last_modified, forum_threads.category_id, forum_threads.tags, forum_threads.op_id, forum_threads.nov, forum_threads.nov, forum_threads.last_replied, forum_threads.pinned, forum_threads.nor, forum_threads.hidden, forum_threads.active, forum_threads.closed, forum_categories.title, op.username AS op_username, op.avatar AS op_avatar, rp.username AS rp_username, rp.avatar AS rp_avatar
		{$from_sql}
		{$left_join_sql}
		LEFT JOIN users op
		ON forum_threads.op_id = op.id
		LEFT JOIN users rp
		ON forum_threads.rp_id = rp.id
		LEFT JOIN forum_categories
		ON forum_threads.category_id = forum_categories.id
		LEFT JOIN forum_viewing_threads
		ON forum_viewing_threads.thread_id = forum_threads.id
		LEFT JOIN forum_replies
		ON forum_replies.thread_id = forum_threads.id
		WHERE forum_threads.hidden = 0 AND forum_threads.active = 1 {$where_sql}
		ORDER BY {$order_sql}";
	$threads = paginate($query, $limit);
	$pinned_threads = array();
    for($i = 0; $i < count($threads->results()); $i++){
		if($threads->results()[$i]['pinned'] == 1 && $category_id && $page <= 1){
			$pinned_threads[$i] = $threads->results()[$i]; 
		}
    }
	foreach($pinned_threads as $key => $value){
		unset($threads->results()[$key]);
		array_unshift($threads->results(), $value);
	}
    return $threads;
}

function forum_is_thread_exist($thread_id){
    $db = db();
    if($db->query("SELECT id FROM forum_threads WHERE id = ".mysqli_real_escape_string(db(), $thread_id))->num_rows == 0){
        return false;
    }
    else{
        return true;
    }
}

function forum_get_thread($thread_id){
    $db = db();
    $thread = $db->query("SELECT * FROM forum_threads WHERE id = ".mysqli_real_escape_string(db(), $thread_id));
    if($thread->num_rows > 0){
        return fetch_all($thread);
    }
    else{
        return false;
    }
}

function forum_get_subject($thread_id){
    $db = db();
    return $db->query("SELECT subject FROM forum_threads WHERE id = ".mysqli_real_escape_string(db(), $thread_id))->fetch_row()[0];
}

function forum_get_op_id($thread_id){
    $db = db();
    return $db->query("SELECT op_id FROM forum_threads WHERE id = ".mysqli_real_escape_string(db(), $thread_id))->fetch_row()[0];
}

function forum_get_post($post_id){
    if(!is_loggedIn()){return false;}
    $db = db();
    return $db->query("SELECT post FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $post_id)." AND replier_id = ".mysqli_real_escape_string(db(), get_userid()))->fetch_row()[0];
}

function forum_is_original_post($post_id){
    $db = db();
    $thread_id = $db->query("SELECT thread_id FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $post_id))->fetch_row()[0];
    $original_post_id = $db->query("SELECT id FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id)." LIMIT 0, 1")->fetch_row()[0];
    if($post_id == $original_post_id) {
        return true;
    }
    else {
        return false;
    }
}

function forum_get_num_likes($reply_id, $str = false){
    $db = db();
    $num_likes = $db->query("SELECT COUNT(id) FROM forum_likes WHERE reply_id = ".mysqli_real_escape_string(db(), $reply_id));
    $num_likes = $num_likes->fetch_row()[0];
	if($str){
		$num_likes_str = $num_likes > 1 ? $num_likes.' likes' : $num_likes.' like';
		$num_likes_str = $num_likes > 0 ? $num_likes_str : '';
		$num_likes = $num_likes_str;
	}
	return $num_likes;
}

function forum_reply_isliked($reply_id){
    $db = db();
	$likes = $db->query("SELECT id FROM forum_likes WHERE reply_id = ".mysqli_real_escape_string(db(), $reply_id)." AND liker_id = ".mysqli_real_escape_string(db(), get_userid()));
	if($likes->num_rows == 0){
		return false;
	}
	else{
		return true;
	}
}

function forum_like($reply_id){
    $db = db();
	if(!forum_reply_isliked($reply_id)){
		$db->query("INSERT INTO forum_likes (reply_id, liker_id) VALUES(".mysqli_real_escape_string(db(), $reply_id).", ".mysqli_real_escape_string(db(), get_userid()).")");
        fire_hook('forum.like', null, array($type = 'forum.like.post', $type_id = $reply_id, $text = forum_get_post($reply_id)));
	}
}

function forum_unlike($reply_id){
    $db = db();
	$db->query("DELETE FROM forum_likes WHERE reply_id = ".mysqli_real_escape_string(db(), $reply_id)." AND liker_id = ".mysqli_real_escape_string(db(), get_userid()));
}

function forum_get_thread_followers($thread_id){
    $db = db();
	$thread_followers = $db->query('SELECT follower_id FROM forum_followed_threads WHERE thread_id = '.mysqli_real_escape_string(db(), $thread_id));
    return fetch_all($thread_followers);
 }

function forum_get_poster_id($reply_id){
    $db = db();
    $poster_id = $db->query("SELECT replier_id FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $reply_id));
    return $poster_id->fetch_row()[0];
 }

function forum_get_thread_id($reply_id){
    $db = db();
    $thread_id = $db->query("SELECT thread_id FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $reply_id));
    return $thread_id->fetch_row()[0];
 }

function forum_thread_isfollowed($thread_id){
    $db = db();
	$followed_threads = $db->query("SELECT id FROM forum_followed_threads WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id)." AND follower_id = ".mysqli_real_escape_string(db(), get_userid()));
	if($followed_threads->num_rows == 0){
		return false;
	}
	else{
		return true;
	}
}

function forum_thread_follow($thread_id){
    $db = db();
	$nor = $db->query("SELECT nor FROM forum_threads WHERE id = ".mysqli_real_escape_string(db(), $thread_id));
	$nor = $nor->fetch_row()[0];
	if(!forum_thread_isfollowed($thread_id)){
		$db->query("INSERT INTO forum_followed_threads (thread_id, follower_id, last_check_nor) VALUES(".mysqli_real_escape_string(db(), $thread_id).", ".mysqli_real_escape_string(db(), get_userid()).", ".mysqli_real_escape_string(db(), $nor).")");
	}
}

function forum_thread_unfollow($thread_id){
    $db = db();
	$db->query("DELETE FROM forum_followed_threads WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id)." AND follower_id = ".mysqli_real_escape_string(db(), get_userid()));
}

function forum_followed_count(){
    $db = db();
	$followed_threads_count = $db->query("SELECT COUNT(forum_followed_threads.id) FROM forum_followed_threads LEFT JOIN forum_threads ON forum_followed_threads.thread_id = forum_threads.id WHERE forum_followed_threads.follower_id = ".mysqli_real_escape_string(db(), get_userid())." AND forum_threads.nor > forum_followed_threads.last_check_nor")->fetch_row()[0];
	$num_followed_threads = ($followed_threads_count == 0) ? NULL : ' ('.$followed_threads_count.') ';
	return $num_followed_threads;
}

function forum_thread_followed_count($thread_id){
    $db = db();
    if(is_loggedIn()){
        $followed_thread_count = $db->query("SELECT COUNT(forum_followed_threads.id) FROM forum_followed_threads LEFT JOIN forum_threads ON forum_followed_threads.thread_id = forum_threads.id WHERE forum_followed_threads.follower_id = ".mysqli_real_escape_string(db(), get_userid())." AND forum_followed_threads.thread_id = ".mysqli_real_escape_string(db(), $thread_id)." AND forum_threads.nor > forum_followed_threads.last_check_nor")->fetch_row()[0];
        $num_followed_thread = ($followed_thread_count == 0) ? NULL : ' ('.$followed_thread_count.') ';
        return $num_followed_thread;
    }
}

function forum_view_thread($thread_id){
	$db = db();
	if(is_loggedIn()){
		$last_viewed = $db->query("SELECT last_viewed FROM forum_viewing_threads WHERE viewer_id = ".get_userid()." AND thread_id = ".mysqli_real_escape_string(db(), $thread_id));
		if($last_viewed->num_rows > 0){
			$db->query("UPDATE forum_viewing_threads SET last_viewed = '".date('Y-m-d H:i:s')."' WHERE viewer_id = ".mysqli_real_escape_string(db(), get_userid())." AND thread_id = ".mysqli_real_escape_string(db(), $thread_id));
			$db->query("UPDATE forum_threads SET last_viewed = '".date("Y-m-d H:i:s")."' WHERE id = ".mysqli_real_escape_string(db(), $thread_id));
		}
		else{
			$db->query("INSERT INTO forum_viewing_threads (viewer_id, ip, thread_id, last_viewed, bot) VALUES (".mysqli_real_escape_string(db(), get_userid()).", '".mysqli_real_escape_string(db(), $_SERVER['REMOTE_ADDR'])."', ".mysqli_real_escape_string(db(), $thread_id).", '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."', 0)");
			$nov = $db->query("SELECT COUNT(id) FROM forum_viewing_threads WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id));
			$nov = $nov->fetch_row()[0];
			$db->query("UPDATE forum_threads SET last_viewed = '".mysqli_real_escape_string(db(), date("Y-m-d H:i:s"))."', nov = ".mysqli_real_escape_string(db(), $nov)." WHERE id = ".mysqli_real_escape_string(db(), $thread_id));
		}
		$nor = $db->query("SELECT nor FROM forum_threads WHERE id = ".mysqli_real_escape_string(db(), $thread_id));
		$nor = $nor->fetch_row()[0];
		if(forum_thread_isfollowed($thread_id)){
			$db->query("UPDATE forum_followed_threads SET last_check_nor = ".mysqli_real_escape_string(db(), $nor)." WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id)." AND follower_id = ".mysqli_real_escape_string(db(), get_userid()));
		}
	}
	else{
		$bot = (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])) ? 1 : 0;
		$last_viewed = $db->query("SELECT last_viewed FROM forum_viewing_threads WHERE ip = '".mysqli_real_escape_string(db(), $_SERVER['REMOTE_ADDR'])."' AND thread_id = ".mysqli_real_escape_string(db(), $thread_id));
		if($last_viewed->num_rows > 0){
			$db->query("UPDATE forum_viewing_threads SET last_viewed = '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."' WHERE ip = '".mysqli_real_escape_string(db(), $_SERVER['REMOTE_ADDR'])."' AND thread_id = ".mysqli_real_escape_string(db(), $thread_id));
			$db->query("UPDATE forum_threads SET last_viewed = '".mysqli_real_escape_string(db(), date("Y-m-d H:i:s"))."' WHERE id = ".mysqli_real_escape_string(db(), $thread_id));
		}
		else{
			$db->query("INSERT INTO forum_viewing_threads (viewer_id, ip, thread_id, last_viewed, bot) VALUES (NULL, '".mysqli_real_escape_string(db(), $_SERVER['REMOTE_ADDR'])."', ".mysqli_real_escape_string(db(), $thread_id).", '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."', ".mysqli_real_escape_string(db(), $bot).")");
			$nov = $db->query("SELECT COUNT(id) FROM forum_viewing_threads WHERE thread_id = ".$thread_id);
			$nov = $nov->fetch_row()[0];
			$db->query("UPDATE forum_threads SET last_viewed = '".mysqli_real_escape_string(db(), date("Y-m-d H:i:s"))."', nov = ".mysqli_real_escape_string(db(), $nov)." WHERE id = ".mysqli_real_escape_string(db(), $thread_id));
		}
	}
}

function forum_get_post_page_info($post_id){
	$db = db();
	$super_length = config('pagination-length-thread', 20);
	$sub_length = config('pagination-length-sub-replies', 4);
	$reply = $db->query("SELECT id, thread_id, replied_id, date FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $post_id))->fetch_assoc();
    if(!$reply){
        return false;
    }
	if($reply['replied_id'] == 0){
		$super_date = $reply['date'];
		$sub_date = null;
		$super_position = $db->query("SELECT COUNT(id) FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $reply['thread_id'])." AND date <= '".mysqli_real_escape_string(db(), $super_date)."' AND replied_id = 0")->fetch_row()[0];
		$sub_position = null;
		$super_total_records = $db->query("SELECT COUNT(id) FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $reply['thread_id'])." AND replied_id = 0")->fetch_row()[0];
		$sub_total_records = null;
		$super_total_pages = ceil($super_total_records / $super_length);
		$sub_total_pages = null;
		$super_page = ceil($super_position / $super_length);
		$sub_page = null;
	}
	else{
		$super_date = $db->query("SELECT date FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $reply['replied_id']))->fetch_row()[0];
		$sub_date = $reply['date'];
		$super_position = $db->query("SELECT COUNT(id) FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $reply['thread_id'])." AND date <= '".mysqli_real_escape_string(db(), $super_date)."' AND replied_id = 0")->fetch_row()[0];
		$sub_position = $db->query("SELECT COUNT(id) FROM forum_replies WHERE replied_id = ".mysqli_real_escape_string(db(), $reply['replied_id'])." AND date <= '".mysqli_real_escape_string(db(), $sub_date)."'")->fetch_row()[0];
		$super_total_records = $db->query("SELECT COUNT(id) FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $reply['thread_id'])." AND replied_id = 0")->fetch_row()[0];
		$sub_total_records = $db->query("SELECT COUNT(id) FROM forum_replies WHERE replied_id = ".mysqli_real_escape_string(db(), $reply['replied_id']))->fetch_row()[0];
		$super_total_pages = ceil($super_total_records / $super_length);
		$sub_total_pages = ceil($sub_total_records / $sub_length);
		$super_page = ceil($super_position / $super_length);
		$sub_page = ceil($sub_position / $sub_length);
	}
	return array(
		'thread_id' => $reply['thread_id'],
		'replied_id' => $reply['replied_id'],
		'super_page' => $super_page,
		'sub_page' => $sub_page
	);
}

function forum_execute_form($post_vars){
    $db = db();
    $type = isset($post_vars['type']) ? $post_vars['type'] : null;
    $errors = array();
    switch($type){
        case 'add_category':
			$expected = array('title' => '');
			extract(array_merge($expected, $post_vars));
			$titleSlug = "forum_category_".md5(time().serialize($post_vars)).'_title';
			foreach($title as $langId => $t){
				add_language_phrase($titleSlug, $t, $langId, 'forum');
			}
            $db->query("INSERT INTO forum_categories(title) VALUES('".mysqli_real_escape_string(db(), sanitizeText($titleSlug))."')");
            foreach($title as $langId => $t){
                (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'forum') : add_language_phrase($titleSlug, $t, $langId, 'forum');
            }
        break;

        case 'edit_category':
			$expected = array('title' => '');
			extract(array_merge($expected, $post_vars));
			$category = forum_get_category(sanitizeText($post_vars['category_id']))[0];
			$titleSlug = $category['title'];
			foreach($title as $langId => $t){
				(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'forum') : add_language_phrase($titleSlug, $t, $langId, 'forum');
			}
        break;

        case 'delete_category':
            $db = db();
			$category = forum_get_category($post_vars['category_id'])[0];
			delete_all_language_phrase($category['title']);
            $new_category_id = $post_vars['new_category_id'] == 'NULL' ? sanitizeText($post_vars['category_id']) : sanitizeText($post_vars['new_category_id']);
            $db->query("DELETE FROM forum_categories WHERE id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['category_id'])));
            $db->query("UPDATE forum_threads SET category_id = ".$new_category_id." WHERE category_id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['category_id'])));
        break;

        case 'add_tag':
            $db->query("INSERT INTO forum_tags(title, color) VALUES('".mysqli_real_escape_string(db(), sanitizeText($post_vars['title']))."', '".mysqli_real_escape_string(db(), sanitizeText($post_vars['color']))."')");
        break;

        case 'edit_tag':
            $db->query("UPDATE forum_tags SET title = '".mysqli_real_escape_string(db(), sanitizeText($post_vars['title']))."', color = '".mysqli_real_escape_string(db(), sanitizeText($post_vars['color']))."' WHERE id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['tag_id'])));
        break;

        case 'delete_tag':
            $db->query("DELETE FROM forum_tags WHERE id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['tag_id'])));
        break;

        case 'create_thread':
			$tags = explode(',', $post_vars['tags']);
			$tag_ids = ' ';
			foreach($tags as $tag){
				$tag_id = $db->query("SELECT id FROM forum_tags WHERE title = '".mysqli_real_escape_string(db(), trim($tag))."'");
				if($tag_id->num_rows > 0){
				$tag_ids .= $tag_id->fetch_row()[0].' ';
				}
			}
			$create_thread = $db->query("BEGIN WORK;");
			if($create_thread){
				$threads = $db->query("INSERT INTO forum_threads(subject, date, last_modified, category_id, tags, op_id, rp_id, last_replied) VALUES('".mysqli_real_escape_string(db(), sanitizeText($post_vars['subject']))."', '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."', '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."', ".mysqli_real_escape_string(db(), sanitizeText($post_vars['category_id'])).", '".mysqli_real_escape_string(db(), $tag_ids)."', ".mysqli_real_escape_string(db(), get_userid()).", ".mysqli_real_escape_string(db(), get_userid()).", '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."')");
				if($threads){
					$thread_id = $db->insert_id;
					$replies = $db->query("INSERT INTO forum_replies(post, date, last_modified, thread_id, replied_id, replier_id) VALUES('".mysqli_real_escape_string(db(), lawedContent(stripslashes($post_vars['postbox'])))."', '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."', '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."', ".mysqli_real_escape_string(db(), $thread_id).", 0, ".mysqli_real_escape_string(db(), get_userid()).")");
					if($replies){
						$follow_thread = $db->query("INSERT INTO forum_followed_threads(thread_id, follower_id) VALUES(".mysqli_real_escape_string(db(), $thread_id).", ".mysqli_real_escape_string(db(), get_userid()).")");
						if($follow_thread){
							$create_thread = $db->query("COMMIT;");
							fire_hook('forum.create', null, array($type = 'forum.create', $type_id = $thread_id, $text = $post_vars['subject']));
							return $thread_id;
							}
						else{
							$create_thread = $db->query("ROLLBACK;");
						}
					}
					else{
						$create_thread = $db->query("ROLLBACK;");
					}
				}
				else{
					$create_thread = $db->query("ROLLBACK;");
				}
			}
        break;

        case 'edit_thread':
            $tags = explode(',', $post_vars['tags']);
            $tag_ids = ' ';
            foreach($tags as $tag){
                $tag_id = $db->query("SELECT id FROM forum_tags WHERE title = '".mysqli_real_escape_string(db(), trim($tag))."'");
                if($tag_id->num_rows > 0){
                    $tag_ids .= $tag_id->fetch_row()[0].' ';
                }
            }
            $db->query("UPDATE forum_threads SET subject = '".mysqli_real_escape_string(db(), sanitizeText($post_vars['subject']))."', category_id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['category_id'])).", tags = '".mysqli_real_escape_string(db(), $tag_ids)."', pinned = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['pinned'])).", hidden = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['hidden'])).", active = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['active'])).", closed = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['closed'])).", featured = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['featured']))." WHERE id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['thread_id'])));
        break;

        case 'modify_thread':
            $thread = forum_get_thread($post_vars['id'])[0];
            if(get_userid() != $thread['op_id']) {
                return false;
            }
            $tags = explode(',', $post_vars['tags']);
            $tag_ids = ' ';
            foreach($tags as $tag){
                $tag_id = $db->query("SELECT id FROM forum_tags WHERE title = '".mysqli_real_escape_string(db(), trim($tag))."'");
                if($tag_id->num_rows > 0){
                    $tag_ids .= $tag_id->fetch_row()[0].' ';
                }
            }
            $db->query("UPDATE forum_threads SET subject = '".mysqli_real_escape_string(db(), sanitizeText($post_vars['subject']))."', category_id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['category_id'])).", tags = '".mysqli_real_escape_string(db(), $tag_ids)."' WHERE id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['id'])));
        break;

        case 'delete_thread':
			forum_delete_thread($post_vars['thread_id']);
        break;

        case 'reply_thread':
            $db->query("INSERT INTO forum_replies(post, date, last_modified, thread_id, replied_id, replier_id, hidden) VALUES('".mysqli_real_escape_string(db(), lawedContent(stripslashes($post_vars['postbox'])))."', '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."', '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."', ".mysqli_real_escape_string(db(), sanitizeText($post_vars['thread_id'])).", ".mysqli_real_escape_string(db(), sanitizeText($post_vars['id'])).", ".mysqli_real_escape_string(db(), get_userid()).", 0)");
			$reply_id = $db->insert_id;
			$nor = $db->query("SELECT COUNT(id) FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $post_vars['thread_id']));
			$nor = $nor->fetch_row()[0];
			$db->query("UPDATE forum_threads SET rp_id = ".mysqli_real_escape_string(db(), get_userid()).", nor = ".mysqli_real_escape_string(db(), ($nor - 1)).", last_replied = '".mysqli_real_escape_string(db(), date('Y-m-d H:i:s'))."' WHERE id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['thread_id'])));
			fire_hook('forum.reply', null, array($type = 'forum.reply.thread', $type_id = $reply_id, $text = $post_vars['postbox']));
			if($post_vars['id'] > 0){
				fire_hook('forum.reply', null, array($type = 'forum.reply.post', $type_id = $reply_id, $text = $post_vars['postbox']));
			}
			return $reply_id;
        break;

        case 'edit_post':
			$db->query("UPDATE forum_replies SET post = '".mysqli_real_escape_string(db(), lawedContent(stripslashes($post_vars['postbox'])))."' WHERE id = ".mysqli_real_escape_string(db(), sanitizeText($post_vars['id'])));
			//fire_hook('forum.reply', null, array($type = 'forum.reply.edit', $type_id = $post_vars['id'], $text = $post_vars['postbox']));
            return $post_vars['id'];
        break;

        case 'delete_post':
            forum_delete_reply($post_vars['id']);
			//fire_hook('forum.reply', null, array($type = 'forum.reply.delete', $type_id = $post_vars['id'], $text = $post_vars['postbox']));
            return $post_vars['id'];
        break;

        default:
            return false;
            break;
    }
}

function forum_assign_get_var($url, $var, $val){
    $scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
    $host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
    $path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
    $query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
    $fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
    $variables = array();
    if(!is_null($query)){
        parse_str($query, $variables);
    }
    $variables[$var] = $val;
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return $scheme.$s.$host.$path.$q.http_build_query($variables).$h.$fragment;
}

function forum_remove_get_var($url, $var){
    $scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
    $host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
    $path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
    $query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
    $fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
    $variables = array();
    if(!is_null($query)){
        parse_str($query, $variables);
    }
    if(isset($variables[$var])){
        unset($variables[$var]);
    }
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return $scheme.$s.$host.$path.$q.http_build_query($variables).$h.$fragment;
}

function forum_timelength($s, $trim = false){
    if($s < 60){
        $stxt = ($s < 2) ? 'second' : 'seconds';
        return $trim ? $s.' '.$stxt : $s.' '.$stxt;
    }
    elseif($s < 3600){
        $m = floor($s / 60);
        $s = $s % 60;
        $mtxt = ($m < 2) ? 'minute' : 'minutes';
        $stxt = ($s < 2) ? 'second' : 'seconds';
        return $trim ? $m.' '.$mtxt : $m.' '.$mtxt.', '.$s.' '.$stxt;
    }
    elseif($s < 86400){
        $h = floor($s / 60 / 60);
        $m = ($s / 60) % 60;
        $s = $s % 60;
        $htxt = ($h < 2) ? 'hour' : 'hours';
        $mtxt = ($m < 2) ? 'minute' : 'minutes';
        $stxt = ($s < 2) ? 'second' : 'seconds';
        return $trim ? $h.' '.$htxt : $h.' '.$htxt.', '.$m.' '.$mtxt.', '.$s.' '.$stxt;
    }
    elseif($s < 604800){
        $d = floor($s / 60 / 60 / 24);
        $h = ($s / 60 / 60) % 24;
        $m = ($s / 60) % 60;
        $s = $s % 60;
        $dtxt = ($d < 2) ? 'day' : 'days';
        $htxt = ($h < 2) ? 'hour' : 'hours';
        $mtxt = ($m < 2) ? 'minute' : 'minutes';
        $stxt = ($s < 2) ? 'second' : 'seconds';
        return $trim ? $d.' '.$dtxt : $d.' '.$dtxt.', '.$h.' '.$htxt.', '.$m.' '.$mtxt.', '.$s.' '.$stxt;
    }
    elseif($s < 31449600){
        $w = floor($s / 60 / 60 / 24 / 7);
        $d = ($s / 60 / 60 / 24) % 7;
        $h = ($s / 60 / 60) % 24;
        $m = ($s / 60) % 60;
        $s = $s % 60;
        $wtxt = ($w < 2) ? 'week' : 'weeks';
        $dtxt = ($d < 2) ? 'day' : 'days';
        $htxt = ($h < 2) ? 'hour' : 'hours';
        $mtxt = ($m < 2) ? 'minute' : 'minutes';
        $stxt = ($s < 2) ? 'second' : 'seconds';
        return $trim ? $w.' '.$wtxt : $w.' '.$wtxt.', '.$d.' '.$dtxt.', '.$h.' '.$htxt.', '.$m.' '.$mtxt.', '.$s.' '.$stxt;
    }
    else{
        $y = floor($s / 60 / 60 / 24 / 7 / 52);
        $w = ($s / 60 / 60 / 24 / 7) % 52;
        $d = ($s / 60 / 60 / 24) % 7;
        $h = ($s / 60 / 60) % 24;
        $m = ($s / 60) % 60;
        $s = $s % 60;
        $ytxt = ($y < 2) ? 'year' : 'years';
        $wtxt = ($w < 2) ? 'week' : 'weeks';
        $dtxt = ($d < 2) ? 'day' : 'days';
        $htxt = ($h < 2) ? 'hour' : 'hours';
        $mtxt = ($m < 2) ? 'minute' : 'minutes';
        $stxt = ($s < 2) ? 'second' : 'seconds';
        return $trim ? $y.' '.$ytxt : $y.' '.$ytxt.', '.$w.' '.$wtxt.', '.$d.' '.$dtxt.', '.$h.' '.$htxt.', '.$m.' '.$mtxt.', '.$s.' '.$stxt;
    }
}

function forum_get_user_column($user_id, $column){
    $db = db();
    $user = $db->query("SELECT ".mysqli_real_escape_string(db(), $column)." FROM users WHERE id = ".mysqli_real_escape_string(db(), $user_id));
    return $user->fetch_row()[0];
}

function forum_get_last_replier($thread_id){
    $db = db();
    $user = $db->query("SELECT replier_id FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id)." ORDER BY date DESC LIMIT 1");
    return fetch_all($user);
}

function forum_delete_thread($thread_id){
	$db = db();
    $db->query("DELETE FROM forum_threads WHERE id = ".mysqli_real_escape_string(db(), $thread_id));
    $db->query("DELETE FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id));
    $db->query("DELETE FROM forum_followed_threads WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id));
    $db->query("DELETE FROM forum_viewing_threads WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id));
	$obsolete_replies =  db()->query("SELECT id FROM forum_replies WHERE thread_id = ".mysqli_real_escape_string(db(), $thread_id));
    while($obsolete_reply = $obsolete_replies->fetch_assoc()) {
        forum_delete_reply($obsolete_reply['id']);
    }
}

function forum_delete_reply($reply_id){
	$db = db();
    $db->query("DELETE FROM forum_replies WHERE id = ".mysqli_real_escape_string(db(), $reply_id));
    $db->query("DELETE FROM forum_likes WHERE reply_id = ".mysqli_real_escape_string(db(), $reply_id));
}

function forum_num_threads(){
    $db = db();
    $num_threads = $db->query("SELECT COUNT(id) FROM forum_threads");
    if($db->error){
        return 0;
    }
    else{
        return $num_threads->fetch_row()[0];
    }
}

function forum_get_avatar($user_id, $size, $username = null) {
    $db = db();
    $user_id = ($username) ? $db->query("SELECT id FROM users WHERE username = '".mysqli_real_escape_string(db(), $username)."'")->fetch_row()[0] : $user_id;
    $user = $db->query("SELECT gender, avatar FROM users WHERE id = ".mysqli_real_escape_string(db(), $user_id))->fetch_assoc();
    if(!$user) return false;
    $avatar = $user['avatar'];
    if ($avatar) {
        return url(str_replace('%w', $size, $avatar));
    }
    else {
        $gender = (isset($user['gender']) and $user['gender']) ? $user['gender'] : null;
        return ($gender) ? img("images/avatar/{$gender}.png") : img("images/avatar.png");
    }
}

function forum_output_text($content) {
    if (is_rtl($content)) {
        $content = "<span style='direction: rtl;text-align: right;display: block'>{$content}</span>";
    }
    return nl2br($content);
}

function forum_slugger($str) {
    return trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $str)), '-');
}

function forum_get_thread_slug_link($thread_id, $page = null) {
    return url('forum/thread/'.$thread_id.'/'.forum_slugger(forum_get_subject($thread_id)).'/'.$page);
}

function forum_get_forum_slug_link($url) {
    $scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
    $host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
    $path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
    $query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
    $fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
    $variables = array();
    if(!is_null($query)){
        parse_str($query, $variables);
    }
    $category = null;
    $tag = null;
    $order = null;
    $search = null;
    $page = null;
    if(isset($variables['c'])) {
        $category = '/category/'.$variables['c'].'/'.forum_slugger(lang(forum_get_category($variables['c'])[0]['title']));
        unset($variables['c']);
    }
    if(isset($variables['t'])) {
        $tag = '/tag/'.$variables['t'].'/'.forum_slugger(forum_get_tag($variables['t'])[0]['title']);
        unset($variables['t']);
    }
    if(isset($variables['o'])) {
        switch($variables['o']){
            case 'l': $order = '/latest'; break;
            case 't': $order = '/top'; break;
            case 'ft': $order = '/featured'; break;
            case 'f': $order = '/followed'; break;
            default: $order = '/new'; break;
        }
        unset($variables['o']);
    }
    if(isset($variables['s'])) {
        $search = '/search/'.$variables['s'];
        unset($variables['s']);
    }

    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return $scheme.$s.$host.rtrim($path, '/').$category.$tag.$order.$search.$q.http_build_query($variables).$h.$fragment;
}