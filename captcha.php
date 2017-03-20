<?php
// Set the header
header("Content-type: image/jpeg");
session_start();

$letters = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
$word = "";
for($i = 0; $i <= 4; $i++) {
    $word .= $letters[rand(0, count($letters) - 1)];
}
$text = $word;

$_SESSION['sv_captcha'] = $text;
$w = 90;
$h = 35;

$image = imagecreate($w, $h);

imagecolorallocate($image, 108, 173, 234);
$white = imagecolorallocate($image, 255, 255, 255);

// Set the font size
$font_size = 60;

$pixel_color = imagecolorallocate($image, 0,0,255);
for($i=0;$i<1000;$i++) {
    imagesetpixel($image,rand()%200,rand()%50,$pixel_color);
}
imagestring($image, $font_size, 15, 10, $text, $white);
imagejpeg($image, null, 100); 