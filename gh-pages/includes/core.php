<?php
/**
 * This File contains necessary class definitions e.t.c
 *
 */
class App
{
    /**
     * Current application version
     * @var $version
     */
    public $version = "6.2.4";

    /**
     * Global Configurations
     */
    public $config = array();

    //app instance
    private static $instance;

    //confirm Api Usage
    private $isApi = false;

    //current theme type backend|frontend|mobile
    public $themeType = "frontend";
    public $theme = "default";
    public $themeLayout = "layouts/main";
    public $layoutParams = array();
    public $pageContentContainer = "#main-wrapper";
    public $design = array();

    //page title
    public $title = "";
    public $keywords = "";
    public $description = "";

    //meta tags
    public $metaTags = array();

    //pager content
    private $content = "";

    //assets
    private $assets = array('css' => array(), 'js' => array());

    //database object
    public $db;

    //current in use language and loaded languages
    public $lang = "english";
    private $fallbackLang = "english";
    private $languages = array();
    private $translations = array();

    //current loggedin user
    public $userid;
    public $user;

    //uri segments
    public $segments = array();

    public $baseUrl;

    public $plugins = array();

    public $corePlugins = array(
        'getstarted',
        'comment',
        'feed',
        'like',
        'notification',
        'relationship',
        'search',
        'page',
        'mention',
        'hashtag',
        'group',
        'game',
        'photo',
        'emoticons',
        'report',
        'chat',
        'ads',
        'event',
        'social',
        'help',
        'upgrader',
        'announcement',
        'blog',
        'membership'
    );
    public $topMenu = "";
    public $onHeader = true;
    public $onHeaderContent = true;
    public $hideFooterContent = false;
    public $defaultColumn = ONE_COLUMN_LAYOUT;

    public $queryCounts = 0;

