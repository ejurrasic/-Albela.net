<?php
/**
 * Default class to resolve urls
 */
namespace Embed\RequestResolvers;

class Curl implements RequestResolverInterface
{
    protected $isBinary;
    protected $content;
    protected $result;
    protected $url;
    protected $config = array(
        CURLOPT_MAXREDIRS => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING => '',
        CURLOPT_AUTOREFERER => true,
        CURLOPT_USERAGENT => 'Embed PHP Library',
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    );

    public static $binaryContentTypes = array(
        '#image/.*#',
        '#application/(pdf|x-download|zip|pdf|msword|vnd\\.ms|postscript|octet-stream|ogg)#',
        '#application/x-zip.*#',
    );

    /**
     * {@inheritdoc}
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config)
    {
        $this->config = $config + $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrl($url)
    {
        $this->result = $this->content = null;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->getResult('url');
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpCode()
    {
        return intval($this->getResult('http_code'));
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->getResult('mime_type');
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if ($this->content === null) {
            $this->resolve();
        }

        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestInfo()
    {
        if ($this->result === null) {
            $this->resolve();
        }

        return $this->result;
    }

    /**
     * Get the result of the http request
     *
     * @param string $name Parameter name
     *
     * @return null|string The result info
     */
    protected function getResult($name)
    {
        if ($this->result === null) {
            $this->resolve();
        }

        return isset($this->result[$name]) ? $this->result[$name] : null;
    }

    /**
     * Resolves the current url and get the content and other data
     */
    protected function resolve()
    {
        $this->content = '';
        $this->isBinary = null;

        $tmpCookies = url('storage/cookies/embed-cookies.txt');

        $connection = curl_init();
        $mr = 5;
        curl_setopt_array($connection, array(
                CURLOPT_RETURNTRANSFER => false,
                //CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_URL => $this->url,
                CURLOPT_COOKIEJAR => $tmpCookies,
                CURLOPT_COOKIEFILE => $tmpCookies,
                CURLOPT_HEADERFUNCTION => array($this, 'headerCallback'),
                CURLOPT_WRITEFUNCTION => array($this, 'writeCallback'),
            ) + $this->config);
        if (!ini_get('open_basedir') && !filter_var(ini_get('safe_mode'), FILTER_VALIDATE_BOOLEAN)) {
            curl_setopt($connection, CURLOPT_FOLLOWLOCATION, $mr > 0);
            curl_setopt($connection, CURLOPT_MAXREDIRS, $mr);
        } else {

            curl_setopt($connection, CURLOPT_FOLLOWLOCATION, false);
            if ($mr > 0) {
                $newurl = curl_getinfo($connection, CURLINFO_EFFECTIVE_URL);

                $rch = curl_copy_handle($connection);
                curl_setopt($rch, CURLOPT_HEADER, true);
                curl_setopt($rch, CURLOPT_NOBODY, true);
                curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
                curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
                do {
                    curl_setopt($rch, CURLOPT_URL, $newurl);
                    $header = curl_exec($rch);
                    if (curl_errno($rch)) {
                        $code = 0;
                    } else {
                        $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                        if ($code == 301 || $code == 302) {
                            preg_match('/Location:(.*?)\n/', $header, $matches);
                            $newurl = trim(array_pop($matches));
                        } else {
                            $code = 0;
                        }
                    }
                } while ($code && --$mr);
                curl_close($rch);
                if (!$mr) {
                    if ($maxredirect === null) {
                        trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
                    } else {
                        $maxredirect = 0;
                    }
                    return false;
                }
                curl_setopt($connection, CURLOPT_URL, $newurl);
            }
        }



        $result = curl_exec($connection);

        $this->result = curl_getinfo($connection) ?: array();

        if (!$result) {
            $this->result['error'] = curl_error($connection);
            $this->result['error_number'] = curl_errno($connection);
        }

        curl_close($connection);

        if (($content_type = $this->getResult('content_type'))) {
            if (strpos($content_type, ';') !== false) {
                list($mimeType, $charset) = explode(';', $content_type);

                $this->result['mime_type'] = $mimeType;

                $charset = substr(strtoupper(strstr($charset, '=')), 1);

                if (!empty($charset) && !empty($this->content) && ($charset !== 'UTF-8')) {
                    $this->content = @mb_convert_encoding($this->content, 'UTF-8', $charset);
                }
            } elseif (strpos($content_type, '/') !== false) {
                $this->result['mime_type'] = $content_type;
            }
        }
    }

    protected function headerCallback($connection, $string)
    {
        if (($this->isBinary === null) && strpos($string, ':')) {
            list($name, $value) = array_map('trim', explode(':', $string, 2));

            if (strtolower($name) === 'content-type') {
                $this->isBinary = false;

                foreach (self::$binaryContentTypes as $regex) {
                    if (preg_match($regex, strtolower($value))) {
                        $this->isBinary = true;
                        break;
                    }
                }
            }
        }

        return strlen($string);
    }

    protected function writeCallback($connection, $string)
    {
        if ($this->isBinary) {
            return 0;
        }

        $this->content .= $string;

        return strlen($string);
    }
}
