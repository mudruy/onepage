<?php
class Ap_Application_Bootstrap_Bootstrap 
    extends Zend_Application_Bootstrap_Bootstrap 
{
    function run()
    {
        $this->_startSession();
        parent::run();
    }
    
    protected function _startSession()
    {
        Zend_Session::start();
    }
    
    protected function _initCloser()
    {
        $config = new Zend_Config($this->getOptions(), true);
        $check = new Ap_Application_CheckFile();
        $config = $config->toArray();
        if ( $check->hasLock() ) {
            header("We're busy updating our site. We'll back to you as soon as we're done. ", true, 503);
            if (array_key_exists('constants', $config) && array_key_exists('PATH_TO_CLOSE_PAGE', $config['constants']) &&
                !empty($config['constants']['PATH_TO_CLOSE_PAGE'])) {
                include( $config['constants']['PATH_TO_CLOSE_PAGE'] );
            } else {
                echo '<div><h1>Service is down for upgrade</h1></div>';
            }
            die;
        }
    }
    
    protected function setconstants($constants) 
    {
        foreach($constants as $key => $value)
            if(!defined($key))
                define($key, $value);
    }
	
    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('Config', $config);
        return $config;
    }
    
    protected function _initAutoloader() 
    {
        $modelsDirs = array(LIBRARY_PATH . '/models', APPLICATION_PATH . '/models');
        foreach ($modelsDirs as $modelsDir) {
            if(!is_dir($modelsDir))
                continue;
            $files = scandir($modelsDir);
            foreach($files AS $file)
                if($file != '.' 
                    and $file != '..' 
                    and $file!='.svn' 
                    and is_dir($modelsDir . '/' . $file)
                )
                new Zend_Application_Module_Autoloader(array(
                    'namespace' => $file,
                    'basePath' => $modelsDir.'/'.$file));
        }
    }
    
    protected function _initPaginator()
    {
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    	Zend_Paginator::setDefaultItemCountPerPage(50);
    	Zend_Paginator::setDefaultPageRange(3);
    }
	
    protected function _initCaptcha()
    {
        Zend_Captcha_Word::$VN = Zend_Captcha_Word::$CN = range(0, 9);
    }
        
    protected function _initCache()
    {
        $options = $this->getOption('cache');
        
        $this->bootstrap('cachemanager');
        $cacheManager = $this->getResource('cachemanager');
        Zend_Registry::set('CacheManager', $cacheManager);
        
        $this->bootstrap('db');
        if($options['metadata']['status'])
            Zend_Db_Table_Abstract::setDefaultMetadataCache($cacheManager->getCache('database'));
        
        $this->bootstrap('view');
        $this->getResource('view')->cache = $cacheManager->getCache('pages');
	
    }
    
    protected function _initLogger()
    {
        $this->bootstrap('log');
        Zend_Registry::set('Log', $this->getResource('log'));
    }
    
}