    public $isMobile = true;
    public $isTablet = true;
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Method to get the instance of app class
     */
    public static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new App();
        }
        return static::$instance;
    }

    /**
     * Main application runner
     */
    public function run()
    {

        $this->config = include(__DIR__ . "/../config.php");

        $this->lang = $this->config("default_language");
        $this->fallbackLang = $this->config("fallback_language");

        $this->config['base_url'] = getRoot();

        $this->config['cookie_path'] = getBase();

        include_once('libraries/Mobile_Detect.php');
        $detect  = new  Mobile_Detect();
        $this->isMobile = $detect->isMobile();
        //$this->isMobile = true;
        if ($detect->isTablet()) {
           $this->isMobile = true;
        }


        if ($this->installed()) {
            $this->loadFunctionFile("users");
            $this->loadFunctionFile("admin");
            //load the admin settings
            $settings = get_all_admin_settings();
            $settings = (empty($settings)) ? array() : $settings;
            $this->config = array_merge($this->config, $settings);


            $this->plugins = get_activated_plugins();

            $this->keywords = config('site-keywords');
            $this->description = config('site-description');

            //we need to reload base again
            $this->config['base_url'] = getRoot();

            $ipFilters = config('ban_filters_ip', '');
            if ($ipFilters) {
                $ipFilters = explode(',', $ipFilters);
                if (in_array(get_ip(), $ipFilters)) exit("You have been banned from this site");
            }

            if ($this->config('https') and !isSecure()) {
                redirect(getFullUrl());
            }

            if (!$this->config('https') and isSecure()) {
                redirect(getFullUrl());
            }

            //set admin selected language
            $this->lang = get_active_language();
            //set the default timezone by admin
            //date_default_timezone_set($this->config('timezone'));

            if (isset($_COOKIE['sv_language'])) {
                $this->lang = $_COOKIE['sv_language'];
            }

            $this->langDetails = get_language($this->lang);
            $this->themeLayout =  'layouts/main';

            if (segment(0, '', $this->getUri()) == 'admincp') {
                $this->setThemetype("backend");
                $this->themeLayout = "layouts/main";

            }


            $phrases = get_phrases($this->lang);
            $this->languages['db_phrases'] = array();
            $this->languages['db_phrases'][$this->lang] = $phrases;
            //load fallback language phrases as well
            $phrases = get_phrases($this->fallbackLang);
            $this->languages['db_phrases'][$this->fallbackLang] = $phrases;

            require $this->path("includes/loader.php");

            //load the selected theme
            $theTheme = get_active_theme($this->themeType);
            if ($this->themeType == 'frontend') {
                $sessionTheme = session_get("theme.selected");
                if ($sessionTheme)  $theTheme = $sessionTheme;
                $queryStringTheme = input('theme');
                if ($queryStringTheme) {
                    $theTheme = $queryStringTheme;
                    session_put("theme.selected", $theTheme);
                }
            }
            $this->setTheme($theTheme);
            $themeLoader = $this->config('themes_dir').$this->themeType.'/'.$this->theme.'/loader.php';
            if (file_exists($themeLoader)) require $themeLoader;
            $this->initiatePlugins(); //run all activated plugins
            fire_hook("system.started",null, array($this));
            $this->topMenu = lang('explore');

        } else {
            $this->getUri();
            $segment = segment(0);
            if($segment != 'install') redirect(url("install"));
            include path("installer/loader.php");
        }

        $this->config['months'] = array(
            'january' => lang('january'),
            'february' => lang('february'),
            'march'    => lang('march'),
            'april'    => lang('april'),
            'may'      => lang('may'),
            'june'     => lang('june'),
            'july'     => lang('july'),
            'august'   => lang('august'),
            'september'=> lang('september'),
            'october'  => lang('october'),
            'november' => lang('november'),
            'december' => lang('december')
        );
        set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $this->title, 'description' => $this->description, 'image' => (!config('site-logo')) ? img("images/logo.png") : url_img(config('site-logo')), 'keywords' => $this->keywords));

        $pager = new Pager();
        //exit($this->getUri());

        if(config('shutdown-site', false) and $this->themeType != 'backend') {
            $result = view('shutdown/content');
        } else {
            $result = $pager->process($this->getUri());
            //echo $this->queryCounts;
        }

        fire_hook("system_shutdown", array($this));

        echo $result;
    }


    private function initiatePlugins()
    {
        foreach($this->plugins as $plugin) {
            $this->startPlugin($plugin);
        }
    }

    private function startPlugin($plugin)
    {
        $path = $this->pluginPath($plugin);
        $loader = $path.'loader.php';
        if (file_exists($loader)) include $loader;
    }

    public function plugin_loaded($plugin)
    {
        fire_hook("plugin.check", null, array($plugin));
        return in_array($plugin, $this->plugins);
    }

    public function plugin_is_core($plugin)
    {
        if (in_array($plugin, $this->corePlugins) or isset($this->corePlugins[$plugin])) return true;
        return false;
    }

    /**
     * Method get a value from the configurations array
     *
     * @var string $key
     * @var mixed $default
     * @return mixed
     */
    public function config($key, $default = "")
    {
        if (!isset($this->config[$key])) return $default;
        return $this->config[$key];
    }

    public function isApi() {
        return $this->isApi;
    }

    public function enableApi() {
        $this->isApi = true;
    }
    /**
     * Method to confirm if the app is installed or not
     *
     * @return boolean
     */
    public function installed()
    {
        return $this->config("installed");
    }

    /**
     *Method to get Database instance
     */
    public function db()
    {
        $this->queryCounts++;
        if ($this->db != null) return $this->db;
        //connect to database
        $this->db = new mysqli(
            $this->config('mysql_host'),
            $this->config("mysql_user"),
            $this->config("mysql_password"),
            $this->config("mysql_db_name")
        );

        if ($this->db->connect_error) die("Failed to connect to database : ". mysqli_connect_error());
        $this->db->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
        return $this->db;
    }

    /**
     *Method to get a language translation from the selected language
     * @param string $name
     * @param array $param
     * @return string
     */
    public function getTranslation($name, $param = array(), $default = null)
    {
        $languagePath = $this->path("languages/".$this->lang.'.php');
        $defaultPath = $this->path("languages/".$this->fallbackLang.'.php');
        $selectFromPath = "";

        if (preg_match("#::#", $name)) {
            list($plugin, $name) = explode("::", $name);
            $languagePath = $this->pluginPath($plugin, "languages/".$this->lang.'.php');
            $defaultPath = $this->pluginPath($plugin, "languages/".$this->fallbackLang.'.php');
        }


        if (isset($this->languages['db_phrases'][$this->lang][$name])) {
            $result = $this->languages['db_phrases'][$this->lang][$name];
            if (!empty($param)) {
                foreach($param as $replace => $value) {
                    $result = str_replace(":".$replace, $value, $result);
                }
            }

            return $result;
        }

        if (isset($this->languages['db_phrases'][$this->fallbackLang][$name])) {
            $result = $this->languages['db_phrases'][$this->fallbackLang][$name];
            if (!empty($param)) {
                foreach($param as $replace => $value) {
                    $result = str_replace(":".$replace, $value, $result);
                }
            }

            return $result;
        }

        if (!isset($this->languages[$languagePath])) {

            if (file_exists($languagePath)) {
                $this->languages[] = $languagePath;
                $this->languages[$languagePath] = include($languagePath);
            }
        }

        $result = (isset($this->languages[$languagePath][$name])) ? $this->languages[$languagePath][$name] : "";
        if (empty($result)) {
            if (!isset($this->languages[$defaultPath])) {
                $this->languages[] = $defaultPath;
                if(file_exists($defaultPath)) {
                    $this->languages[$defaultPath] = include($defaultPath);
                }
                $result = (isset($this->languages[$defaultPath][$name])) ? $this->languages[$defaultPath][$name] : "";

            } else {
                $result = (isset($this->languages[$defaultPath][$name])) ? $this->languages[$defaultPath][$name] : "";
            }
            if (empty($result)) return ($default) ? $default : $name;
        };

        if (!empty($param)) {
            foreach($param as $replace => $value) {
                $result = str_replace(":".$replace, $value, $result);
            }
        }
        return $result;
    }

    /**
     * Form a url with the base url making a full url
     * @param string $url
     * @return string
     */
    public function url($url = "")
    {
        if ($url) $url = fire_hook("filter.url", $url);
        if (preg_match('#http|https#', $url)) {
            return $url;
        }
        //exit($this->config("base_url").$url);
        return $this->config("base_url").$url;
    }

    /**
     * Method to get uri
     */
    public function getUri()
    {

        $fullUrl = getFullUrl();
        $uri = str_replace(strtolower($this->url()), "", strtolower($fullUrl));
        if (!empty($uri)) $this->segments = explode('/', $uri);
        return $uri;
    }

    /**
     * get a value from segment
     * @param $index
     * @return mixed
     */
    public function segment($index, $default = null)
    {
        if (isset($this->segments[$index])) {
            if ($this->installed()) {
                return mysqli_real_escape_string($this->db(), $this->segments[$index]);
            }
            return  $this->segments[$index];
        }
        return $default;
    }

    /**
     * Detect if ssl is enabled or not
     * @return mixed
     */
    public function sslEnabled()
    {
        return $this->config("https", false);
    }

    /**
     * Get the base path
     * @param string $path
     * @return string
     */
    public function path($path = "")
    {
        $path =  $this->config("base_path").$path;
        return $path;
    }

    /**
     * Get path to any plugins easily
     * @param string $plugin
     * @param string $path
     * @return string
     */
    public function pluginPath($plugin, $path = "")
    {
        $pluginBase = $this->config("plugins_dir").$plugin.'/';
        return $pluginBase.$path;
    }

    /**
     * Method to load functions file from core and plugin
     *
     * @param string $path
     * @return $this
     */
    public function loadFunctionFile($path = null)
    {
        if ($path == null) return $this;
        if (preg_match("#::#", $path)) {
            //its from plugin functions folder
            list($plugin, $path) = explode("::", $path);
            $filePath = $this->pluginPath($plugin, "functions/".$path.".php");
        } else {
            $filePath = $this->path("includes/functions/".$path.".php");
        }

        if (file_exists($filePath)) try{require_once $filePath;} catch(Exception $e) {exit($e->getMessage());};
        return $this;
    }

    /**
     * Set the current theme
     *
     * @param string $theme
     * @return $this
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Set the current theme type
     *
     * @param string $type
     * @return $this
     */
    public function setThemeType($type)
    {
        $this->themeType = $type;
        return $this;
    }

    /**
     * Set the theme layout
     *
     * @param string $layout
     * @return $this
     */
    public function setLayout($layout, $params = array())
    {
        $this->themeLayout = $layout;
        $this->layoutParams = array_merge($this->layoutParams, $params);
        return $this;
    }

    public function setPageContainer($container)
    {
        $this->pageContentContainer = $container;
        return $this;
    }

    /**
     * function to set the current page title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title = "")
    {
        $this->title = get_setting("site_title", "crea8socialPRO").' '.get_setting('title_separator', "-").' '.$title;
        return $this;
    }

    public function setKeywords($keywords) {
        $this->keywords = $keywords;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Function to get view content
     *
     * @param string $view
     * @param array $param
     * @return string
     */
    public function view($view, $param = array())
    {
        $viewPath = $this->getViewPath($view);
        if (!$viewPath) return false;
        ob_start();

        /**
         * make the parameters available to the views
         */
        $app = $this;
        //$app->config = array();
        extract($param);

        if (file_exists($viewPath)) //trigger_error(Error::viewNotFound($viewPath));
        include $viewPath;
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Method to get view path
     *
     * @param string $view
     * @return string
     */
    public function getViewPath($view, $theme = null)
    {
        $themeBase = $this->config("themes_dir");
        $theme = ($theme) ? $theme : $this->theme;
        $originalView = $view;
        $viewPath = $themeBase.$this->themeType.'/'.$theme.'/';
        $plugin = "";

        if (preg_match("#::#", $view)) {
            list($plugin, $view) = explode("::", $view);
        }
        if ($plugin) {
            if (!plugin_loaded($plugin)) return false;
            $viewPath = $this->pluginPath($plugin);
        }
        if (!$this->installed() and preg_match("#installer#", $view)) {
            $viewPath = path("installer/");
            $view = str_replace("installer/", '', $view);
        }
        if ($plugin and $this->plugin_is_core($plugin)) {
            $base = "";
            if ($this->themeType == 'backend') $base = "admincp/";
            if ($this->themeType == 'frontend') {
                $overwritePath = $themeBase.$this->themeType.'/'.$theme.'/plugins/'.$plugin.'/';
                if (file_exists($overwritePath.'html/'.$view.'.phtml')) return $overwritePath.'html/'.$view.'.phtml';
            }
            $view = $base.$view;
            $mobileViewPath = $viewPath.'html/'.$view.'-mobile.phtml';
            if ($this->isMobile and file_exists($mobileViewPath)) {
                return $mobileViewPath;
            }
            return $viewPath.'html/'.$view.'.phtml';
        }

        $mobileViewPath = $viewPath.'html/'.$view.'-mobile.phtml';
        if ($plugin and $this->themeType == 'frontend') {
            $mobileOverritePath = $themeBase.$this->themeType.'/'.$theme.'/plugins/'.$plugin.'/';
            if (file_exists($mobileOverritePath.'html/'.$view.'-mobile.phtml')) $mobileViewPath = $mobileOverritePath.'html/'.$view.'-mobile.phtml';
        }
        if ($this->isMobile and file_exists($mobileViewPath)) {
            return $mobileViewPath;
        }
        if ($plugin and $this->themeType == 'frontend') {
            $overwritePath = $themeBase.$this->themeType.'/'.$theme.'/plugins/'.$plugin.'/';
            if (file_exists($overwritePath.'html/'.$view.'.phtml')) return $overwritePath.'html/'.$view.'.phtml';
        }

        $finalViewPath = $viewPath.'html/'.$view.'.phtml';
        if (!$plugin and $this->themeType == 'frontend') {
            if (!file_exists($viewPath.'html/'.$view.'.phtml')) {
                $finalViewPath = $themeBase.$this->themeType.'/default/html/'.$view.'.phtml';
            }
        }

        return $finalViewPath;
    }

    /**
     * Method to render page content
     *
     */
    public function render($content = "")
    {
        if ($this->themeType == 'frontend') {
            $currentPage = Pager::getCurrentPage();
            $pageDetails = Pager::getSitePage($currentPage);
            $type = $this->defaultColumn;
            $description = $this->description;
            $keywords = $this->keywords;
            if ($pageDetails) {
                $type = $pageDetails['column_type'];
                if (isset($pageDetails['description']) && !empty($pageDetails['description'])) $description = $pageDetails['description'];
                if (isset($pageDetails['keywords']) && !empty($pageDetails['keywords'])) $keywords = $pageDetails['keywords'];
            }
            $content = $this->view('layouts/columns', array('type' => $type, 'content' => $content, 'page' => $currentPage,'pageDetails' => $pageDetails));
            $this->setMetaTags(array('description' => $description));
            $this->setMetaTags(array('keywords' => $keywords));
        }

        $content = $this->view($this->themeLayout, array_merge(array('title' => $this->title, 'content' => $content), $this->layoutParams));
        if (is_ajax()) {
            $content = mb_convert_encoding($content,'UTF-8','UTF-8');
            return json_encode(array(
                'title' => $this->title,
                'container' => $this->pageContentContainer,
                'content' => $content,
                'menu' => $this->topMenu,
                'design' => $this->design
            ));
        }
        $c = view('layouts/header', array('title' => $this->title, 'keywords' => $this->keywords, 'description' => $this->description));
        $c .= $content;
        $c .= view('layouts/footer', array('title' => $this->title));
        return $c;
    }

    /**
     * Method for getting an array of the HTML meta tags of the current page.
     *
     */
    public function getMetaTags()
    {
        return $this->metaTags;
    }

    /**
     * Method for updating the array containing the HTML meta tags of the current page.
     *
     */
    public function setMetaTags($new_meta_tags)
    {
        $this->metaTags = array_merge($this->metaTags, $new_meta_tags);
        return $this->metaTags;
    }

    /**
     * Method for rendering the array containing the HTML meta tags of the current page in HTML.
     *
     */
    public function renderMetaTags()
    {
        $meta_array = $this->metaTags;
        $html = '';
        foreach ($meta_array as $type => $content){
            if($type == 'name'){$html .= trim($content) != '' ? "\n\t".'<meta property="og:site_name" content="'.$content.'" />' : '';}
            if($type == 'title'){$html .= trim($content) != '' ? "\n\t".'<meta property="og:title" content="'.$content.'" />' : '';}
            if($type == 'description'){$html .= trim($content) != '' ? "\n\t".'<meta name="description" content="'.$content.'" />'."\n\t".'<meta property="og:description" content="'.$content.'" />' : '';}
            if($type == 'keywords'){$html .= trim($content) != '' ? "\n\t".'<meta name="keywords" content="'.$content.'" />' : '';}
            if($type == 'image'){$html .= trim($content) != '' ? "\n\t".'<meta property="og:image" content="'.$content.'" />'."\n\t".'<link rel="image_src" href="'.$content.'" />' : '';}
        }
        $meta_appends = "\n\t".'<meta charset="utf-8">'."\n\t".'<meta name="viewport" content="width=device-width, initial-scale=1">'."\n\t".'<meta http-equiv="x-ua-compatible" content="ie=edge">';
        return $html."\t".$meta_appends."\n";
    }

    /**
     * Method to add assets
     * @param string $asset
     * @param string $themeType
     * @return $this
     */
    public function registerAsset($asset, $themeType = "frontend")
    {
        $type = (pathinfo($asset, PATHINFO_EXTENSION) == 'css') ? 'css' : 'js';
        if (!isset($this->assets[$type][$themeType])) $this->assets[$type][$themeType] = array();
        $this->assets[$type][$themeType][] = $asset;
        return $this;
    }

    /**
     * @param $type
     * @param string $themeType
     * @return string
     */
    public function renderAssets($type, $themeType = "frontend")
    {
        if (!isset($this->assets[$type][$themeType])) return "";
        $assets = $this->assets[$type][$themeType];
        $html = "<script></script>";
        $html = fire_hook("before-render-".$type, $html, array($html));
        $minify = config('minify-assets', true);
        if ($minify) {
            //loop th
            $link = $this->minifyAssets($assets, $type);
            if ($type == "css") {
                $html .= "<link href='".$link."' rel='stylesheet' type='text/css'/>\n";
            } else {
                $html .= "<script src='".$link."'></script>";
            }
        } else {
            foreach($assets as $asset) {
                $link = $this->getAssetLink($asset);
                if ($type == "css") {
                    $html .= "<link href='".$link."' rel='stylesheet' type='text/css'/>\n";
                } else {
                    $html .= "<script src='".$link."'></script>";
                }
            }
        }



        $html = fire_hook("after-render-".$type, $html, array($html));
        return $html;
    }

    /**
     * @param $asset
     * @return string
     */
    public function getAssetLink($asset, $base = true)
    {
        $base = ($base) ? $this->config("base_url") : '';
        $plugin = "";

        if (preg_match("#::#", $asset)) {
            list($plugin, $asset) = explode("::", $asset);
        }
        if ($plugin) {
            $thePluginPath = $base.$this->config("plugins_folder").'/'.$plugin.'/'.$asset;
            $theThemePath = $base.$this->config("themes_folder").'/'.$this->themeType.'/'.$this->theme.'/plugins/'.$plugin.'/'.$asset;
            if (file_exists($theThemePath)) return $theThemePath;
            return $thePluginPath;

        } else {
            $file =  $base.$this->config("themes_folder").'/'.$this->themeType.'/'.$this->theme.'/'.$asset;
            if (file_exists($file)) return $file;
            return $base.$this->config("themes_folder").'/'.$this->themeType.'/default/'.$asset;
        }
    }

    private function minifyAssets($assets, $type) {
        $evaluate = $this->evaluateAllPathAndCalculateLastAssessTime($assets);
        list($assets, $calculatedTime) = $evaluate;

        $minifyDir = 'storage/assets/'.$type.'/';
        if (!is_dir(path($minifyDir))) mkdir(path($minifyDir), 0777, true);

        $minifyFile = $minifyDir.md5($calculatedTime.$type.getRoot()).'.'.$type;
        if (file_exists(path($minifyFile))) {
            return url($minifyFile);
        } else{
            $content = "";
            foreach($assets as $asset) {
                if (file_exists($asset)) $content .= $this->parseAssetsContent($asset, $type);

            }
            file_put_contents(path($minifyFile), $content);
            return url($minifyFile);
        }
    }

    protected function parseAssetsContent($asset, $type) {
        $realPath = path($asset);
        $content = file_get_contents($asset);
        // Remove comments.
        $content = preg_replace('!/\*.*?\*/!s', '', $content);
        $content = preg_replace('/^\s*\/\/(.+?)$/m', "\n", $content);

        if ($type == 'css') {
            /**
             * parse url with ../../
             */
            $content = str_replace('../../', url($this->stripSegment($asset, 2)).'/', $content);

            /**
             * now do ../
             */
            $content = str_replace('../', url($this->stripSegment($asset, 1)).'/', $content);

            // Remove tabs, spaces, newlines, etc.
            $content = str_replace(array("\r\n","\r","\n","\t"), '', $content);

            // Remove other spaces before/after.
            $content = preg_replace(array('(( )+{)','({( )+)'), '{', $content);
            $content = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $content);
            $content = preg_replace(array('(;( )+)','(( )+;)'), ';', $content);
        }

        return ($type == 'css') ? $content : ';'.$content;
    }

    /**
     * Help function to strip segment from a path
     *
     * @param string $path
     * @param int $number
     * @return string
     */
    protected function stripSegment($path, $number)
    {
        $a = explode('/', $path);

        $i = count($a) - ($number +1 );

        $path = "";

        for( $y =0; $y < $i; $y++)
        {
            $path.= $a[$y].'/';
        }

        return $path;
    }

    /**
     * Evaluate the paths and calculate the last asset time
     *
     * @param array $assets
     * @return array
     */
    protected function evaluateAllPathAndCalculateLastAssessTime($assets)
    {
        $newAssets = array();
        $calculatedTime = 0;

        foreach($assets as $asset)
        {
            $path = $this->getAssetLink($asset, false);
            $newAssets[] = $path;
            $realPath = path($path);

            $calculatedTime += @filemtime($realPath);
        }

        return array($newAssets, $calculatedTime);
    }
}

