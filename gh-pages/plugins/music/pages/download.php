<?php
function download_pager($app) {
    $id = segment(2);
    $music = get_music($id);
    $artist = str_replace(' ', '-', $music['artist']);
    $number = $music['slug'];
    $filename = $artist."-".$number;
    if($music) {
        $file = fire_hook('filter.url', url($music['file_path']));
        header("Content-type: octet/stream");
        header("Content-disposition: attachment; filename=".$filename.".mp3;");
        try {
            $head = array_change_key_case(get_headers($file, TRUE));
            $filesize = $head['content-length'];
            header("Content-Length: ".$filesize);
        } catch (Exception $e) {

        }
        readfile($file);
        exit;
    } else {
        return MyError::error404();
    }
}
