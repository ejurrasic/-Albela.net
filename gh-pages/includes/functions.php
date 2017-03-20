<?php
/**
 * Error handler
 */
function php_error_handler($level, $message, $file, $line, $context) {
    if (error_reporting() and $level) {
        throw new ErrorException($message, 0, $level, $file, $line);
    }
}

/**
 * exception hander
 */
function php_exception_handler($e) {
    return MyError::handler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
}

/**
 * @return bool
 */
function php_fatal_error_handler() {
    $error = error_get_last();

    if ($error) {
        /**
         * @var $type
         * @var $message
         * @var $file
         * @var $line
         * @var $type
         */
        extract($error);
        if (!in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) return;
        //Error::handler($error['type'], $error['message'], $error['file'], $error['line'], "");
        return MyError::handler($type, $message, $file, $line);
    }
}

/**
 * Function to get the full url
 */
function getFullUrl($queryStr = false)
{
    $request = $_SERVER;
    $host = (isset($request['HTTP_HOST'])) ? $request['HTTP_HOST'] : $request['SERVER_NAME'];
    $isSecure = (isset($request['HTTPS']) and $request['HTTPS'] == "on") ? true : false;
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $queryString = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : null;
    $scheme = (App::getInstance()->sslEnabled()) ? "https://" : "http://";
    $fullUrl = $scheme.$host.$uri;
    return $fullUrl = ($queryStr) ? $fullUrl.$queryString : $fullUrl;
}

function isSecure() {
    return $isSecure = (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == "on") ? true : false;
}

function getScheme() {

    return (App::getInstance()->sslEnabled()) ? 'https' : 'http';
}

function getHost() {
    $request = $_SERVER;
    $host = (isset($request['HTTP_HOST'])) ? $request['HTTP_HOST'] : $request['SERVER_NAME'];

    //remove unwanted characters
    $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
    //prevent Dos attack
    if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
        die();
    }

    return $host;
}
function server($name, $default = null) {
    if (isset($_SERVER[$name])) return $_SERVER[$name];
    return $default;
}
function getRoot() {
    $base = getBase();

    return getScheme().'://'.getHost().$base;
}

function getBase() {
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

    $baseUrl = str_replace('index.php', '', $baseUrl);

    return $baseUrl;
}

/**
 * Function to get the request method
 * @return string
 */