/**
 * Pager class
 */
class Pager
{
    private static $pages = array();

    private $app;

    private static $filters = array();

    private static $menus = array();
    private static $offMenus = array();
    private static $sitePages = array();
    private static $currentPage;

    public function __construct()
    {
        $this->app = App::getInstance();
    }

    public static function offMenu($location) {
        if (!in_array($location, static::$offMenus)) static::$offMenus[] = $location;
        return true;
    }

    public static function addSitePage($id, $val, $callback = null) {
        $expected = array(
            'title' => '',
            'description' => '',
            'page_type' => 'manual',
            'tags' => '',
            'keywords' => '',
            'column_type' => '',
            'content' => '',
            'blocks' => array()
        );
        /**
         * one-column - 1
         * two-column-right - 2
         * two-colomn-left - 3
         * three-column - 4
         * top-one-column - 5
         * top-two-column-right - 6
         * top-two-colomn-left - 7
         * top-three-column - 8
         * bottom-one-column - 9
         * bottom-two-column-right - 10
         * bottom-two-colomn-left - 11
         * bottom-three-column - 12
         */
        /**
         * @var $title
         * @var $description
         * @var $page_type
         * @var $tags
         * @var $keywords
         * @var $column_type
         * @var $content
         */
        extract(array_merge($expected, $val));
        if (!static::sitePageExists($id)) {
           db()->query("INSERT INTO static_pages (slug,title,content,tags,description,keywords,page_type,column_type)VALUES(
            '{$id}','{$title}','{$content}','{$tags}','{$description}','{$keywords}','{$page_type}','{$column_type}'
            )");
            forget_cache("site-pages");
            $insertedId = db()->insert_id;
            fire_hook("site.page.add", null, array($insertedId, $val));
            if ($callback) call_user_func($callback);
        }
        return true;
    }

    private static function sitePageExists($id) {
        $pages = static::getSitePages();
        if (isset($pages[$id])) return true;
        return false;
    }

    public static function getSitePage($id) {
        $pages = static::getSitePages();
        if (isset($pages[$id])) {
            if (!$pages[$id]['column_type']) $pages[$id]['column_type'] = app()->defaultColumn;

            return $pages[$id];
        }
        return array(
            'id' => $id,
            'column_type' => 1,
            'page_type' => 'auto'
        );
    }

    public static function getColumnTypeName($columnType) {
        $types = array(
            1 => 'one_column_layout',
            2 => 'two_column_right_layout',
            3 => 'two_column_left_layout',
            4 => 'three_column_layout',
            5 => 'top_one_column_layout',
            6 => 'top_two_column_right_layout',
            7 => 'top_two_column_left_layout',
            8 => 'top_three_column_layout',
            9 => 'bottom_one_column_layout',
            10 => 'bottom_two_column_right_layout',
            11 => 'bottom_two_column_left_layout',
            12 => 'bottom_three_column_layout',
            13 => 'top_one_column_layout',
            14 => 'top_two_column_right_layout',
            15 => 'top_two_column_left_layout',
            16 => 'top_three_column_layout'
         );
        return $types[$columnType];
    }

    public static function getSitePages() {
        if (cache_exists("site-pages")) {
            return get_cache('site-pages');
        } else {
            $query = db()->query("SELECT * FROM static_pages");
            $pages = array();
            //echo db()->error;
            while($fetch = $query->fetch_assoc()) {
                $pages[$fetch['slug']] = $fetch;
            }
            set_cacheForever("site-pages", $pages);
            return $pages;
        }
    }
    /**
     * Function to add menus
     *
     * @param string $location
     * @param array $details
     */
    public static function addMenu($location, $details)
    {
        if (in_array($location, static::$offMenus)) {
            return new Menu('', '', '');
        }
        $expected = array(
            'title' => '',
            'link' => '',
            'icon' => '',
            'id' => '',
            'ajaxify' => true,
            'open_new_tab' => false
        );

        /**
         * @var $title
         * @var $link
         * @var $icon
         * @var $id
         * @var $ajaxify
         * @var $open_new_tab
         */
        extract(array_merge($expected, $details));
        if (!isset(static::$menus[$location])) static::$menus[$location] = array();
        $id = (empty($id)) ? $link : $id;
        static::$menus[$location][$id] = new Menu($title, $link, $id, $icon, $ajaxify, $open_new_tab);

        return static::$menus[$location][$id];
    }

    /**
     * function to get menu object
     * @param string $location
     * @param $id
     * @return mixed
     */
    public static function getMenu($location, $id)
    {
        if (isset(static::$menus[$location][$id])) return static::$menus[$location][$id];
        return new Menu('','');
    }

    /**
     * Method to get all menus for a pager in a location
     * @param string $location
     * @return array
     */
    public static function getMenus($location)
    {
        //in case of save menus
        $saveMenus = Menu::getSaveMenus($location);
        if ($saveMenus) {

            foreach($saveMenus as $menu) {
                static::addMenu($location, $menu);
            }
        }

        if (isset(static::$menus[$location])) return static::$menus[$location];
        return array();
    }

    /**
     * Method to register filters
     *
     * @param string name
     * @param mixed $callable
     * @return boolean
     */
    public static function addFilter($name, $callable)
    {
        static::$filters[$name] = $callable;
    }

    public static function passFilters($filter)
    {
        $filters = explode("|", $filter);
        $passed = true;
        foreach($filters as $filter) {
            if (isset(static::$filters[$filter])) {
                $callableFunction = static::$filters[$filter];
                if (is_callable($callableFunction)) {
                    if (!call_user_func_array($callableFunction, array(App::getInstance()))) {
                        $passed = false;
                    }
                }
            }
        }

        return $passed;
    }

    /**
     * Method to add routes
     *
     * @param string $pattern
     * @param array $parameters
     * @return boolean
     */
    public static function add($pattern, $parameters = array())
    {
        $expectedParameters = array(
            'as' => '',
            'use' => '',
            'filter' => 'GET',
            'method' => '',
            'block' => false
        );

        /**
         * @var $as
         * @var $use
         * @var $filter
         * @var $method
         * @var $block
         */
        extract(array_merge($expectedParameters, $parameters));

        if (!$pattern or !$use) return false;

        $as = ($as) ? $as : $pattern;

        static::$pages[$as]  = array(
            'pattern' => $pattern,
            'use' => $use,
            'filter' => $filter,
            'method' => $method
        );
        static::$pages[$as] = new Page($pattern, $use, $method, $filter);

        if ($block and !empty($as)) {
            register_block_page($as, $block);
        }

        return static::$pages[$as];
    }

    /**
     * Function to add only POST Requests
     * @see add
     */
    public static function post($pattern, $parameters = array())
    {
        return static::add($pattern, array_merge($parameters, array('method' => 'POST')));
    }

    /**
     * Function to add GET Requests
     * @see add
     */
    public static function get($pattern, $parameters = array())
    {
        return static::add($pattern, array_merge($parameters, array('method' => 'GET')));
    }

    /**
     * Functions to add any GET or POST Requests
     * @see add
     */
    public static function any($pattern, $parameters = array())
    {
        return static::add($pattern, array_merge($parameters, array('method' => 'ANY')));
    }

    /**
     * Method to get link
     */
    public static function getLink($id, $param = array()) {
        if (!isset(static::$pages[$id])) return "";
        return static::$pages[$id]->getLink($param);
    }

