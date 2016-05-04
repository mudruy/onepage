<?php

class Ap_Filter_File_DistribGetter {
    
    public $distrib_file;
    public $cache_file, $xaccel_target;
    
    protected $_cash;


    CONST MAX_TIME_LOAD = 600;
    
    /**
     * init class 
     */
    public function __construct() {
        set_time_limit(self::MAX_TIME_LOAD);
    }


    /**
     * main method 
     */
    public function execute($filename = 'distrib', $xaccel='/protecteddistroxy/') {
        $this->distrib_file  = WWW_PATH . "distrib/$filename.bin";
        $this->xaccel_target = $xaccel . "$filename.bin";
        
        //$paths = explode('/',$_SERVER['REQUEST_URI']);
        $this->cache_file = CACHE_PATH .basename($this->distrib_file).".cache";
        
        $dir = dirname(__FILE__);
        chdir($dir);
        if (!file_exists($this->distrib_file))
	{
            die("File not found: {$this->distrib_file}");
	}

        $this->cache_read();

        if (false === $this->_cache || $this->cache_expired())
            $this->_cache = $this->cache_create();

        if (false == $this->_cache)
        {
            die("Can't create cache file");
        }
        $this->_getFile();
    }
    
    /**
     * read cache from file cache_file and unserialize
     * @return array 
     */
    public function cache_read(){
        $this->_cache = false;
        $f = fopen($this->cache_file, 'r');
        if (false !== $f)
        {
            flock($f, LOCK_SH);

            $tmp = fread($f, 1024);
            if (false != $tmp)
            {
                $this->_cache = unserialize($tmp);
                $this->_cache = isset($this->_cache['time'], $this->_cache['hash']) ? $this->_cache : false;
            }

            flock($f, LOCK_UN);
            fclose($f);
        }
        return $this->_cache;
    }

    /**
     * check md5 summ expaired
     * @return boolean 
     */
    public function cache_expired(){
        return (filemtime($this->distrib_file) === $this->_cache['time']) ? false : true;
    }

    /**
     * create cache in file
     * @return array 
     */
    public function cache_create(){
        $this->_cache = false;
        $f = fopen($this->cache_file, 'w');
        if (false !== $f)
        {
            flock($f, LOCK_EX);

            $time = filemtime($this->distrib_file);
            $hash = base64_encode(pack("H*", md5_file($this->distrib_file)));

            $this->_cache = array('file' => $this->distrib_file, 'time' => $time, 'hash' => $hash);
            fwrite($f, serialize($this->_cache));

            flock($f, LOCK_UN);
            fclose($f);
        }
        return $this->_cache;
    }
    
    /**
     * read file from filesystem and get  
     */
    protected function _getFile() {
        
        //hook for different countries
        if ($this->distrib_file == "../distrib/distrib_custom.bin") {
            $header_filename = basename("../distrib/distrib.bin");
        } else {
            $header_filename = basename($this->distrib_file);
        }
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='. $this->_cache['hash'] . $header_filename);
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($this->distrib_file));
        header('Content-MD5: '.$this->_cache['hash']);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('X-Accel-Redirect: ' . $this->xaccel_target);

        //readfile($this->distrib_file);
    }
}