function get_request_method()
{
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

/**
 * Method to get path
 */
function path($path = "") {
    return App::getInstance()->path($path);
}

/**
 * Function to get app instance
 */
function app() {
    return App::getInstance();
}

/**
 * Method to get path from plugin dir
 */
function plugin_path($plugin, $path = "") {
    return App::getInstance()->pluginPath($plugin, $path);
}

/**
 * Function to register pagers
 * @param string $pattern
 * @param array $parameters
 * @return boolean
 */
function register_post_pager($pattern, $parameters = array())
{
    return Pager::post($pattern, $parameters);
}

/**
 * @param $pattern
 * @param array $parameters
 * @return bool
 */
function register_get_pager($pattern, $parameters = array())
{
    return Pager::get($pattern, $parameters);
}

/**
 * @param $pattern
 * @param array $parameters
 * @return bool
 */
function register_pager($pattern, $parameters = array())
{
    return Pager::any($pattern, $parameters);
}

/**
 * Method to add filters
 * @param string $name
 * @param mixed $callable
 * @return boolean
 */
function register_filter($name, $callable) {
    return Pager::addFilter($name, $callable);
}

/**
 * Function to load html view file
 * @param string $view
 * @param array $param
 * @return string
 */
function view($view, $param = array()) {
    return App::getInstance()->view($view, $param);
}

/**
 * Method for setting meta tags
 */
function set_meta_tags($new_meta_tags) {
    return App::getInstance()->setMetaTags($new_meta_tags);
}

/**
 * Method for getting meta tags
 */
function get_meta_tags_array() {
    return App::getInstance()->getMetaTags();
}

/**
 * Method for rendering meta tags
 */
function render_meta_tags() {
    return App::getInstance()->renderMetaTags();
}

/**
 * Method to register assets css, js e.t.c
 */
function register_asset($asset, $themeType = "frontend") {
    return App::getInstance()->registerAsset($asset, $themeType);
}

/**
 * Method to render an assets
 */
function render_assets($type, $themeType = "frontend") {
    return App::getInstance()->renderAssets($type, $themeType);
}

/**
 * function to easily get the generated page title
 */
function get_title() {
    return App::getInstance()->title;
}

/**
 * Function to get image assets
 * @param string $path e.g images/file.png or pluginname:images/file.png
 * @param int $size
 * @return string
 */
function img($path, $size = null) {
    $path = ($size) ? str_replace('%w', $size, $path) : $path;
    return url(App::getInstance()->getAssetLink($path, false));
}

function asset_link($path) {
    return App::getInstance()->getAssetLink($path);
}

function url_img($path, $size = null) {
    $path = ($size) ? str_replace('%w', $size, $path) : $path;

    if (stripos('%d', $path) != -1) {
        if ($size < 200) {
            $size = 200;
        } elseif ($size <700) {
            $size = 600;
        } else {
            $size = 960;
        }
        $path = ($size) ? str_replace('%d', $size, $path) : $path;
    }
    $url = url($path);
    $url = fire_hook('filter.url', $url);
    return $url;
}

function video_url($path) {
    return url($path);
}

/**
 * Method to set page title
 * @param string $title
 */
function set_title($title = "") {
    return App::getInstance()->setTitle($title);
}

/**
 * Method to get the database instance
 */
function db() {
    return App::getInstance()->db();
}
function config($key, $default = null) {
    return App::getInstance()->config($key, $default);
}
/**
 * function to get translations
 * @param string $name
 * @param array $replace
 */
function lang($name, $replace = array(), $default = null) {
    return App::getInstance()->getTranslation($name, $replace, $default);
}

function _lang($name, $replace = array(), $default = null) {
    echo lang($name, $replace, $default);
}
/**
 * Function get user inputs from GET or POST Method
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function input($name, $default = "", $escape = true) {
    //if (!isset($_POST[$name]) and !isset($_GET[$name])) return $default;
    //for all admin lets escape be off
    //if (segment(0) == 'admincp') $escape = false;
    if ($name == "val" and get_request_method() != "POST") return false;
    $index = "";
    if (preg_match("#\.#", $name)) list($name, $index) = explode(".", $name);

    $result = (isset($_GET[$name])) ? $_GET[$name] : $default;
    $result = (isset($_POST[$name])) ? $_POST[$name] : $result;

    if (is_array($result)) {
        if (empty($index)) {
            $nR = array();
            foreach($result as $k => $v) {
                if (is_array($v)) {
                    $newResult = array();
                    foreach($v as $n => $a) {
                         $newResult[$n] =  ((!is_array($a) and $escape === true) || (is_array($escape) && !in_array($k, $escape))) ?  str_replace("\\\"", "\"", str_replace("\\n", "\n", str_replace("\\r", "\r", mysqli_real_escape_string(db(), sanitizeText($a))))) : str_replace("'", '&#39;', $a);
                    }
                    $nR[$k] = $newResult;
                } else {
                    $nR[$k] = ($escape === true || (is_array($escape) && !in_array($k, $escape))) ?  str_replace("\\\"", "\"", str_replace("\\n", "\n", str_replace("\\r", "\r", mysqli_real_escape_string(db(), sanitizeText($v))))) : str_replace("'", '&#39;', $v);
                }
            }
            $result = $nR;
        } else {
            if(!isset($result[$index])) return $default;
            if (is_array($result[$index])) {
                $newResult = array();
                foreach($result[$index] as $n => $v) {
                    $newResult[$n] = ((!is_array($v) and $escape === true) || (is_array($escape) && !in_array($index, $escape))) ? str_replace("\\\"", "\"", str_replace("\\n", "\n", str_replace("\\r", "\r", mysqli_real_escape_string(db(), sanitizeText($v))))) : str_replace("'", '&#39;', $v);
                }
                $result = $newResult;
            } else {
                $result = ((!is_array($result[$index]) and $escape === true) || (is_array($escape) && !in_array($index, $escape))) ?  str_replace("\\\"", "\"", str_replace("\\n", "\n", str_replace("\\r", "\r", mysqli_real_escape_string(db(), sanitizeText($result[$index]))))) : str_replace("'", '&#39;', $result[$index]);
            }

        }
    } else {
        $result = ((!is_array($result) and $escape === true) || (is_array($escape) && !in_array($name, $escape))) ?  str_replace("\\\"", "\"", str_replace("\\n", "\n", str_replace("\\r", "\r", mysqli_real_escape_string(db(), sanitizeText($result))))) : str_replace("'", '&#39;', $result);
    }

    return $result;
}

/**
 * Function get user file input
 * @param string $name
 * @return mixed
 */
function input_file($name) {
    if (isset($_FILES[$name])){
        if (is_array($_FILES[$name]['name'])) {
            $files = array();
            $index = 0;
            foreach($_FILES[$name]['name'] as $n) {
                if ($_FILES[$name]['name'] != 0) {
                    $files[] = array(
                        'name' => $n,
                        'type' => $_FILES[$name]['type'][$index],
                        'tmp_name' => $_FILES[$name]['tmp_name'][$index],
                        'error' => $_FILES[$name]['error'][$index],
                        'size' => $_FILES[$name]['size'][$index]
                    );
                }
                $index++;
            }

            if (empty($files)) return false;
            return $files;
        } else {
            if ($_FILES[$name]['size'] == 0) return false;
            return $_FILES[$name];
        }
    }
    return false;
}

/**
 * function to get if input has a value
 * @param string $name
 */
function input_has($name) {
    return input($name);
}

/**
 * Method to put data into the session
 * @param string $name
 * @param string $value
 * @return boolean
 */
function session_put($name, $value = "") {
    $_SESSION[$name] = $value;
    return true;
}
/**
 * Function to get value from a session
 * @param string $name
 * @param string $default
 * @return string
 */
function session_get($name, $default = false) {
    if (!isset($_SESSION[$name])) return $default;
    return $_SESSION[$name];
}
/**
 * Method to remove data from the session
 * @param string $name
 * @return boolean
 */
function session_forget($name) {
    if (isset($_SESSION[$name])) unset($_SESSION[$name]);
    return true;
}

/**
 * Function to redirect by link
 * @param string $url
 * @parapm array $flash array('id' => 'flash-message-id', 'message' => '')
 * @return mixed
 */
function redirect($url, $flash = array()) {
    add_flash($flash);
    @session_write_close();
    @session_regenerate_id(true);
    header("Location:".$url);
    exit;
}

/**
 * @param array $flash
 */
function redirect_back($flash = array()) {
    $back = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
    add_flash($flash);
    if (empty($back) and !preg_match("#".config("base_url")."#", $back)) redirect(url());
    redirect($back);
}
//redirect to a pager
function redirect_to_pager($id, $param = array(), $flash = array()) {
    $url = Pager::getLink($id, $param);
    add_flash($flash);
    redirect($url);
}

function add_flash($flash = array()) {
    if ($flash and isset($flash['id']) and isset($flash['message'])) {
        $id = $flash['id'];
        $message = serialize($flash['message']);
        session_put($id, $message);
    }
}

/**
 * Function to check flash data
 * @param string $id
 */
function has_flash($id) {
    $data = session_get($id);
    if ($data)  return true;
    return false;
}

/**
 * Function to flash data
 * @param string $id
 */
function get_flash($id) {
    $data = session_get($id);
    if ($data) $data = unserialize($data);
    session_forget($id);
    return $data;
}

/**
 * Method to generate a link
 * @param string $url
 * @return string
 */
function url($url = "") {

    return App::getInstance()->url($url);
}
/**
 * Method to generate link to a pager
 * @param string $id
 * @param array $param
 * @return string
 */
function url_to_pager($id, $param = array()) {
    return Pager::getLink($id, $param);
}
/**
 * Function to add menus
 */
function add_menu($location, $details) {
    return Pager::addMenu($location, $details);
}
/**
 * function to get a menu object by its location and id
 * @param string $location
 * @param string $id
 * @return mixed
 */
function get_menu($location, $id) {
    return Pager::getMenu($location, $id);
}
/**
 * Method to get the menus for a location
 * @param string $location
 * @return array
 */
function get_menus($location) {
    return Pager::getMenus($location);
}

/**
 * Method to add available menus
 * @param string $title
 * @param string $link
 * @param string $icon
 * @param string $location
 * @return boolean
 */
function add_available_menu($title, $link, $icon = null, $location = 'all') {
    return Menu::addAvailableMenu($title, $link, $icon, $location);
}

/**
 * Method to get available menus
 * @param string $location
 * @return array
 */
function get_available_menus($location) {
    return Menu::getAvailableMenus($location);
}

/**
 * Method to add menu locations
 * @param string $id
 * @param string $title
 * @return boolean
 */
function add_menu_location($id, $title) {
    return Menu::addLocation($id, $title);
}

/**
 * Method to get menu locations
 * @return array
 */
function get_menu_locations() {
    return Menu::getLocations();
}
/**
 * function to get the settings list
 * @return array
 */
function get_settings_menu() {
    $path = path("includes/settings/");
    $openDir = opendir($path);
    $file = array();
    while($read = readdir($openDir)) {
        if (substr($read, 0, 1) != ".") {
            $settingId = str_replace(".php", "", $read);
            $settings = include $path.$settingId.'.php';
            $file[$settingId] = $settings['title'];
        }
    }
    return $file;
}
/**
 * Function to load functions file
 * @param string $name
 */
function load_functions($path = null) {
    App::getInstance()->loadFunctionFile($path);
}

/**
 * Function to save a value to cache
 * @param string $key
 * @param mixed $value
 * $param int   $time
 * @param null $time
 */
function set_cache($key,$value,$time = null)
{
    $cache = new Cache;
    $cache->set($key,$value,$time);
}

/**
 * Function to get value from cache
 * @param mixed $key
 */
function get_cache($key,$default = null)
{
    $cache = Cache::getInstance();
    return $cache->get($key,$default);
}

/**
 * Function to set a value forever in cache
 * @param string $key
 * @param $value
 * @internal param mixed $value
 */
function set_cacheForever($key,$value)
{
    $cache = Cache::getInstance();
    $cache->setForever($key,$value);
}

/**
 * Function to unset a value from cache
 * @return void
 */
function forget_cache($key)
{
    $cache = Cache::getInstance();
    $cache->forget($key);
}

/**
 * Function to unset all value from cache
 * @return void
 */
function flush_cache()
{
    $cache = Cache::getInstance();
    $cache->flush();
}

/**
 * Function to check if a key exists in cache
 * @param string $key
 * @return bool
 */
function cache_exists($key)
{
    $cache = Cache::getInstance();
    return  $cache->keyexists($key);
}
/**
 * function get admin settings
 *@param string $key
 * @param string $default
 */
function get_setting($key, $default = "") {
    return config($key, $default);
}

/**
 * Function to hash content
 * @param string $content
 * @return string
 */
function hash_make($content) {
    $app = App::getInstance();
    if ($app->config('bcrypt')) {
        require_once path("includes/libraries/password.php");
        return password_hash($content, PASSWORD_BCRYPT, array('cost' => 10));
    } else {
        return md5($content);
    }
}
/**
 * function to check if a given hash match a content
 * @param string $content
 * @param string $hash
 * @return boolean
 */
function hash_check($content, $hash) {
    $app = App::getInstance();
    if ($app->config('bcrypt')) {
        require_once path("includes/libraries/password.php");
        return password_verify($content, $hash);
    } else {
        return (md5($content) == $hash);
    }
}

/**
 * Function to attach several callback to an event
 * @param $event
 * @param null $values
 * @param null $callback
 * @return mixed|null
 */
function register_hook($event,$callback)
{
    $hook = Hook::getInstance();
    $hook->attachOrFire($event,$values = null,$callback);
}

/**
 * Function to fire several events  attached to a hook
 * @param $event
 * @param null $values
 * @internal param null $callback
 * @return mixed|null
 */
function fire_hook($event,$values = null, $param = array())
{
    $hook = Hook::getInstance();
    return $hook->attachOrFire($event,$values,$callback = null, $param);
}

/**
 * Function to segment from the uri
 * @param int $index
 * @return string
 */
function segment($index, $default = null) {
    return App::getInstance()->segment($index, $default);
}
/**
 * Function to validate inputs
 * @param array input
 */
function validator($inputs,$rules)
{
    $validator = Validator::getInstance();
    $validator->scan($inputs,$rules);
    return $validator->errors();
}

/**
 * @param array $validator_response
 * @return bool
 */
function validation_passes()
{
    $validator = Validator::getInstance();
    $errors    = $validator->errorBag;
    if(empty($errors)) return TRUE;
    return FALSE;
}

/**
 * @param array $validator_response
 * @return bool
 */
function validation_fails()
{
    $validator = Validator::getInstance();
    $errors    = $validator->errorBag;
    if(empty($errors)) return FALSE;
    return TRUE;
}

/**
 * Return the first error from the errorBag
 * or the first error for a given field
 */
function validation_first($key = null)
{
    $validator = Validator::getInstance();
    return  $validator->first($key);
}

/**
 * Function to extend validation
 * @param string $rule
 * @param string $message
 * @param mixed $callable
 * @return mixed
 */
function validation_extend($rule, $message, $callable) {
    Validator::getInstance()->extendValidation($rule, $message, $callable);
}

/**
 * Function to get user ip address
 * @return string
 */
function get_ip() {
    //Just get the headers if we can or else use the SERVER global
    if ( function_exists( 'apache_request_headers' ) ) {
        $headers = apache_request_headers();
    } else {
        $headers = $_SERVER;
    }

    //Get the forwarded IP if it exists
    if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
        $the_ip = $headers['X-Forwarded-For'];
    } elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )
    ) {
        $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
    } else {
        $the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
    }

    return $the_ip;
}