    /**
     * Method to process requests
     *
     * @param string $uri
     * @return string
     */
    public function process($uri = "")
    {
        //always remove the / in front
        if (substr($uri, -1) == '/') $uri = rtrim($uri, '/');
        if (!$uri) $uri = "/";
        $pages = static::$pages;

        /**
         * Lets scan through our registered pages to load the appropriate page
         * for the request
         */
        $content = "";
        $found = false;
        //print_r($pages);
        //exit;
        foreach($pages as $id => $page) {
            if (!$content and preg_match("!^".$page->getPattern()."$!", $uri)) {
                //Ok lets check if the request method is allowed
                $method = strtoupper($page->method);
                $requestMethod = get_request_method();
                static::$currentPage = $id;
                if (($method == "ANY" or $method == $requestMethod) and Pager::passFilters($page->filters)) {
                    //then we can load this page

                    $content = $this->loadPager($page->use);
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) return MyError::error404();
        return $content;
    }

    public static function getCurrentPage() {
        return static::$currentPage;
    }

    public static function setCurrentPage($page) {
        static::$currentPage = $page;
    }

    /**
     * Load the pager file from the directory accordingly and call
     * the appropriate function load the page
     *
     * @param string $pager
     * @return string
     */
    private function loadPager($pager)
    {
        $function = "";
        $fileName = "";
        $plugin = "";
        $filePath = $this->app->path("includes/pages/");

        /**
         * Check if the request pager is from one of the plugins
         */
        if (preg_match("#::#", $pager)) {
            list($plugin, $pager) = explode("::", $pager);
        }
        list($fileName, $function) = explode("@", $pager);
        if ($plugin) $filePath = $this->app->pluginPath($plugin, "pages/");
        if ($fileName == 'install') $filePath = $this->app->path("installer/");

        //complete file path
        $filePath = $filePath.$fileName.'.php';
        if (!file_exists($filePath)) return MyError::error404();
        require_once $filePath;

        /**
         * Check if the function exists or not
         */
        if (function_exists($function)) {
            $app = $this->app;
            return call_user_func_array($function, array($app));
        } else {
            //throw page not found error
            return MyError::error404();
        }

    }
}

/**
 * Site widgets class
 */
class Widget{
    /**
     * Method to add widget to database table
     * @param int $widgetId
     * @param string $pageId
     * @param string $widget
     * @param string $location
     * @return boolean
     */
    public static function add($widgetId, $pageId, $widget, $location, $settings = '') {
        $sort = 1;
        $widgetId = ($widgetId) ? $widgetId : md5(time().$pageId.$widget.$location);
        if (static::exists($widgetId)) {
            if ($settings) {
                db()->query("UPDATE blocks SET settings='{$settings}' WHERE id='{$widgetId}'");
            }
            return true;
        }
        $query = db()->query("SELECT id FROM blocks WHERE page_id='{$pageId}' AND block_location='{$location}'");
        if ($query) $sort = $query->num_rows;
        db()->query("INSERT INTO blocks(id,page_id,block_view,block_location,sort,settings) VALUES('{$widgetId}','{$pageId}','{$widget}','{$location}','{$sort}','{$settings}')");
        forget_cache("page-widgets-".$pageId.'-top');
        forget_cache("page-widgets-".$pageId.'-left');
        forget_cache("page-widgets-".$pageId.'-middle');
        forget_cache("page-widgets-".$pageId.'-right');
        forget_cache("page-widgets-".$pageId.'-bottom');
        forget_cache("page-widgets-".$pageId.'-all');
        return true;
    }

    public static function exists($widgetId) {
        $query = db()->query("SELECT id FROM blocks WHERE id='{$widgetId}' LIMIT 1");
        if ($query) return $query->num_rows;
        return false;
    }

    public static  function getWidgetPath($widget) {
        $path = path("widgets/");
        if (preg_match("#plugin::#", $widget)) {
            list($plugin, $widgetPath)  = explode("::", $widget);
            list($pluginName, $widget) = explode("|", $widgetPath);
            $path = path('plugins/'.$pluginName.'/widgets/');
        }

        if (preg_match("#theme::#", $widget)) {
            list($plugin, $widgetPath)  = explode("::", $widget);
            list($pluginName, $widget) = explode("|", $widgetPath);
            $path = path('themes/frontend/'.$pluginName.'/widgets/');
        }
        $widgetPath = $path.$widget.'/';
        return $widgetPath;
    }

    public static function getViewPath($theWidgetPath, $widget) {
        $normalPath = $theWidgetPath.'view.phtml';
        $mobileViewPath = $theWidgetPath.'view-mobile.phtml';
        if (app()->isMobile and file_exists($mobileViewPath)) {
            $normalPath = $mobileViewPath;
        }
        //let try if current theme want to override widgets
        $currentTheme = app()->theme;
        if (preg_match("#plugin::#", $widget)) {
            list($plugin, $widgetPath)  = explode("::", $widget);
            list($pluginName, $widget) = explode("|", $widgetPath);
            if (!plugin_loaded($pluginName)) return false;
            $path = "themes/frontend/{$currentTheme}/widget-override/".'plugins/'.$pluginName.'/'.$widget.'/view.phtml';
            $mobileViewPath = "themes/frontend/{$currentTheme}/widget-override/".'plugins/'.$pluginName.'/'.$widget.'/view-mobile.phtml';
            if (app()->isMobile and file_exists($mobileViewPath)) {
                $path = $mobileViewPath;
            }
            if(file_exists($path)) $normalPath = $path;
        } else {
            $path = "themes/frontend/{$currentTheme}/widget-override/".$widget.'/view.phtml';
            $mobileViewPath = "themes/frontend/{$currentTheme}/widget-override/".$widget.'/view-mobile.phtml';
            if (app()->isMobile and file_exists($mobileViewPath)) {
                $path = $mobileViewPath;
            }
            if(file_exists($path)) $normalPath = $path;
        }
        return $normalPath;
    }

    public static function getSettings($widget, $theWidget) {
        $dbSettings = ($widget['settings']) ? perfectUnserialize($widget['settings']) : array();
        if ($dbSettings) return $dbSettings;
        //lets scan for default widget settings
        $defaultSettings = array();
        if (isset($theWidget['settings'])) {
            foreach($theWidget['settings'] as $id => $detail) {
                $defaultSettings[$id] = $detail['value'];
            }
        }
        return $defaultSettings;
    }

    public static function load($widget, $content = null) {
        $theWidget = static::get($widget['block_view']);
        $theWidgetPath = static::getWidgetPath($widget['block_view']);
        $widgetName = $widget['block_view'];
        $settings = static::getSettings($widget, $theWidget);

        if ($theWidget and $theWidgetPath) {
            $viewPath = static::getViewPath($theWidgetPath, $widgetName);
            if ($viewPath) {
                ob_start();

                /**
                 * make the parameters available to the views
                 */
                $app = app();

                if ($settings) extract($settings);

                if (file_exists($viewPath)) {
                    include $viewPath;
                } else {
                    //trigger_error(Error::viewNotFound($viewPath));
                }
                $content = ob_get_clean();
                return $content;
            } else {
                return '';
            }
        }
    }

    public static function get($widget) {
        $widgetPath = static::getWidgetPath($widget);
        if (!$widgetPath) return false;
        $info = array();
        if (is_dir($widgetPath)) {
            $info = (file_exists($widgetPath.'info.php')) ? include($widgetPath.'info.php') : array();
        }
        $info['delete'] = ($widget == 'content') ? false : true;
        return $info;
    }

    public static function listWidgets($plugin, $pluginName = null) {
        $path = path("widgets/");
        if ($plugin == 'plugin') {
            $path = path('plugins/'.$pluginName.'/widgets/');
        }

        if ($plugin == 'theme') {
            $path = path('themes/frontend/'.$pluginName.'/widgets/');
        }

        $widgets = array();
        if(is_dir($path)) {
            if($h = opendir($path)) {
                while($file = readdir($h)) {
                    if (substr($file, 0, 1) != '.' and !preg_match('#\.html#', $file)) {
                        $tpath =$path .$file.'/';
                        if (file_exists($tpath.'info.php')) {
                            $info = include $tpath.'info.php';
                            $id = $file;
                            if ($plugin == 'plugin') $id = "plugin::{$pluginName}|{$file}";
                            if ($plugin == 'theme') $id = "theme::{$pluginName}|{$file}";
                            $widgets[$id] = $info;
                        }
                    }
                }
            }
        }
        return $widgets;
    }

    public static function getWidgetPages($pageId, $position = 'all') {
        $widgets = array();
        $cacheName = "page-widgets-".$pageId.'-'.$position;
        if (cache_exists($cacheName)) {
            return get_cache($cacheName);
        } else {
            $query = db()->query("SELECT * FROM blocks WHERE page_id='{$pageId}' AND block_location='{$position}' ORDER BY sort ASC");
            while($fetch = $query->fetch_assoc()) {
                $widgets[] = $fetch;
            }
            set_cacheForever($cacheName, $widgets);
            return $widgets;
        }
    }

    public static function getWidget($block_view) {
        $widget = array();
        $cacheName = "page-widget-".$block_view;
        if (cache_exists($cacheName)) {
            return get_cache($cacheName);
        } else {
            $query = db()->query("SELECT * FROM blocks WHERE block_view='{$block_view}'");
            while($fetch = $query->fetch_assoc()) {
                $widget = $fetch;
            }
            if(empty($widget)) return false;
            set_cacheForever($cacheName, $widget);
            return $widget;
        }
    }
}

/**
 * Error Handler functions
 */
class MyError
{
    public static function error404()
    {
        http_response_code(404);
        $app = App::getInstance();
        $app->setLayout('layouts/blank')->setTitle(lang('404-not-found'));
        echo $app->render(view("errors/general"));
    }

    /**
     * @param $path
     */
    public static function viewNotFound($path)
    {

    }

    /**
     * @param $level
     * @param $message
     * @param $file
     * @param $line
     * @param $context
     */
    public static function handler($level, $message, $file, $line)
    {
        http_response_code(404);
        echo view("errors/debug", array('level' => $level, 'message' => $message, 'file' => $file, 'line' => $line));
        exit;
        $app = App::getInstance();
        if ($app->config("debug")) {
            //show the error message
            http_response_code(404);
            echo view("errors/debug", array('level' => $level, 'message' => $message, 'file' => $file, 'line' => $line));
            //echo
        } else {

            //show good error page
            $app = App::getInstance();
            $app->setLayout('layouts/blank')->setTitle(lang('404-not-found'));
            echo $app->render(view("errors/general"));
        }

    }
}

/**
 * Each Page class
 */
class Page
{
    public $pattern;
    public $filters;
    public $use;
    public $method;
    private $patternReplace = array();

    /**
     * @param $pattern
     * @param $use
     * @param $method
     * @param $filters
     */
    public function __construct($pattern, $use, $method, $filters)
    {
        $this->pattern = $pattern;
        $this->use = $use;
        $this->method = $method;
        $this->filters = $filters;
    }

    /**
     * Method to add patterReplace array
     *
     * @param array $replace
     */
    public function where($replace = array())
    {
        $this->patternReplace = $replace;
    }

    /**
     * Get the full pattern with the replaces
     * @return string
     */
    public function getPattern()
    {
        if ($this->pattern != "/" and substr($this->pattern, -1) == '/') $this->pattern = rtrim($this->pattern, '/');
        if (empty($this->patternReplace)) return $this->pattern;
        $pattern = $this->pattern;
        foreach($this->patternReplace as $replace => $p) {
            $pattern = str_replace("{".$replace."}", $p, $pattern);
        }
        return $pattern;
    }

    /**
     * @param array $param
     * @return mixed
     */
    public function getLink($param = array())
    {
        if (empty($param)) return app()->url($this->pattern);
        $pattern = $this->pattern;
        foreach($param as $id => $value) {
            $pattern = str_replace("{".$id."}", $value, $pattern);
        }
        return app()->url($pattern);
    }
}

/**
 * Pager Menu class
 */
class Menu
{
    public $title;
    public $id;
    public $link;
    public $icon;
    private $menus = array();
    private $active = false;
    private static $availableMenus = array();
    private static $menuLocation = array();
    public $ajax = true;
    public $tab = false;

    public function __construct($title, $link, $id = "", $icon = "", $ajaxify = true, $open_new_tab = false)
    {
        $this->title = $title;
        $this->link = url($link);
        $this->id = $id;
        $this->icon = $icon;
        $this->ajax = $ajaxify;
        $this->tab = $open_new_tab;
    }

    public function addMenu($title, $link, $id = "", $icon = "")
    {
        $id = (empty($id)) ? $link : $id;
        $this->menus[$id] = new Menu($title, $link, $id, $icon);
        return $this;
    }

    public function findMenu($id)
    {
        return (isset($this->menus[$id])) ? $this->menus[$id] : new Menu('', '');
    }

    public function getMenus()
    {
        return $this->menus;
    }

    public function hasMenu()
    {
        return (!empty($this->menus)) ? true : false;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function setActive($value = true)
    {
        $this->active = $value;
        return $this;
    }

    public static function addAvailableMenu($title, $link, $icon = '', $location = 'all') {
        static::$availableMenus[$location][] = array(
            'title' => $title,
            'link' => $link,
            'icon' => $icon
        );
        return true;
    }

    public static function getAvailableMenus($location) {
        $result = array();
        if (isset(static::$availableMenus[$location])) $result = static::$availableMenus[$location];
        $result = array_merge($result, static::$availableMenus['all']);
        return $result;
    }

    public static function addLocation($id, $title) {
        static::$menuLocation[$id] = $title;
        return true;
    }

    public static function getLocations() {
        return static::$menuLocation;
    }

    public static function saveMenu($location,$title,$link,$type = 'manual',$ajax = 1,$icon = '',$tab = 0 , $id = null) {
        $order = 1;
        $query = db()->query("SELECT id FROM menus WHERE menu_location='{$location}'");
        $id = ($id) ? $id : md5(time().$title.$link);
        if ($query) $order = $query->num_rows;
        db()->query("INSERT INTO menus (menu_location,title,link,type,ajaxify,icon,menu_order,open_new_tab,id) VALUES(
    '{$location}','{$title}','{$link}','{$type}','{$ajax}','{$icon}','{$order}','{$tab}','{$id}'
    )");
        forget_cache("site-menus-{$location}");
    }

    public static function findSaveMenu($id) {
        $query = db()->query("SELECT * FROM menus WHERE id='{$id}'");
        if ($query) return $query->fetch_assoc();
        return false;
    }

    public static function getSaveMenus($location) {
        $cacheName = "site-menus-{$location}";
        if (cache_exists($cacheName)) {
            return get_cache($cacheName);
        } else {
            $query = db()->query("SELECT * FROM menus WHERE menu_location='{$location}' ORDER by menu_order ASC");
            $result = fetch_all($query);
            set_cacheForever($cacheName, $result);
            return $result;
        }
    }
}

class FileCache{
    public function storage($filepath = null, $dir =  false)
    {
        if ($filepath) {
            $parts = array_slice(str_split($filepath, 2), 0, 2);
            $key = join('/', $parts).'/';
            if ($dir) {
                try{
                    mkdir('storage/cache/'.$key, 0777, true);
                }catch(Exception $e) {}
            }
            $filepath = $key . $filepath;
        }

        return path('storage/cache/'.$filepath);
    }

    /**
     * Store an item in the cache for a given number of minutes.
     * If minute is not supplied cache for 60 sec
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return void
     */
    public  function set($key,$val,$time = null)
    {
        $this->newcachelibrary();
        $key = md5($key);
        /**
         * Check if key exist, then overide
         */
        if( !file_exists($this->storage($key)) ) $this->forget($key);

        /**
         * Check if time to cache is supplied
         * use default of 60secs
         */
        $time = isset($time) ? time() + $time :  time() + 60 ;

        $this->filepath = $this->storage( $key, true);


        $filehandle = file_put_contents($this->filepath, $time.serialize($val));
    }

    /**
     * Store an item in the cache forever.
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return void
     */
    public  function setForever($key,$val)
    {
        return $this->set($key,$val,9999999999);
    }

    /**
     * Retrieve an item from the cache.
     * @param  string  $key
     * @return mixed
     */
    public  function get($key,$default = null)
    {
        $key   = md5($key);

        /**
         * Check if available
         */
        $path = $this->storage($key);
        if( !file_exists($path) ) return $default;
        /**
         * Check if expire
         */
        $content = file_get_contents($this->storage($key));
        $time = substr( $content, 0, 11);

        if(time() > $time)
        {

            $this->forget($key);
            return $default;
        }

        $value = $content;
        return unserialize(str_replace($time,"", $value));
    }


    /**
     * @param string $key
     * @return bool
     */
    public function keyexists($key)
    {
        $key = md5($key);
        return file_exists($this->storage($key));
    }

    /**
     * delete an item from the cache.
     * @param $filename
     * @internal param string $key
     * @return mixed
     */
    public  function forget($key)
    {
        $key = md5($key);

        if( !file_exists($this->storage($key)) ) return NULL;

        unlink($this->storage($key));
    }


    /**
     * Remove all item from the cache.
     * @return void
     */
    public function flush()
    {
        if( !file_exists($this->storage()) && !is_dir($this->storage()) ) return NULL;

        $handle = opendir($this->storage());
        while(false !== ($file = readdir($handle)))
        {
            if($file !== "." && $file !== "..")
            {
                unlink($this->storage($file));
            }
        }

        rmdir( $this->storage() );
    }


    /**
     * create new cache
     * library if not exist
     * @param
     * @return void
     */
    private function newcachelibrary()
    {
        if(!file_exists( $this->storage() ))  mkdir( $this->storage() ,0777, true);
    }
}

class MyMemCache{

    private $memcache;
    private $prefix;

    public function __construct() {
        $this->memcache = new Memcache();
        $this->memcache->addServer(config('memcache-host', '127.0.0.1'), config('memcache-port', '11211'), 100);
        $this->prefix  = config('memcache-prefix');
    }
    /**
     * Store an item in the cache for a given number of minutes.
     * If minute is not supplied cache for 60 sec
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return void
     */
    public  function set($key,$val,$time = null)
    {
        $key = $this->prefix.$key;
        $this->memcache->set($key, $val, $time);
    }

    /**
     * Store an item in the cache forever.
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return void
     */
    public  function setForever($key,$val)
    {
        $key = $this->prefix.$key;
        $this->memcache->set($key, $val, 0);
    }

    /**
     * Retrieve an item from the cache.
     * @param  string  $key
     * @return mixed
     */
    public  function get($key,$default = null)
    {
        $key = $this->prefix.$key;
        $value = $this->memcache->get($key);
        return $value;
    }


    /**
     * @param string $key
     * @return bool
     */
    public function keyexists($key)
    {
        return $this->get($key);
    }

    /**
     * delete an item from the cache.
     * @param $filename
     * @internal param string $key
     * @return mixed
     */
    public  function forget($key)
    {
        $key = $this->prefix.$key;
        return $this->memcache->delete($key);
    }


    /**
     * Remove all item from the cache.
     * @return void
     */
    public function flush()
    {
        return $this->memcache->flush();
    }

}

class Cache{

    static $instance;

    private $driver;

    public function __construct() {
        $driver = config('cache-driver', 'memcache');

        switch($driver) {
            case 'file':
                $this->driver = new FileCache();
                break;
            case 'memcache':

                if (class_exists('Memcache')) {
                    $this->driver = new MyMemCache();
                } else {
                    $this->driver = new FileCache(); //savely fallback to file cache
                }
                break;
        }
    }

    public static function getInstance()
    {
        if(!isset(static::$instance) && static::$instance == null) return static::$instance = new Cache;
        return static::$instance;
    }


    /**
     * Store an item in the cache for a given number of minutes.
     * If minute is not supplied cache for 60 sec
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return void
     */
    public  function set($key,$val,$time = null)
    {
        return $this->driver->set($key, $val, $time);
    }

    /**
     * Store an item in the cache forever.
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return void
     */
    public  function setForever($key,$val)
    {
        return $this->driver->setForever($key, $val);
    }

    /**
     * Retrieve an item from the cache.
     * @param  string  $key
     * @return mixed
     */
    public  function get($key,$default = null)
    {
        return $this->driver->get($key, $default);
    }


    /**
     * @param string $key
     * @return bool
     */
    public function keyexists($key)
    {
        return $this->driver->keyexists($key);
    }

    /**
     * delete an item from the cache.
     * @param $filename
     * @internal param string $key
     * @return mixed
     */
    public  function forget($key)
    {
        return $this->driver->forget($key);
    }


    /**
     * Remove all item from the cache.
     * @return void
     */
    public function flush()
    {
        return $this->driver->flush();
    }

}

class Hook{

    private $events = array();

    static $instance;

    public static function getInstance()
    {
        if (!static::$instance) static::$instance = new Hook();
        return static::$instance;
    }

    /**
     * @param $event
     * @param null $values
     * @param null $callback
     * @return mixed|null
     */
    public function attachOrFire($event,$values = NULL,$callback = NULL, $param = array())
    {
        if (!is_array($param)) $param = array($param);
        if($callback !== NULL)
        {
            if(!isset($this->events[$event])) $this->events[$event] = array();
            $this->events[$event][] = $callback;
        }
        else{
            $theValue = $values;
            $result = $values;
            if (isset($this->events[$event])) {
                foreach($this->events[$event] as $callbacks)
                {
                    $newParam = ($values) ? array_merge(array($theValue), $param) : $param;
                    $v  = call_user_func_array($callbacks,$newParam);
                    $theValue = ($values) ? $v : $theValue;
                    $result = ($v) ? $v : $result;
                }
            }
            return ($values) ? $theValue : $result;
        }
    }
}


class Validator{

    static $instance;
    public $inputs   = array();
    public $rules    = array();
    public  $errorBag = array();
    public $extendRulesFunctions = array();
    public $messages = array();

    public static function getInstance()
    {
        if(!isset(static::$instance)) static::$instance = new Validator();
        return static::$instance;
    }

    public function __construct()
    {
        $this->messages = array(
            'required' => lang('validation-required'),
            'min'      => lang('validation-min'),
            'max'      => lang('validation-max'),
            'between'  => lang('validation-between'),
            'alphanum' => lang('validation-alphanum'),
            'integer'  => lang('validation-integer'),
            'alpha'    => lang('validation-alpha'),
            'numeric'  => lang('validation-numeric'),
            'url'      => lang('validation-url'),
            'email'    => lang('validation-email'),
            'unique'   => lang('validation-unique'),
            'date'     => lang('validation-date')
        );
    }

    public function scan(array $inputs, array $rules)
    {
        $this->inputs = array_merge($this->inputs, $inputs);
        $this->rules  = array_merge($this->rules, $rules);
        $obj    = $this;

//        if(count($this->inputs) != count($this->rules)) die('Validator Error: Numbers of parameters / rules supplied does not match');

        array_walk($this->inputs,function($item,$key) use($rules,$inputs,$obj){

            /**field to validate**/
            $field = $key;

            /**value supplied for field**/
            $value = $item;

            if(!array_key_exists($key, $rules)) return true;

            /**rules to validate field**/
            $rules = $rules[$key];

            $ruleSets = explode("|", $rules);

            $filteredRules = $obj->validRuleset($ruleSets,$value);

            /**
             * Iterate through all validation rules
             * available for a specific field, then
             * call with cal_user_func_array
             */
            array_walk($filteredRules,function($rules,$key) use ($obj,$field){
                $method = array_shift($rules);
                $param  = $rules;
                $param  = $param['param'];

                /*add field to validator method*/
                if( is_array($param) ) $param['field'] = $field ;
                if( !is_array($param) ) $param = array($param,$field) ;

                if (isset($obj->extendRulesFunctions[$method])) {
                    $result = call_user_func_array($obj->extendRulesFunctions[$method], array_values($param));
                    if (!$result) {
                        $obj->errorBag[] = array('field' => $field,'error' => $obj->getError($method,array($field)));
                    }
                } else {
                    call_user_func_array(array($obj, strtolower($method) ), array_values($param) );
                }

            });

        });

    }

    /**
     * Check if validation passes
     * @return bool
     */
    public function passes()
    {
        if(empty($this->errorBag)) return TRUE;
        return FALSE;
    }

    /**
     * Check if validation fails
     * @return bool
     */
    public function fails()
    {
        if(!empty($this->errorBag)) return TRUE;
        return FALSE;
    }

    /**
     * Return errorBag
     * @return array
     */
    public function errors()
    {
        return $this->errorBag;
    }
    /**
     * Takes array of ruleset to return as action with param
     * @param array $ruleSets
     * @param string $value
     * @return array
     */
    public function validRuleset($ruleSets,$value)
    {
        $validation = array();

        foreach($ruleSets as $rule)
        {
            if(preg_match('#:#', $rule))
            {
                $validation[] = $this->getExtendedRule($rule,$value);
                continue;
            }
            $validation[] = array('action' => $rule, 'param' => $value);
        }

        return $validation;
    }

    /**
     * Take an extended rule
     * then breakdown to method and values
     * @param string $rule
     * @param $value
     * @return array
     */
    public function getExtendedRule($rule,$value)
    {
        /**
         * explode rule to return method as
         * first index, arguments as other
         * indexes, add value to validate as extra param
         */
        $param   = explode(':',$rule);
        $rule    = array_shift($param);
        $argue   = $param;
        $argue[] = $value;

        return array('action' => $rule, 'param' => $argue);
    }

    /**
     * Validation rule :required
     * @param string value
     * return array
     */
    public function required($value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);
        $contain_image = preg_match('/<img/', $value);

        if(strlen($value) == 0 && !$contain_image)
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('required',array($field) ));
        }
    }

