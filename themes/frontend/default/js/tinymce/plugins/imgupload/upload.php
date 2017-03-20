<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!class_exists('finfo')) {
        exit('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body onload="top.alert(\'fileinfo is not installed\')"></body></html>');
    }
    function get_base($l)
    {
        $p = '';
        for ($i = 0; $i < $l; $i++) {
            $p .= '..'.DIRECTORY_SEPARATOR;
        }
        return realpath($p);
    }
    function server($name, $default = null) {
        if (isset($_SERVER[$name])) return $_SERVER[$name];
        return $default;
    }
    function get_base_url ($base_dir) {

        $filename = basename(server('SCRIPT_FILENAME'));
        if (basename(server('SCRIPT_NAME')) == $filename) {
            $baseUrl = server('SCRIPT_NAME');
        } elseif(basename(server('PHP_SELF')) == $filename) {
            $baseUrl = server('PHP_SELF');
        } elseif(basename(server('ORIG_SCRIPT_NAME')) == $filename) {
            $baseUrl = server('ORIG_SCRIPT_NAME');
        } else {
            $baseUrl = server('SCRIPT_NAME');
        }

        $path = "themes".DIRECTORY_SEPARATOR."frontend".DIRECTORY_SEPARATOR."default".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."tinymce".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."imgupload".DIRECTORY_SEPARATOR."upload.php";

        $baseUrl = str_replace($path, "", $baseUrl);
        $protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? 'https' : strtolower(explode('/', $_SERVER["SERVER_PROTOCOL"])[0]);
        $url = $protocol.'://'.$_SERVER["SERVER_NAME"].$baseUrl;
        return $url;
        /**$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? 'https' : strtolower(explode('/', $_SERVER["SERVER_PROTOCOL"])[0]);
        //$port = $_SERVER["SERVER_PORT"] == "80" ? '' : ':'.$_SERVER["SERVER_PORT"];
        $root_dir = str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
        $dirs = str_replace($root_dir, '', $base_dir);
        $url = $protocol.'://'.$_SERVER["SERVER_NAME"].str_replace(DIRECTORY_SEPARATOR, '/', $dirs);
        exit($url);
        return $url;**/
    }
    $config = include('config.php');
    $base_dir = get_base($config['base_diff']);
    $upload_path = $base_dir.DIRECTORY_SEPARATOR.$config['upload_path'];
    if (!is_dir($upload_path)) {
        try {
            mkdir($upload_path);
        } catch (Exception $e) {
            $errors[] = 'Permission to create upload directory denied by server';
        }
    }
    $path_info = pathinfo($_FILES['img']['name']);
    $dir = $path_info['dirname'];
    $file_name = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($path_info['filename'])));
    $ext = isset($path_info['extension']) ? $path_info['extension'] : 'jpg';
    $i = 0;
    while (file_exists($dir.DIRECTORY_SEPARATOR.$file_name.'-'.str_pad($i, 4, '0', STR_PAD_LEFT).'.'.$ext)) {
        $i++;
    }
    $path = rtrim($upload_path, '/').DIRECTORY_SEPARATOR.$file_name.'-'.str_pad($i, 4, '0', STR_PAD_LEFT).'.'.$ext;
    $url = get_base_url($base_dir).'/'.str_replace(DIRECTORY_SEPARATOR, '/', $config['upload_path']).'/'.$file_name.'-'.str_pad($i, 4, '0', STR_PAD_LEFT).'.'.$ext;
    $allowedtypes = explode(',', $config['allowed_types']);
    if (!is_uploaded_file($_FILES['img']['tmp_name'])) {
        $errors[] = 'No image was uploaded';
    }
    if (is_uploaded_file($_FILES['img']['tmp_name'])) {
        if (class_exists('finfo')) {
            $type = preg_replace('/^image\//', '', finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['img']['tmp_name']));
            if ((!in_array($type, $allowedtypes))) {
                $errors[] = 'Invalid Format ('.$type.')';
            }
        } else {
            echo 'fileinfo is not installed';
        }
    }
    if ($_FILES['img']['size'] > $config['max_size']) {
        if ($config['max_size'] < 1024) {
            $max_size = $config['max_size'].' Bytes';
        } elseif ($config['max_size'] < 1048576) {
            $max_size = round($config['max_size'] / 1024).' KB';
        } else {
            $max_size = round($config['max_size'] / 1048576, 1).' MB';
        }
        $errors[] = 'Maximum Size Allowed '.$max_size;
    }
    if (empty($errors)) {
            echo move_uploaded_file($_FILES['img']['tmp_name'], $path) ? '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body onload="top.tinymce.activeEditor.insertContent(\'<img src='.$url.' />\'); top.tinymce.activeEditor.windowManager.close();"></body></html>' : '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body onload="top.alert(\'Image was not uploaded successfully\')"></body></html>';
    } else {
        echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body onload="top.alert(\''.implode(' &bull; ', $errors).'\')"></body></html>';
    }
}