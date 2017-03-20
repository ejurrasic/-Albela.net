<?php
function people_get_users($filter, $limit) {
    $db = db();
    extract(array_merge(array('gender' => 'both', 'from_age' => 'any', 'to_age' => 'any', 'country' => 'any', 'keywords' => '', 'online_status' => 'both', 'feature' => 'both'), $filter));
    $words = explode(' ', trim($keywords));
    $from_age = is_numeric($from_age) ? $from_age : 0;
    $to_age = is_numeric($to_age) ? $to_age : 99;
    $min_age = ($from_age <= $to_age) ? $from_age : $to_age;
    $max_age = ($min_age == $from_age) ? $to_age : $from_age;
    $min_date = date('Y', (time() - ($min_age * 31570560))).'-'.date('m').'-'.date('d');
    $max_date = date('Y', (time() - (($max_age + 1) * 31570560))).'-'.date('m').'-'.date('d');
    $min_year = date('Y', (time() - ($min_age * 31570560)));
    $max_year = date('Y', (time() - (($max_age + 1) * 31570560)));
    $online_operator = $online_status == 'online' ? '>' : '<';
    $feature_operator = $feature == 'featured' ? '=' : "!=";
    $where_sql = '';
    foreach($words as $word) {
        $where_sql .= "(username LIKE '%".mysqli_real_escape_string(db(), $word)."%' OR first_name LIKE '%".mysqli_real_escape_string(db(), $word)."%' OR last_name LIKE '%".mysqli_real_escape_string(db(), $word)."%' OR email_address LIKE '%".mysqli_real_escape_string(db(), $word)."%')";
    }
    $where_sql .= ($min_age == 0 && $max_age == 99) ? '' : " AND ((birth_year <= '".$min_year."') AND (birth_year >= '".$max_year."'))";
    $where_sql .= $gender == 'both' ? '' : " AND gender = '".mysqli_real_escape_string(db(), $gender)."'";
    $where_sql .= $country == 'any' ? '' : " AND country = '".mysqli_real_escape_string(db(), $country)."'";
    $where_sql .= $online_status == 'both' ? '' : " AND online_time ".$online_operator." ".(time() - 50);
    $where_sql .= $feature == 'both' ? '' : " AND featured ".$feature_operator." 1";
    $query = "SELECT id, username, first_name, last_name, gender, country, avatar, online_time, featured FROM users WHERE {$where_sql} ORDER BY join_date DESC";
    return paginate($query, $limit);
}