    /**
     * Validation rule :min
     * @param int min
     * @param string value
     * return array
     */
    public function min($min,$value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        $min   =  $min;
        if(strlen($value) < $min)
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('min',array($field,$min) ));
        }
    }

    /**
     * Validation rule :max
     * @param int max
     * @param string value
     * return array
     */
    public function max($max,$value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        $max   =  $max;
        if(strlen($value) > $max)
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('max',array($field,$max) ));
        }
    }

    /**
     * Validation rule :between
     * @param int min
     * @param int max
     * @param string value
     * @param string field
     * return array
     */
    public function between($min,$max,$value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        $max   =  $max;
        $min   =  $min;
        if(strlen($value) < $min || strlen($value) > $max)
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('max',array($field,$min,$max) ));
        }
    }

    /**
     * Validation rule :numeric
     * @param string value
     * @param string field
     * return array
     */
    public function numeric($value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        $valid = is_numeric($value);
        if(!$valid)
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('numeric',array($field)));
        }
    }

    /**
     * Validation rule :alpha
     * @param string value
     * @param string field
     * return array
     */
    public function alpha($value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        $valid = preg_match('/^\pL+$/u', $value);
        if(!$valid)
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('alpha',array($field)));
        }
    }

    /**
     * Validation rule :alphanum
     * @param string value
     * @param string field
     * return array
     */
    public function alphanum($value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        $valid = preg_match('/^[\pL\pN]+$/u', $value);
        $slug = toAscii($value);

        if(!$valid or empty($slug) or strlen($value) != strlen($slug))
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('alphanum',array($field)));
        }
    }

    /**
     * Validation rule :slug
     * @param string $value
     * @param string $field
     * @return array
     */
    public function alphadash($value, $field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        $valid = preg_match('/^[\pL\pN_-]+$/u', $value);
        $slug = toAscii($value);

        if(!$valid or empty($slug)  or strlen($value) != strlen($slug))
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('alphanum',array($field)));
        }
    }

    /**
     * Validation rule :email
     * @param int min
     * @param int max
     * @param string value
     * @param string field
     * return array
     */
    public function email($value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        if (filter_var($value, FILTER_VALIDATE_EMAIL) == false) {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('email',array($field)));
        }
    }

    /**
     * Validation rule :url
     * @param string value
     * @param string field
     * return array
     */
    public function url($value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        if(!filter_var($value, FILTER_VALIDATE_URL));
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('url',array($field)));
        }
    }

    /**
     * Validation rule :integer
     * @param string value
     * @param string field
     * return array
     */
    public function integer($value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);

        if(!is_int($value));
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('integer',array($field)));
        }
    }

    /**
     * Check if a field is unique against
     * a column from the db
     */
    public function unique($table,$value,$field)
    {
        $value = strip_tags($value);
        $field = strip_tags($field);
        $table = strip_tags($table);

        $stmt = db()->prepare('SELECT '.$field .' FROM '.$table.' WHERE '. $field.' = ? LIMIT 1');
        $stmt->bind_param('s',$value);
        $stmt->execute();
        $match = '';
        $stmt->bind_result($match);
        $stmt->fetch();
        /**return error if match found*/
        if($match)
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('unique',array($field)));
        }
    }

    /*
     * Validate against a given date
     */
    function date($value,$field)
    {
        $date = date_parse($value);
        if(!checkdate($date['month'], $date['day'], $date['year']) || is_null(strtotime($value)) )
        {
            $this->errorBag[] = array('field' => $field,'error' => $this->getError('date',array($field)));
        }
    }

    /**
     * Function to return error for a specific rule
     * @param string type
     * @param array $arguments
     * @internal param \arguments $array
     * @return array
     */
    public function getError($type,array $arguments)
    {
        $message = $this->error_messages();
        $message = $message[$type];

        preg_match_all("#:[a-z]+#",$message,$matches);
        $args = array();
        foreach($arguments as $a) {
            $args[] = lang($a);
        }

        return str_replace(array_shift($matches),$args,$message);
    }

    /**
     * Get the first error message
     * or a first error message of param provided
     */
    public function first($param = null)
    {
        if(empty($this->errorBag)) return "";

        if(empty($param))
        {
            $array = array_shift($this->errorBag);
            if( isset($array['error']) ) $first = $array['error'];
        }
        foreach($this->errorBag as $errors)
        {
            if($errors['field'] == $param){  $first =  $errors['error']; break;}
        }

        if(!empty($first)) {return $first;}
        else{
            return "";
        }
    }

    /**
     * Function add extended Rules functions right from anywhere
     * @param string $rule
     * @param string $message
     * @param mixed $callable
     * @return mixed
     */
    public function extendValidation($rule, $message, $callable)
    {
        $this->extendRulesFunctions[$rule] = $callable;
        $this->messages[$rule] = $message;
    }

    /**
     * Validation error messages
     */
    public function error_messages()
    {
        return $this->messages;
    }
}