/**
 * Function to get all languages from language directory
 * @return array
 */
function get_languages() {
    $directory = path("languages/");
    $handle = opendir($directory);
    $languages = array();

    while($read = readdir($handle)) {
        if (substr($read, 0, 1) != '.' and preg_match("#\.php#", $read)) {
            $lang = str_replace('.php', '', $read);
            $languages[$lang] = ucwords($lang).'';
        }
    }
    return $languages;
}

/**
 * Function to return old input from $_POST global
 * or return default if not available
 */
function input_old($key,$default = null)
{
    if(isset($_POST[$key])) return $_POST[$key];
    return $default;
}

/**
 * Function to get active theme of a type
 * @param string $type
 * @return string
 */
function get_active_theme($type = "frontend") {
    if (cache_exists("themes")) {
        $themes = get_cache("themes", array());
        if (isset($themes[$type]) and !empty($themes)) return $themes[$type];
    }
    return "default";
}

/**
 * function to check if the request is from ajax
 * @return boolean
 */
function is_ajax() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") {
        return true;
    }
    return false;
}
/**
 * Function to load map unto a page
 */
function load_map($mapinfo)
{
    echo '<script>
          var LongLat = new google.maps.LatLng('. $mapinfo[0] .','. $mapinfo[1].');
          var map     = "";
          var title   ="'. $mapinfo[4].'";
          var info    ="'. $mapinfo[5].'"

          function initialize() {
            var mapCanvas = document.getElementById("map-canvas");
            var mapOptions = {
              center: LongLat,
              zoom:'. $mapinfo[2] .',
              mapTypeId: google.maps.MapTypeId.'. $mapinfo[3].
        '}
        map = new google.maps.Map(mapCanvas, mapOptions);
      }
      initialize();

      var infowindow = new google.maps.InfoWindow({
          content: info
      });

      var marker = new google.maps.Marker({
        position: LongLat,
        map:  map,
        title:title
    });

     google.maps.event.addListener(marker, "click", function() {
        infowindow.open(map,marker);
      });
    </script>';
}

/**
 * Get map configurations
 */
function get_mapconfig($config)
{
    return explode('|',$config);
}

/**
 * Register blocks Page
 */
function register_block_page($pageId, $pageTitle = null) {
    return Blocks::getInstance()->registerPage($pageId, $pageTitle);
}

/**
 * Function to register site page
 * @param string $id
 * @param array $page
 * @param func $oCcallback, its executed when the page is newly inserted
 */
function register_site_page($id, $page, $oCallback = null) {
    return Pager::addSitePage($id, $page, $oCallback);
}

/**
 * Function to add widgets
 * @param int $widgetId
 * @param string $pageId
 * @param string $widget
 * @param string $location
 * @return boolean
 */
function add_widget($widgetId, $pageId, $widget, $location) {
    return Widget::add($widgetId, $pageId, $widget, $location);
}

/**
 * Register Blocks
 * @deprecated
 */
function register_block($blockView, $blockTitle = null, $page = null, $settings = array()) {
    return Blocks::getInstance()->registerBlock($blockView, $blockTitle, $page, $settings);
}

/**
 * Get all registered blocks
 */
function get_blocks($pageId = null) {
    return Blocks::getInstance()->getBlocks($pageId);
}

/**
 * Get all register pages
 */
function get_block_pages() {
    return Blocks::getInstance()->getPages();
}

/**
 * Function to add page blocks
 */
function add_page_block($blockView, $pageId, $blockId = null, $settings = array())
{
    return Blocks::getInstance()->addPageBlock($blockView, $pageId, $blockId, $settings);
}

/**
 * Function to remove page blocks
 */
function remove_page_block($blockView, $pageId) {
    return Blocks::getInstance()->removePageBlock($blockView, $pageId);
}

/**
 * function to get all blocks for a page
 */
function get_page_blocks($pageId, $global = true) {
    //return Blocks::getInstance()->getPageBlocks($pageId, $global);
}

function get_page_registered_blocks($pageId){
    return Blocks::getInstance()->getPageRegisteredBlocks($pageId);
}

function theme_extend($event,$values = null, $param = array()) {
    return fire_hook($event,$values, $param);
}

/**
 * function to paginate a query
 */
function paginate($query, $limit = 10, $links = 7) {
    return $paginator = new Paginator($query, $limit, $links);
}

function delete_file($path) {
    $basePath = path();
    $basePath2 = $basePath.'/';

    if ($path == $basePath or $path == $basePath2) return false;

    $path = fire_hook("delete.file", $path);
    if (is_dir($path) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file)
        {
            if (in_array($file->getBasename(), array('.', '..')) !== true)
            {
                if ($file->isDir() === true)
                {
                    rmdir($file->getPathName());
                }

                else if (($file->isFile() === true) || ($file->isLink() === true))
                {
                    unlink($file->getPathname());
                }
            }
        }

        return rmdir($path);
    }

    else if ((is_file($path) === true) || (is_link($path) === true))
    {
        return unlink($path);
    }

    return false;
}

