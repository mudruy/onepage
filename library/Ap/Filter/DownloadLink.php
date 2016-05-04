<?php

class Ap_Filter_DownloadLink
{
    protected $_filename;
    protected $_template_filename;
    protected $_layout_id;
    const COOKIE_NAME = 'user_id';
    const DEFFILENAME = 'Fast File Downloader';
    
    public function __construct($filename = '') 
    {
        $this->_filename = urlencode(trim($filename . ' Downloader_'));
        $this->_template_filename = $filename;
    }
    
    public function getDownloadLink($user_id=false, $filename = false)
    {
        $path_url = SOFT_URL;
        switch ($this->getLayoutId()) {
            case 6:
                $path_url = '/getoxy/';
                if(mb_strlen($filename)>0) {
                    $filename = str_replace('+','_',urlencode(trim($filename))). '_Downloader';
                } else {
                    $filename = 'Downloader_';
                }
                break;
            case 8:
                $path_url = '/getfilerammer/';
                $filename = 'FileRammer_';
                break;
            case 10:
                if(mb_strlen($filename)>0) {
                    $filename = str_replace('+','_',urlencode(trim($filename))). '_Downloader';
                } else {
                    $filename = 'Downloader_';
                }
                break;
            case 11:
                $path_url = '/getfilerammer/';
                $filename = 'FileRammer_';
                break;
            case 12:
                $path_url = '/getmedia/';
                if(mb_strlen($filename)>0) {
                    $filename = str_replace('+','_',urlencode(trim($filename))). '_Downloader';
                } else {
                    $filename = 'Downloader_';
                }
                break;
            case 14:
                $path_url = '/getoxy/';
                if(mb_strlen($filename)>0) {
                    $filename = str_replace('+','_',urlencode(trim($filename))). '_Downloader';
                } else {
                    $filename = 'Downloader_';
                }
                break;
            case 18:
                $path_url = '/getoxy/';
                if(mb_strlen($filename)>0) {
                    $filename = str_replace('+','_',urlencode(trim($filename))). '_Downloader';
                } else {
                    $filename = 'Downloader_';
                }
                break;
            case 20:
                $path_url = '/getyoutube/';
                if(mb_strlen($filename)>0) {
                    $filename = str_replace('+','_',urlencode(trim($filename))). '_Downloader';
                } else {
                    $filename = 'Downloader_';
                }
                break;
            case 21:
                $path_url = '/getmedia/';
                //$path_url = '/getoxy/';
                if(mb_strlen($filename)>0) {
                    $filename = str_replace('+','_',urlencode(trim($filename))). '_Downloader';
                } else {
                    $filename = 'Downloader_';
                }
                break;
            default:
                if(mb_strlen($filename)>0) {
                    $filename = str_replace('+','_',urlencode(trim($filename))). '_Downloader';
                } else {
                    $filename = 'Downloader_';
                }
                break;
        }
        
        $user_sub_id = $user_id - floor($user_id/1000)*1000;
        if($user_sub_id == 998) {
            $path_url = '/getoxy/';
        }
            
            
        $url = $path_url.(($filename)?$filename:$this->_filename).'_';
        $url.= (($user_id)?(int)$user_id.$this->getLayoutId():0).'.exe';
        $url = self::geterateSecretUrl($url);
        return ($url);
    }
    
    /**
     *
     * @param string $url (like '/p/files/top_secret.pdf')
     * @return string (like $url .= ?st&e)
     */
    public static function geterateSecretUrl($url) {
        
        $ExpPeriod = Zend_Registry::get('Config')->cookie->guard->timeout;
        $secret = HOTLINKSECRET; // To make the hash more difficult to reproduce.
        $path   = urldecode($url);//'/p/files/top_secret.pdf'; // This is the file to send to the user.
        $expire = time()+$ExpPeriod; // At which point in time the file should expire. time() + x; would be the usual usage.

        $md5 = base64_encode(md5($secret . self::getIP() .  $path . $expire, true)); // Using binary hashing.
        $md5 = strtr($md5, '+/', '-_'); // + and / are considered special characters in URLs, see the wikipedia page linked in references.
        $md5 = str_replace('=', '', $md5); // When used in query parameters the base64 padding character is considered special.
        $url.='?st='.$md5.'&e='.$expire;
        return $url; 
    }
    
    public function addForeignVars($request, $url){
        $visitor_id = $request->getRequest()->getParam('visitor_id', '');
        $bid        = $request->getRequest()->getParam('bid', '');
        $zid        = $request->getRequest()->getParam('zid', '');
        $fileid     = $request->getRequest()->getParam('fileid', '');
        if(!empty($visitor_id)){
            $url .= '&visitor_id='.$visitor_id;
        }
        if(!empty($bid)){
            $url .= '&bid='.$bid;
        }
        if(!empty($zid)){
            $url .= '&zid='.$zid;
        }
        if(!empty($fileid)){
            $url .= '&fileid='.$fileid;
        }
        return $url; 
    }


    public function getLayoutId()
    {
        return $this->_layout_id;
    }
    
    public function setLayoutId($layout_id)
    {
        $this->_layout_id = str_pad($layout_id, 2, '0', STR_PAD_LEFT);
        return $this;
    }
    
    public function getFilename()
    {
        //return self::DEFFILENAME;
        if ($this->_layout_id ==7 or empty($this->_template_filename)) {
            return self::DEFFILENAME;
        } else 
        return substr($this->_template_filename, 0, 50) . ' Downloader';
    }
    
    static public function setUserId($user_id)
    {
        $ExpPeriod = Zend_Registry::get('Config')->cookie->timeout;
        $_COOKIE[self::COOKIE_NAME] = $user_id;
        if ( APPLICATION_ENV != 'testing' ){
            setcookie(self::COOKIE_NAME, $user_id, time() + $ExpPeriod,'/');
        }
    }
    
    /**
     * clear from space
     * @param string $filename
     * @return string 
     */
    public static function normalize_filename($filename) {
        return preg_replace('/\s/ui','',mb_strtolower(urldecode ($filename), 'UTF-8'));
    }
    
    /**
     * ip getter
     * @return boolean 
     */
    public static function getIP() {
        if (getenv('REMOTE_ADDR'))
            $user_ip = getenv('REMOTE_ADDR');
        elseif (getenv('HTTP_FORWARDED_FOR'))
            $user_ip = getenv('HTTP_FORWARDED_FOR');
        elseif (getenv('HTTP_X_FORWARDED_FOR'))
            $user_ip = getenv('HTTP_X_FORWARDED_FOR');
        elseif (getenv('HTTP_X_COMING_FROM'))
            $user_ip = getenv('HTTP_X_COMING_FROM');
        elseif (getenv('HTTP_VIA'))
            $user_ip = getenv('HTTP_VIA');
        elseif (getenv('HTTP_XROXY_CONNECTION'))
            $user_ip = getenv('HTTP_XROXY_CONNECTION');
        elseif (getenv('HTTP_CLIENT_IP'))
            $user_ip = getenv('HTTP_CLIENT_IP');
        elseif (getenv('X-Real-IP'))
            $user_ip = getenv('X-Real-IP');
        $user_ip = trim($user_ip);
        if (empty($user_ip))
            return false;
        if (!preg_match("/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/", $user_ip))
            return false;
        return $user_ip;
    }

}