/**
 * Uploader class
 */
class Uploader
{
    /**
     * Allow image type
     */
    private $imageTypes = array('png', 'jpg', 'gif', 'jpeg');
    private $imageSizes = array(75, 200,600, 920);

    /**
     * Allowed File types
     */
    private $fileTypes = array(
        'doc',
        'xml',
        'exe',
        'txt',
        'zip',
        'rar',
        'doc',
        'mp3',
        'jpg',
        'png',
        'css',
        'psd',
        'pdf',
        '3gp',
        'ppt',
        'pptx',
        'xls',
        'xlsx',
        'html',
        'docx',
        'fla',
        'avi',
        'mp4',
        'swf',
        'ico',
        'gif',
        'webm',
        'jpeg'
    );

    /**
     * Allowed video types
     */
    private $videoTypes = array('mp4');
    private $audioTypes = array('mp3');
    private $sourceFile;
    private $linkContent = '';
    public $source;
    public $sourceName;
    public $sourceSize;
    public $extension;
    public $destinationPath;
    public $destinationName;
    public $baseDir;

    private $dbType;
    private $dbTypeId;
    private $type;

    //max sizes
    private $maxFileSize = 10000000;
    private $maxImageSize = 10000000;
    private $maxVideoSize = 10000000;
    private $maxAudioSize = 10000000;

    //allow Animated gif
    private $animatedGif = true;

    private $error = false;
    private $errorMessage;
    public $result;
    public  $insertedId;
    public $allowCDN = true;
    /**
     * @param $source
     * @param string $type
     * @param mixed $validate
     */
    public function __construct($source, $type = "image", $validate = false, $fromFile = false, $isLink = false)
    {
        $source = is_string($source) ? fire_hook('path.local', null, array($source)) : $source;
        $this->source = $source;
        $this->type = $type;
        $this->maxFileSize = config("max-file-upload", $this->maxFileSize);
        $this->maxVideoSize = config("max-video-upload", $this->maxVideoSize);
        $this->maxAudioSize = config("max-audio-upload", $this->maxAudioSize);
        $this->maxImageSize = config("max-image-size", $this->maxImageSize);
        $this->animatedGif = config("support-animated-image", $this->animatedGif);
        $this->imageTypes = explode(',', config('image-file-types', 'jpg,png,gif,jpeg'));
        $this->videoTypes = explode(',', config('video-file-types', 'mp4,mov,wmv,3gp,avi,flv,f4v,webm'));
        $this->audioTypes = explode(',', config('audio-file-types', 'mp3,aac,wma'));
        $this->fileTypes = explode(',', config('files-file-types', 'doc,xml,exe,txt,zip,rar,mp3,jpg,png,css,psd,pdf,3gp,ppt,pptx,xls,xlsx,html,docx,fla,avi,mp4,swf,ico,gif,jpeg,webm'));

        if(!$fromFile) {
            if ($source and $this->source['size'] != 0) {
                $this->source = $source;
                $this->sourceFile = $this->source['tmp_name'];
                $this->sourceSize = $this->source['size'];
                $this->sourceName = $this->source['name'];
                $name = pathinfo($this->sourceName);
                if (isset($name['extension'])) $this->extension = strtolower($name['extension']);

                $this->confirmFile();

            } else {
                if (!$validate) {
                    $this->error = true;
                    $this->errorMessage = lang("failed-to-upload-file");
                } else {
                    $this->validate($validate);
                }
            }
        } else {
            $this->source = $this->sourceFile = $this->sourceName = $source;
            if (!$isLink) {
                $name = pathinfo($this->sourceName);
                if (isset($name['extension'])) $this->extension = strtolower($name['extension']);
            } else {
                $content = @file_get_contents($this->source);
                if (!$content) {
                    $this->error = true;
                    $this->errorMessage = lang("failed-to-upload-file");
                } else {
                    $this->extension = "jpg";
                    $this->linkContent = $content;
                }

            }
        }

        //load our libraries
        require_once path("includes/libraries/PHPImageWorkshop/autoload.php");
        if($this->animatedGif) require_once path("includes/libraries/gif_exg.php");

        //confirm the creation of uploads directory
        if (!is_dir(path('storage/uploads/'))) {
            @mkdir(path('storage/uploads/'), 0777, true);
            $file = @fopen(path('storage/uploads/index.html'), 'x+');
            fclose($file);
        }

    }

    public function setFileTypes($types) {
        $this->fileTypes = $types;
        return $this;
    }

    public function noThumbnails() {
        $this->imageSizes = array(600, 920);
        return $this;
    }

    public function disableCDN() {
        $this->allowCDN = false;
    }

    public function enableCDN() {
        $this->allowCDN = true;
    }

    /**
     * Method to get the image width
     * @return null
     */
    function getWidth()
    {
        list($width, $height) = getimagesize($this->sourceFile);
        return ($width) ? $width : null;
    }

    /**
     * Method to get the image height
     * @return int
     */
    function getHeight()
    {
        list($width, $height) = getimagesize($this->sourceFile);
        return ($height) ? $height : null;
    }

    public function confirmFile()
    {
        switch($this->type) {
            case 'image':
                if (!in_array($this->extension, $this->imageTypes)){
                    $this->errorMessage = lang("upload-file-not-valid-image");
                    $this->error = true;
                }
                if ($this->sourceSize > $this->maxImageSize) {
                    $this->errorMessage = lang("upload-image-size-error", array('size' => format_bytes($this->maxImageSize)));
                    $this->error = true;
                }
            break;
            case 'video':
                if (!in_array($this->extension, $this->videoTypes)) {
                    $this->errorMessage = lang("upload-file-not-valid-video");
                    $this->error = true;
                }
                if ($this->sourceSize > $this->maxVideoSize) {
                    $this->errorMessage = lang("upload-video-size-error", array('size' => format_bytes($this->maxVideoSize)));
                    $this->error = true;
                }
            break;
            case 'audio':
                if (!in_array($this->extension, $this->audioTypes)) {
                    $this->errorMessage = lang("upload-file-not-valid-audio");
                    $this->error = true;
                }
                if ($this->sourceSize > $this->maxAudioSize) {
                    $this->errorMessage = lang("upload-audio-size-error", array('size' => format_bytes($this->maxAudioSize)));
                    $this->error = true;
                }
            break;
            case 'file':
                if (!in_array($this->extension, $this->fileTypes)) {
                    $this->errorMessage = lang("upload-file-not-valid-file");
                    $this->error = true;
                }

                if ($this->sourceSize > $this->maxFileSize) {
                    $this->errorMessage = lang("upload-file-size-error", array('size' => format_bytes($this->maxFileSize)));
                    $this->error = true;
                }
            break;
        }
    }