function toAscii($str, $replace=array(), $delimiter='-', $charset='ISO-8859-1') {


    $str = str_replace(
        array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
        array("'", "'", '"', '"', '-', '--', '...'),
        $str); // by mordomiamil
    try{
        $str = iconv($charset, 'UTF-8', $str); // by lelebart
        if( !empty($replace) ) {
            $str = str_replace((array)$replace, ' ', $str);
        }
        $clean = $str;
    } catch(Exception $e) {}

    try {
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    } catch( Exception $e) {

    }

    $str = preg_replace('/[^\x{0600}-\x{06FF}A-Za-z !@#$%^&*()]/u','', $str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
    $clean = strtolower(trim($clean, '-'));
    return $clean;
}

/**
 * Mailer functions
 */
function mailer() {
    return Mailer::getInstance();
}

/**
 * function to add email templates
 */
function add_email_template($id, $details = array(), $langId = 'english') {
    return mailer()->addTemplate($id, $details, $langId);
}

/**
 * Mysqli helper function to return all the assoc array results
 * @param mysqli
 * @return array
 */
function fetch_all($query) {
    $get = array();
    if(!$query) return $get;
    while($fetch = $query->fetch_assoc()) {
        $get[] = $fetch;
    }

    return $get;
}

if (!function_exists('perfectSerialize')) {
    function perfectSerialize($string) {
        return base64_encode(serialize($string));
    }
}

if (!function_exists('perfectUnserialize')) {
    function perfectUnserialize($string) {

        if (base64_decode($string, true) == true) {

            return @unserialize(base64_decode($string));
        } else {
            return @unserialize($string);
        }
    }
}

function str_limit($text, $limit, $ad = '...') {
    return  mb_substr($text, 0, $limit, 'utf-8').((strlen($text) > $limit) ? $ad : null);
}

function format_output_text($content) {
    $content = str_replace('\\r\\n', '<br>',$content);
    $content = str_replace('\\r', '<br>',$content);
    $content  = str_replace('\\n\\n', '<br>',$content);
    $content = str_replace('\\n', '<br>',$content);
    $content = str_replace('\\n', '<br>',$content);
    $content = stripslashes($content);
    $content = nl2br($content);
    $content = autoLinkUrls($content);
    if ($content) $content = fire_hook('filter.content', $content);
    //$content = sanitizeText($content);
    //replace bad words
    $badWords = config('ban_filters_words', '');
    if ($badWords) {
        $badWords = explode(',', $badWords);
        foreach($badWords as $word) {
            $content = str_replace($word, '***', $content);
        }
    }

    return $content;
}

function is_rtl( $string ) {
    $rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';
    return preg_match($rtl_chars_pattern, $string);
}

function output_text($content) {

    $tContent = $content;
    $original = $content;
    $content = format_output_text($content);
    if (is_rtl($content)) {
        $content = "<span style='direction: rtl;text-align: right;display: block'>{$content}</span>";

    }
    //too much text solution
    $id = md5($tContent.time());
    $result = "<span id='{$id}' style='font-weight: normal !important'>";
    if (mb_strlen($tContent) > 500) {
        $result .= "<span class='text-full' style='display: none;font-weight: normal'>{$content}</span>";
        $tContent = format_output_text(str_limit($tContent, 500));
        if (is_rtl($tContent)) $tContent = "<span style='direction: rtl;text-align: right;display:block'>{$tContent}</span>";
        $result .= "<span style='font-weight: normal !important'>".$tContent."</span>";
        $result .= '<a href="" onclick=\'return read_more(this, "'.$id.'")\'>'.lang('read-more').'</a>';
    } else {
        $result .= $content;
    }

    $result .= "</span>";
    if (config('enable-bing-translator', false) and !empty($original) and !isEnglish($original)) {
        $trans = lang('see-translation');
        $result .= "<div id='{$id}-translation' class='non-translated'><input name='text' type='hidden' value='{$original}'/><button data-id='{$id}' onclick='return translateText(this)'>{$trans}</button></div>";
    }


    return $result;
}

function isEnglish($string) {
    if (strlen($string) != mb_strlen($string, 'utf-8')) return false;
    return true;
}

function format_bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE) {
// Format string
    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

    // IEC prefixes (binary)
    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
    {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    }
    // SI prefixes (decimal)
    else
    {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string) $force_unit, $units)) === FALSE)
    {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}


