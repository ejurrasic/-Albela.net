<?php
interface CDNEngineInterface {
    public function upload($server, $file, $uri);

    /**
     * Method to validate settings
     * @param array $settings
     * @return boolean
     */
    public function validateSettings($settings);

    /**
     * Method to output proper link for media
     * @param string $media
     * @param array $server
     * @return string
     */
    public function output($media, $server);

    /**
     * Validation error message
     * @return string
     */
    public function validationError();

    /**
     * Method to delete files from CDN
     * @param string $media
     * @param array $server
     * @return boolean
     */
    public function delete($media, $server);
}

class  AmazonCDNEngine implements CDNEngineInterface {
    public function upload($server, $file, $uri) {
        $this->settings = $settings = perfectUnserialize($server['settings']);
        $s3 = $this->load();

        try {
            if ($s3->putObjectFile($file, $settings['bucket'], $uri, \S3::ACL_PUBLIC_READ)) {
                return true;
            } else{
                return false;
            }
        } catch(\Exception $e) {
            //print_r($e);
            //exit;
            return false;
        }
    }

    public function validateSettings($settings) {
        return true;
    }

    private function load() {
        include_once path("plugins/cdn/engine/S3.php");
        $s3 = new \S3($this->settings['id'], $this->settings['key']);
        return $s3;
    }

    public function output($media, $server) {
        $this->settings = $settings = perfectUnserialize($server['settings']);
        return 'http://'.$this->settings['bucket'].'.'.$this->settings['endpoint'].'/'.$media;
    }

    public function validationError() {
        return "Failed, please confirm the settings";
    }

    public function delete($media, $server) {
        $this->settings = $settings = perfectUnserialize($server['settings']);
        $s3 = $this->load();

        try {
            if (\S3::deleteObject($settings['bucket'], $media)) {
                return true;
            } else{
                return false;
            }
        } catch(\Exception $e) {
            //print_r($e);
            //exit;
            return false;
        }
    }
}


class  HostedCDNEngine implements CDNEngineInterface {
    public function upload($server, $file, $uri) {
        $this->settings = $settings = perfectUnserialize($server['settings']);
        try {
            $target_url = $this->settings['endpoint'].$this->settings['file'].'.php?action=save&name='.$uri.'&key='.$this->settings['key'];
            //$post = array('extra_info' => '123456','file_contents'=>new CurlFile($file));

            $post = array('extra_info' => '123456','file_contents'=>'@'.$file);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$target_url);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $result=curl_exec ($ch);
            curl_close ($ch);
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function validateSettings($settings) {
        return true;
    }


    public function output($media, $server) {
        $this->settings = $settings = perfectUnserialize($server['settings']);
        return $this->settings['endpoint'].$media;
    }

    public function validationError() {
        return "Failed, please confirm the settings";
    }

    public function delete($media, $server) {
        $this->settings = $settings = perfectUnserialize($server['settings']);
        $target_url = $this->settings['endpoint'].$this->settings['file'].'.php?action=delete&name='.$media.'&key='.$this->settings['key'];
        //exit($target_url);
        try{
            @file_get_contents($target_url);
        } catch(Exception $e) {}
    }
}