    /**
     * Validate upload files for multiple uploads
     * @param array $files
     * @return boolean
     */
    public function validate($files)
    {
        $isError = false;
        foreach($files as $file){
            $pathInfo = pathinfo($file['name']);
            $this->extension = strtolower($pathInfo['extension']);
            $this->sourceSize = $file['size'];
            switch($this->type) {
                case 'image':
                    if (!in_array($this->extension, $this->imageTypes)){
                        $this->errorMessage = lang("upload-file-not-valid-image");
                        $this->error = true;
                    }
                    if ($this->sourceSize > $this->maxImageSize) {
                        $this->errorMessage = lang("upload-file-size-error", array('size' => format_bytes($this->maxImageSize)));
                        $this->error = true;
                    }
                break;
                case 'video':
                    if (!in_array($this->extension, $this->videoTypes)) {
                        $this->errorMessage = lang("upload-file-not-valid-video");
                        $this->error = true;
                    }
                    if ($this->sourceSize > $this->maxVideoSize) {
                        $this->errorMessage = lang("upload-file-size-error", array('size' => format_bytes($this->maxVideoSize)));
                        $this->error = true;
                    }
                break;
                case 'audio':
                    if (!in_array($this->extension, $this->audioTypes)) {
                        $this->errorMessage = lang("upload-file-not-valid-audio");
                        $this->error = true;
                    }
                    if ($this->sourceSize > $this->maxAudioSize) {
                        $this->errorMessage = lang("upload-file-size-error", array('size' => format_bytes($this->maxAudioSize)));
                        $this->error = true;
                    }
                break;
                case 'file':
                    if (!in_array($this->extension, $this->fileTypes)) {
                        $this->errorMessage = lang("upload-file-not-valid-file");
                        $this->error = true;
                    }

                    if ($this->sourceSize > $this->maxFileSize) {
                        $this->errorMessage = lang("upload-file-size-error", array('size' => format_bytes($this->maxFileSize)));
                        $this->error = true;
                    }
                break;
            }
        }
    }

    /**
     * Function to confirm file passes
     */
    public function passed()
    {
        return !$this->error;
    }

    /**
     * Function to set destination
     */
    public function setPath($path)
    {
        $this->baseDir = "storage/uploads/".$path;
        $path = path("storage/uploads/").$path;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            //create the index.html file
            if (!file_exists($path.'index.html')) {
                $file = fopen($path.'index.html', 'x+');
                fclose($file);
            }
        }
        $this->destinationPath = $path;
        return $this;
    }

    /**
     *Function to resize image
     * @param int $width
     * @param int $height
     * @param string $fit
     * @param string $any
     * @return $this
     */
    public function resize($width = null, $height = null, $fit = "inside", $any = "down")
    {
        if ($this->error) return false;
        $fileName = md5($this->sourceName.time()).'.'.$this->extension;
        $fileName = (!$width) ? '_%w_'.$fileName : '_'.$width.'_'.$fileName;

        $this->result = $this->baseDir.$fileName;

        if ($width) {
            $this->finalizeResize($fileName, $width, $height, $fit, $any);
        } else {
            foreach($this->imageSizes as $size) {
                $this->finalizeResize(str_replace('%w', $size, $fileName), $size, $size, $fit, $any);
            }
        }

        return $this;
    }

    /**
     * @param $filename
     * @param $width
     * @param $height
     * @param $fit
     * @param $any
     */
    private function finalizeResize($filename, $width, $height, $fit, $any)
    {
        try {
            if ($this->animatedGif and $this->extension == "gif") {
                $Gif = new GIF_eXG($this->sourceFile, 1);
                $Gif->resize($this->destinationPath.$filename, $width, $height, 1, 0);
            } else {
                /**$wm = WideImage::load($this->sourceFile);
                $wm = $wm->resize($width, $height, $fit, $any);
                $wm->saveToFile($this->destinationPath.$filename);**/
                if ($this->linkContent) {
                    $layer = PHPImageWorkshop\ImageWorkshop::initFromString($this->linkContent);
                } else {
                    if(extension_loaded('exif')) {
                        $layer = PHPImageWorkshop\ImageWorkshop::initFromPath($this->sourceFile, true);
                    } else {
                        $layer = PHPImageWorkshop\ImageWorkshop::initFromPath($this->sourceFile);
                    }
                }
                if($width == 550) {
                    $layer->resizeInPixel($width, $height, true);
                }
                elseif ($width < 600) {
                    $layer->cropMaximumInPixel(0, 0, "MM");
                    $layer->resizeInPixel($width, $height);
                } else {
                    $layer->resizeToFit($width, $height, true);
                }

                $layer->save($this->destinationPath, $filename);
            }

            fire_hook("upload", null, array($this, $filename));
        } catch(Exception $e){
            exit($e->getMessage());
            $this->result = '';
        }
    }

    /**
     * Function to crop image
     * @param int $left
     * @param int $top
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function crop($left = 0, $top = 0, $width = '100%', $height = '100%')
    {
        if ($this->error) return false;
        $fileName = md5($this->sourceName.time()).'.'.$this->extension;
        $fileName = '_'.str_replace('%', '', $width).'_'.$fileName;
        $this->result = $this->baseDir.$fileName;

        try{
            $layer = PHPImageWorkshop\ImageWorkshop::initFromPath($this->sourceFile, true);
            $layer->cropInPixel($width, $height, $left, $top);
            $layer->save($this->destinationPath, $fileName);
            /**$wm = $wm->crop($left, $top, $width, $height);
            $wm->saveToFile($this->destinationPath.$fileName);**/
            fire_hook("upload", null, array($this, $fileName));
        } catch(Exception $e){$this->result = '';}

        return $this;
    }
    /**
     * Function to get result
     * @return string
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * Function to save media to database
     * @param string $type
     * @param string $typeId
     * @return $this
     */
    public function toDB($type = "", $typeId = "", $privacy = 1, $album = '')
    {
        if ($this->error) return false;
        $userid = get_userid();
        $query = db()->query("INSERT INTO `medias` (`user_id`,`path`, `file_type`, `type`, `type_id`,`privacy`,`album_id`)
         VALUES('{$userid}','{$this->result}', '{$this->type}','{$type}', '{$typeId}','{$privacy}','{$album}');
        ");
        if ($query) {
            $insertId = db()->insert_id;
            $this->insertedId = $insertId;
            fire_hook('media-added', null, array($insertId, $this->result, $this->type, $type, $typeId, $privacy, $album));
        }
        return $this;
    }

    /**
     * Function to upload video
     */
    public function uploadVideo()
    {
        return $this->directUpload();
    }

    /**
     * function to upload file
     */
    public function uploadFile()
    {
        return $this->directUpload();
    }

    protected function directUpload()
    {
        if ($this->error) return false;
        $fileName = md5($this->sourceName.time()).".".$this->extension;
        $this->result = $this->baseDir.$fileName;
        move_uploaded_file($this->sourceFile, $this->destinationPath.$fileName);
        fire_hook("upload.start", null, array($this, $fileName));
        fire_hook("upload", null, array($this, $fileName));
        return $this;
    }

    public function getError()
    {
        return $this->errorMessage;
    }
}

/**
 * Blocks Class
 */
class Blocks
{
    private $pages = array();
    private $blocks = array(
        'all' => array(), //contain all blocks that can appear on any page
    );
    private static $instance;

    public function __construct()
    {

    }

    public static  function getInstance()
    {
        if (static::$instance) return static::$instance;
        return static::$instance = new Blocks();
    }

    /**
     * Method to register pages
     * @param string $pageId
     * @param string $pageTitle
     * @return $this
     */
    public function registerPage($pageId, $pageTitle = null)
    {
        $pageTitle = (!$pageTitle) ? $pageId : $pageTitle;
        $this->pages[$pageId] = $pageTitle;
        return $this;
    }

    /**
     * Method to register blocks
     * @param string $blockView
     * @param string $blockTitle
     * @param string $page
     * @return $this
     */
    public function registerBlock($blockView, $blockTitle = null, $page = null, $settings = array())
    {
        $blockTitle = ($blockTitle) ? $blockTitle : $blockView;
        if ($page) {
            $pages = (!is_array($page)) ? array($page) : $page;
            foreach($pages as $page) {
                if (!isset($this->blocks[$page])) $this->blocks[$page] = array();
                $this->blocks[$page][$blockView] = array('title' => $blockTitle, 'settings' => $settings);
            }
        } else {
            $this->blocks['all'][$blockView] = array('title' => $blockTitle, 'settings' => $settings);
        }
        return $this;
    }

    /**
     * Get all registered pages
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Method to get all register blocks
     * @param string $page
     * @return array
     */
    public function getBlocks($page = null)
    {
        $blocks = $this->blocks['all'];
        if ($page and isset($this->blocks[$page])) {
            $blocks = array_merge($blocks, $this->blocks[$page]);
        }
        return $blocks;
    }

    /**
     * Method to add a block to a page
     * @param string $blockView
     * @param string $pageId
     * @return $this
     */
    public function addPageBlock($blockView, $pageId, $blockId = null, $settings = array())
    {
        $pages = (!is_array($pageId)) ? array($pageId) : $pageId;
        foreach($pages as $pageId) {
            $check = db()->query("SELECT `page_id` FROM `blocks` WHERE `id`='{$blockId}'");
            if ($check and $check->num_rows < 1) {
                $settings = perfectSerialize($settings);
                db()->query("INSERT INTO `blocks` (block_view,page_id,id,settings) VALUES('{$blockView}','{$pageId}','{$blockId}', '{$settings}')");
            }
            forget_cache("page-blocks-".$pageId);
        }

        return $this;
    }

    /**
     * Method to remove a block from a page
     * @param string $blockView
     * @param string $pageId
     * @return $this
     */
    public function removePageBlock($blockView, $pageId)
    {
        db()->query("DELETE FROM `blocks` WHERE `page_id`='{$pageId}' AND `block_view`='{$blockView}'");
        forget_cache("page-blocks-".$pageId);
        return $this;
    }

    public function getPageRegisteredBlocks($pageId)
    {
        $query = db()->query("SELECT * FROM `blocks` WHERE `page_id`='{$pageId}' ORDER BY sort asc ");
        if ($query) return fetch_all($query);
        return array();
    }

    /**
     * Method to get all blocks content for a page
     * @param string $pageId
     * @return string
     */
    public function getPageBlocks($pageId, $global = true)
    {
        $content = "";
        $results = array();
        if (cache_exists("page-blocks-".$pageId)) {
            $results =  get_cache("page-blocks-".$pageId);
        } else {
            $query = db()->query("SELECT `block_view`,`settings` FROM `blocks` WHERE `page_id`='{$pageId}' ORDER BY sort ASC");

            if ($query) {
                $results = fetch_all($query);
            }
            //all page blocks

            set_cacheForever("page-blocks-".$pageId, $results);

        }

        foreach($results as $block) {
            $content .= view($block['block_view'], array('settings' => perfectUnSerialize($block['settings'])));
        }
        if ($pageId != "all" and $global) $content .= $this->getPageBlocks("all");
        return $content;
    }
}

/**
 * Paginator class
 */
class Paginator
{
    private $db;
    private $limit;
    private $page;
    private $query;
    public $total;
    private $baseUrl = "";
    private $links = 7;
    private $listClass = "pagination";
    private $appends = array();

    public function __construct($query, $limit = 10, $links = 7)
    {
        $this->db = db();
        $this->query = $query;

        $result = $this->db->query($this->query);
        //exit($query);
        if($result) $this->total = $result->num_rows;

        $this->page = input("page", 1);
        $this->validatePageNumber();
        $this->limit = $limit;
        $this->links = $links;
        $this->baseUrl = getFullUrl();
    }

    function validatePageNumber()
    {
        $this->page = (int) str_replace("-", '', $this->page);
    }

    public function setListClass($class = "")
    {
        $this->listClass = $class;
        return $this;
    }

    public function append($param = array())
    {
        $this->appends = $param;
        return $this;
    }