if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        }

        return $code;

    }
}

if (!function_exists('sanitizeText')) {
    function sanitizeText($string, $limit = false, $output = false) {
        if (!is_string($string)) return $string;
        //$string = lawedContent($string);//great one
        $string = trim($string);
        $string = htmlspecialchars($string, ENT_QUOTES);

        $string = str_replace('&amp;#', '&#',$string);
        $string = str_replace('&amp;', '&',$string);
        if ($limit) {
            $string = substr($string, 0, $limit);
        }
        return $string;
    }

}

if (!function_exists('remoteFileExists')) {
    function remoteFileExists($remote) {
        $curl = curl_init($remote);
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $ret = true;
            }
        }

        curl_close($curl);

        return $ret;
    }
}

function autoLinkUrls($text,$popup = true){
    $target = false;
    $str = $text;
    if ($target)
    {
        $target = ' target="'.$target.'"';
    }
    else
    {
        $target = '';
    }
    // find and replace link
    $str = preg_replace('@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@',"<a onclick=\"return window.open('http://$1')\" nofollow='nofollow' href='javascript::void(0)' {$target}>$1</a>", $str);
    // add "http://" if not set
    $str = preg_replace('/<a\s[^>]*href\s*=\s*"((?!https?:\/\/)[^"]*)"[^>]*>/i', "<a onclick=\"return window.open('$1')\" nofollow='nofollow' href='javascript::void(0)' {$target}>$1</a>", $str);
    //return $str;
    $regexB = '(?:[^-\\/"\':!=a-z0-9_@＠]|^|\\:)';
    $regexUrl = '(?:[^\\p{P}\\p{Lo}\\s][\\.-](?=[^\\p{P}\\p{Lo}\\s])|[^\\p{P}\\p{Lo}\\s])+\\.[a-z]{2,}(?::[0-9]+)?';
    $regexUrlChars = '(?:(?:\\([a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_,~]+\\))|@[a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_,~]+\\/|[\\.\\,]?(?:[a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_~]|,(?!\s)))';
    $regexURLPath = '[a-z0-9=#\\/]';
    $regexQuery = '[a-z0-9!\\*\'\\(\\);:&=\\+\\$\\/%#\\[\\]\\-_\\.,~]';
    $regexQueryEnd = '[a-z0-9_&=#\\/]';

    $regex = '/(?:'             # $1 Complete match (preg_match already matches everything.)
        . '('.$regexB.')'    # $2 Preceding character
        . '('                                     # $3 Complete URL
        . '((?:https?:\\/\\/|www\\.)?)'           # $4 Protocol (or www)
        . '('.$regexUrl.')'          # $5 Domain(s) (and port)
        . '(\\/'.$regexUrlChars.'*'   # $6 URL Path
        . $regexURLPath.'?)?'
        . '(\\?'.$regexQuery.'*'  # $7 Query String
        . $regexQueryEnd.')?'
        . ')'
        . ')/iux';
//    return $text;
    return preg_replace_callback($regex, function($matches) {

        list($all, $before, $url, $protocol, $domain, $path, $query) = array_pad($matches, 7, '');
        $href = ((!$protocol || strtolower($protocol) === 'www.') ? 'http://'.$url : $url);
        //if (!$protocol && !preg_match('/\\.(?:com|net|org|gov|edu)$/iu' , $domain)) return $all;
        return $before."<a onclick=\"return window.open('".$href."')\" nofollow='nofollow' href='javascript:void(0)' >".$url."</a>";
    } , $text);
}//end AutoLinkUrls

