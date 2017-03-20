<?php
register_hook("user.signup", function($user) {
    require(path("plugins/letteravatar/library/gd-text/src/Color.php"));
    require(path("plugins/letteravatar/library/gd-text/src/Box.php"));
    require(path("plugins/letteravatar/library/letter/src/ColorPalette.php"));
    require(path("plugins/letteravatar/library/letter/src/LetterAvatar.php"));
    $userid = $user[0];
    $letterAvatar = new LetterAvatar\LetterAvatar;
    $savePath = "storage/avatar/";
    $path = path($savePath);
    if (!is_dir(path($savePath))) {
        mkdir($path, 0777, true);
    }

    $savePath = $savePath."{$userid}_avatar".time().".png";
    $user = find_user($userid);
    if($user['avatar']) return $user;
    $setting = config('letteravatar-type', '0');
    if ($setting == '0') {
        $letter = get_user_name($user);
        $ratio = 0.8;
    } elseif($setting == 2) {
        $first = substr($user['first_name'], 0, 1);
        $second = substr($user['last_name'], 0, 1);
        if (!$second) $second = substr($user['first_name'], 1,1);
        $l = $first.$second;
        $letter = array($l);
        $ratio = 0.5;
    }
    else {
        $letter = ($user['gender'] == 'male') ? 'M' : 'F';
        $ratio = 0.8;
    }
    $letterAvatar->setFontRatio($ratio)->generate($letter, 240)->saveAsPng(path($savePath));
    db()->query("UPDATE users SET avatar='{$savePath}' WHERE id='{$userid}'");

    return $user;
});