    public function results()
    {
        if ($this->limit == "all") {
            $query = $this->query;
        } else {
            $query = $this->query . " LIMIT ". (($this->page - 1) * $this->limit). ", {$this->limit}";
        }
        $query = $this->db->query($query);
        if ($query) {
            return fetch_all($query);
        }
        return array();
    }

    public function links($ajax = false)
    {
        if ($this->limit == 'all') return '';
        $last = ceil($this->total / $this->limit);
        $start = (($this->page - $this->links) > 0) ? $this->page - $this->links : 1;
        $end = (($this->page + $this->links) < $last) ? $this->page + $this->links : $last;
        $ajax = ($ajax) ? 'ajax="true"' : null;

        $html = "<ul style='padding-bottom: 20px;display: block'  class='{$this->listClass}'>";
        $class = ($this->page == 1) ? "disabled" : "";
        $html .= "<li class='{$class}'> <a ".$ajax."  href='".$this->getLink((($this->page - 1) == 0) ? 1 : $this->page - 1  )."'>&laquo;</a>";


        if ($start > 1) {
            $html .= "<li><a ".$ajax." href='".$this->getLink(1)."'>1</a></li>";
            $html .= "<li class='disabled'><span>...</span></li>";
        }

        for( $i = $start; $i <= $end; $i++) {
            $class = ($this->page == $i) ? "active" : "";
            $style = ($this->page == $i) ? "style='color:white !important'" : null;
            $html .= "<li class='".$class."' ><a {$style} ".$ajax." href='".$this->getLink($i)."'>".$i."</a></li>";
        }

        if ($end < $last) {
            $html .= "<li class='disabled'><span>...</span></li>";
            $html .= "<li><a ".$ajax." href='".$this->getLink($last)."'>{$last}</a>";
        }

        $class = ($this->page == $last) ? "disabled" : "";
        $html .= "<li class='{$class}'> <a ".$ajax." href='".$this->getLink(($last == $this->page) ? $last : $this->page + 1)."'>&raquo;</a>";
        $html .="</ul>";

        return $html;
    }

    public function getLink($page)
    {
        $link = $this->baseUrl."?page=".$page;
        foreach($this->appends as $key => $value) {
            $link .= "&".$key."=".$value;
        }
        return $link;
    }
}

/**
 * Mailer class
 */
class Mailer
{
    protected $driver = 'mail';
    protected $fromName = '';
    protected $fromAddress = '';
    protected $smtp_host = '';
    protected $smtp_username = '';
    protected $smtp_password = '';
    protected $smtp_port = '';
    protected $ssl = '';
    protected $queue = false;
    protected static $instance;
    protected $mailer;

    function __construct()
    {
        $this->driver = config('email-driver', 'mail');
        $this->fromAddress = config('email-from-address');
        $this->fromName = config('email-from-name');
        $this->smtp_host = config('email-smtp-host');
        $this->smtp_username = config('email-smtp-username');
        $this->smtp_password = config('email-smtp-password');
        $this->smtp_port = config('email-smtp-port');
        $this->ssl = config('email-ssl');
        $this->queue = config('email-queue');

        //load php mail for us
        require_once path('includes/libraries/phpmailer/PHPMailerAutoload.php');
        try{
            $this->mailer = new PHPMailer(true);
            if ($this->driver == 'smtp') {
                $this->mailer->isSMTP();
                $this->mailer->Host = $this->smtp_host;
                $this->mailer->Port = $this->smtp_port;
                $this->mailer->CharSet = "UTF-8";
                $this->mailer->Encoding = "base64";
                $this->mailer->SMTPAutoTLS = false;
                if (!empty($this->smtp_username) and !empty($this->smtp_password)) {
                    $this->mailer->Username = $this->smtp_username;
                    $this->mailer->Password = $this->smtp_password;
                    $this->mailer->SMTPAuth = true;
                }
            }
            $this->mailer->setFrom($this->fromAddress, $this->fromName);
        } catch(Exception $e) {}
    }

    public static function getInstance()
    {
        //if (static::$instance) return static::$instance;
        static::$instance = new Mailer();
        return static::$instance;
    }

    public function setAddress($address, $name = null)
    {
        try{
            $this->mailer->addAddress($address, $name);
        } catch(Exception $e){}
        return $this;
    }

    public function setSubject($subject)
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    public function setMessage($message)
    {
        $message = str_replace(array("\\n","\\r"), array('', ''), $message);
        $message = html_entity_decode($message, ENT_QUOTES);
        $this->mailer->msgHTML($message);
        return $this;
    }

    public function addAttachment($path)
    {
        $this->mailer->addAttachment($path);
        return $this;
    }

    public function send()
    {
        try {
            $this->mailer->send();
        } catch(Exception $e) {}
    }

    public function template($emailId, $params = array())
    {
        $template = get_email_template($emailId);
        $subject = lang($template['subject']);
        $body = lang($template['body_content']);
        $globalPlaceholders = array(
            'site-url' => url(),
            'site-title' => config('site_title')
        );
        $params = array_merge($globalPlaceholders, $params);

        if (preg_match('#\[header\]#', $body)) {
            //replace the header content
            $headerTemplate = get_email_template('header');
            $headerContent = lang($headerTemplate['body_content']);
            $body = str_replace('[header]', $headerContent, $body);
        }

        if (preg_match('#\[footer\]#', $body)) {
            //replace the footer content
            $footerTemplate = get_email_template('footer');
            $footerContent = lang($footerTemplate['body_content']);
            $body = str_replace('[footer]', $footerContent, $body);
        }

        foreach($params as $key => $value) {
            $body = str_replace('['.$key.']', $value, $body);
            $subject = str_replace('['.$key.']', $value, $subject);
        }
        $body = html_entity_decode($body, ENT_QUOTES);
        $this->setSubject($subject)->setMessage($body);

        return $this;
    }

    public function addTemplate($id, $details = array(), $langId = 'english')
    {
        $expectedDetails = array(
            'title' => '',
            'description' => '',
            'placeholders' => '',
            'subject' => '',
            'body_content' => ''
        );

        /**
         * @var $title
         * @var $description
         * @var $placeholders
         * @var $subject
         * @var $body_content
         */
        extract(array_merge($expectedDetails, $details));
        if (!$this->templateExists($id)) {
            $titleSlug = $id.'_email_template_title';
            $descriptionSlug = $id.'_email_template_description';
            $subjectSlug = $id.'_email_template_subject';
            $messageSlug = $id.'_email_template_message';
            add_language_phrase($titleSlug, $title, $langId, 'email-template');
            add_language_phrase($descriptionSlug, $description, $langId, 'email-template');
            add_language_phrase($subjectSlug, $subject, $langId, 'email-template');
            add_language_phrase($messageSlug, $body_content, $langId, 'email-template');
            db()->query("INSERT INTO `email_templates` (`email_id`,`title`,`description`,`placeholders`,`subject`,`body_content`)
            VALUES('{$id}','{$titleSlug}','{$descriptionSlug}','{$placeholders}','{$subjectSlug}','{$messageSlug}')");
        }
        return true;
    }

    public function templateExists($id)
    {
        $query = db()->query("SELECT * FROM `email_templates` WHERE email_id='{$id}'");
        if ($query and $query->num_rows > 0) return true;
        return false;
    }

}

/**
 * Pusher API
 */
class Pusher {
    //driver
    public $driver;

    public static $instance;

    public function setDriver($driver) {
        $this->driver = $driver;
    }

    public static function getInstance() {
        if (static::$instance) return static::$instance;
        static::$instance = new Pusher();
        return static::$instance;
    }

    public function getDriver() {
        return $this->driver;
    }

    public function lists() {
        $lists = array(
            'ajax' => lang('ajax-long-polling'),
        );

        return fire_hook('pusher.list', $lists);
    }

}

interface PusherInterface {
    /**
     * Method to send message
     */
    public function sendMessage($userid, $type, $details, $subPush = null, $seenUpdate = true);

}

class AjaxPusher implements PusherInterface {
    public function sendMessage($userid, $type, $details, $subPush = null, $seenUpdate = true) {
        $users = (is_array($userid)) ? $userid : array($userid);
        foreach($users as $userid) {
            $cacheName = "ajax-pusher-".$userid;
            $pushes = array(
                'seen' => 0,
                'types' => array()
            );
            if (cache_exists($cacheName)) {
                $pushes = get_cache($cacheName);
            }
            if (!isset($pushes['types'][$type])) $pushes['types'][$type] = array();
            if ($subPush) {
                if (!isset($pushes['types'][$type][$subPush])) $pushes['types'][$type][$subPush] = array();
                $pushes['types'][$type][$subPush][md5(perfectSerialize($details))] = $details;
            } else {
                $pushes['types'][$type][md5(perfectSerialize($details))] = $details;
            }

            if ($seenUpdate) $pushes['seen'] = 0;
            set_cacheForever($cacheName, $pushes);
            fire_hook("ajax.push.notification", null, array($userid));
        }
    }

    public function result($userid = null) {
        $userid = ($userid) ? $userid : get_userid();
        $cacheName = "ajax-pusher-".$userid;
        $pushes = array('seen' => 1, 'userid' => get_userid());
        if (cache_exists($cacheName)) {
            $pushes = get_cache($cacheName);
        }
        //forget_cache($cacheName);
        $pushes['userid'] = get_userid();
        $p = $pushes;
        $p['seen'] = 1;

        set_cacheForever($cacheName, $p); //updating the cache
        $pushes = fire_hook('ajax.push.result', $pushes);
        return json_encode($pushes);

        return false;
    }

    public function reset($type, $subPush = null, $delete = false) {
        $userid =  get_userid();
        $cacheName = "ajax-pusher-".$userid;
        if (cache_exists($cacheName)) {
            $pushes = get_cache($cacheName);
            if ($subPush) {
                $pushes['types'][$type][$subPush] = array();
                if ($delete) {
                    if (isset($pushes['types'][$type][$subPush])) unset($pushes['types'][$type][$subPush]);
                }
            } else {
                $pushes['types'][$type] = array();
                if ($delete) {
                    unset($pushes['types'][$type]);
                }
            }

            set_cacheForever($cacheName, $pushes); //updating the cache
        }

        return true;
    }

    public function resetAll() {
        $userid =  get_userid();
        $cacheName = "ajax-pusher-".$userid;
        forget_cache($cacheName);
    }
}

/**
 * Task Manager
 */
class TaskManager {

    private static $tasks = array();

    public static function add($taskId, $func) {
        static::$tasks[$taskId] = $func;
    }

    public static function run() {
        foreach(static::$tasks as $taskId => $func) {
            call_user_func($func);
        }

        return true;
    }
}


/**
 *
 * CSRF Protection
 */

class CSRFProtection {
    public static function getToken() {
        $sessionArray = (isset($_SESSION['csrf_tokens'])) ? $_SESSION['csrf_tokens'] : array();
        $newToken = md5(uniqid(mt_rand(), true).time());
        $sessionArray[] = $newToken;
        $_SESSION['csrf_tokens'] = $sessionArray;
        return $newToken;
    }

    public static function validate($expire = true) {
        $token = input("csrf_token");
        if (App::getInstance()->isApi() or is_ajax()) return true;
        $tokens = (isset($_SESSION['csrf_tokens'])) ? $_SESSION['csrf_tokens'] : array();
        if (!$tokens and !is_ajax()) exit("Unauthorized");
        if (in_array($token, $_SESSION['csrf_tokens'])) {
            if (!$expire) unset($_SESSION['csrf_tokens'][$token]);
            return true;
        }


        if (!is_loggedIn() and is_ajax()) exit('login');
        exit("Unauthorized");
    }

    public static function embed() {
        echo "<input type='hidden' name='csrf_token' value='".CSRFProtection::getToken()."'/>";
    }
}