function curl_get_file_size( $url ) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);

    $data = curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($ch);
    return $size;
}

function curl_get_content($url ,  $javascript_loop = 0, $timeout = 100 ) {
    $url = str_replace( "&amp;", "&", urldecode(trim($url)) );

    $cookie = tempnam ("/tmp", "CURLCOOKIE");
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
    //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_ENCODING, "" );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
    $content = curl_exec( $ch );
    $response = curl_getinfo( $ch );
    curl_close ( $ch );

    return $content;
}


function lawedContent($t, $C=1, $S=array()) {
    if (file_exists(path('includes/libraries/htmlawed/htmLawed.php'))) {
        require_once path('includes/libraries/htmlawed/htmLawed.php');

        return htmLawed($t, $C, $S);
    }

    return $t;
}

function perfect_url($url) {
    if (!preg_match('#http://#', $url) and !preg_match('#https://#', $url)) {
        $url = 'http://'.$url;
    }
    return $url;
}

function pusher() {
    return Pusher::getInstance()->getDriver();
}

function setPusher($pusher) {
    Pusher::getInstance()->setDriver($pusher);
}
function remote_filesize($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    $data = curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($ch);
    return  $size;

}
function download_file($path, $baseName = null, $speed = null) {
    if (!$path) return false;
    if ($path) $path = fire_hook("filter.url", $path);
    if (!preg_match("#storage#", $path)) return false;
    if (is_file($path) === true or preg_match("#http://#", $path) or preg_match("#https://#", $path))
    {

        @set_time_limit(0);

        while (ob_get_level() > 0)
        {
            ob_end_clean();
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $baseName = ($baseName) ? $baseName : md5(basename($path).time().time());
        $basename = $baseName.'.'.$ext;

        if (preg_match("#http://#", $path) or preg_match("#https://#", $path)) {
            $size = remote_filesize($path);
        } else  {
            $size =  sprintf('%u', filesize($path));
        }
        $speed = (is_null($speed) === true) ? $size : intval($speed) * 1024;

        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . $size);
        header('Content-Disposition: attachment; filename="' . $basename . '"');
        header('Content-Transfer-Encoding: binary');

        for ($i = 0; $i <= $size; $i = $i + $speed)
        {
            echo file_get_contents($path, false, null, $i, $speed);

            while (ob_get_level() > 0)
            {
                ob_end_clean();
            }

            flush();
            sleep(1);
        }

        //exit();
    }
}

function isMobile() {
    return app()->isMobile;
}

function isTablet() {
    return app()->isTablet;
}

function timeAgoMin($time, $full = false)
{
    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? 1 : $time;
    $tokens = array (
        31536000 => 'y',
        2592000 => 'mon',
        604800 => 'w',
        86400 => 'd',
        3600 => 'h',
        60 => 'min',
        1 => 'sec'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.''.$text.(($numberOfUnits>1)?'':'');
    }
}

function isRTL() {
    return (app()->langDetails['dir'] == 'rtl' and !isMobile());
}


function set_youtube_param($embed_code, $set = array(), $unset = array()) {
    preg_match( '/src="([^"]*)"/i', $embed_code, $array );
    if(isset($array[1])) {
        $video_link = $array[1];
    } else {
        return $embed_code;
    }
    if(!filter_var($video_link, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) || !is_array($set) || !is_array($unset)) return $embed_code;
    $scheme = (isset(parse_url($video_link)['scheme'])) ? parse_url($video_link)['scheme'] : null;
    $host = (isset(parse_url($video_link)['host'])) ? parse_url($video_link)['host'] : null;
    $path = (isset(parse_url($video_link)['path']) && parse_url($video_link)['path'] != '/') ? parse_url($video_link)['path'] : null;
    $query = (isset(parse_url($video_link)['query'])) ? parse_url($video_link)['query'] : null;
    $fragment = (isset(parse_url($video_link)['fragment'])) ? parse_url($video_link)['fragment'] : null;
    $variables = array();
    if(!is_null($query)){
        parse_str($query, $variables);
    }
    foreach ($set as $var => $val){
        $variables[$var] = $val;
    }
    foreach ($unset as $var){
        if(isset($variables[$var])){
            unset($variables[$var]);
        }
    }
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return preg_replace('/'.preg_quote($video_link, '/').'/i', $scheme.$s.$host.$path.$q.http_build_query($variables).$h.$fragment, $embed_code);
}

function slugger($str) {
    return trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $str)), '-');
}

function unique_slugger($title, $table, $id_column, $title_column, $slug_column) {
    $db = db();
    $id = $db->query("SELECT ".$id_column." FROM ".$table." WHERE ".$title_column." = '".mysqli_real_escape_string(db(), $title)."'");
    $id = ($id->num_rows == 0) ? 0 : $id->fetch_row()[0];
    $slug = slugger(lang($title));
    if($db->query("SELECT COUNT(".$id_column.") FROM ".$table." WHERE ".$slug_column." = '".mysqli_real_escape_string(db(),$slug)."' AND ".$id_column." != ".mysqli_real_escape_string(db(),$id))->fetch_row()[0] == 0) {
        return $slug;
    }
    else {
        $i = 0;
        while($db->query("SELECT COUNT(".$id_column.") FROM ".$table." WHERE ".$slug_column." = '".mysqli_real_escape_string(db(),$slug."-".$i)."' AND ".$id_column." != ".mysqli_real_escape_string(db(),$id))->fetch_row()[0] > 0) {
            $i++;
        }
        return $slug.'-'.$i;
    }
}