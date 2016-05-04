<?php

/**
 * Merged files for config - application.ini, databases.ini, etc.
 *
 * @author gordeev
 */
class Ap_Config_Loader {
    
    /**
     * init class 
     */
    protected static function init() {
        //инициализируем нужные классы
        require_once 'Zend/Loader/Autoloader.php';
        require_once LIBRARY_PATH . '/Ap/Search/Finder.php';

        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->suppressNotFoundWarnings(false);
        $loader->setFallbackAutoloader(true);
        $loader->autoload('Zend_Cache_Frontend_File');
        $loader->autoload('Zend_Config_Ini');
        $loader->autoload('Zend_Cache');
    }
    
    /**
     * find ini files or just load it
     * @return array 
     */
    protected static function findFiles() {
        //$t1 = microtime( true );
        //формируем пути к ini файлам
        $base_path = APPLICATION_PATH .
            DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
        
        $files = Ap_Search_Finder::type('file')
            ->name('*.ini')->in( $base_path );
        //можно сделать так чтобы грузились все инишки из папки config. надо узнать - надо ли
        
        $base_path_common = LIBRARY_PATH .
            DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
        
        $files_common = Ap_Search_Finder::type('file')
            ->name('*.ini')->in( $base_path_common );
        $files = array_merge($files, $files_common);
        
        //делаем массив в котором application файл всегда в начале
        //а development всегда в конце. и работает development инишка
        //тольков тестовом и dev окружении
        $filename_files = array();
        foreach ($files as $key => $file) {
            $filename_files[ pathinfo( $file, PATHINFO_FILENAME) ] = $file;
        }
        
        if( count($files) != count($filename_files) ) {
            throw new Exception('Config file have duplicate names');
        }
        
        //запоминаем application.ini
        if( array_key_exists('application', $filename_files) ) {
            $first_file = $filename_files['application'];
            unset($filename_files['application']);
        } else {
            throw new Exception('application.ini not exist');
        }
        
        $pre_pre_last_file = false;
        if( array_key_exists('seo', $filename_files)  ) {
            $pre_pre_last_file = $filename_files['seo'];
            unset($filename_files['seo']);
        }
        
        $pre_last_file = false;
        if( array_key_exists('local', $filename_files)  ) {
            $pre_last_file = $filename_files['local'];
            unset($filename_files['local']);
        } 
        
        //запоминаем development в нужных окружениях
        $last_file = false;
        if( array_key_exists('development', $filename_files)  ) {
            if ( APPLICATION_ENV != 'production' ){
                $last_file = $filename_files['development'];
            }
            unset($filename_files['development']);
        } else {
            if ( APPLICATION_ENV != 'production' ){
                throw new Exception('development.ini not exist');
            }
        }
        
        //формируем упорядоченный массив
        $sort_files = array();
        $sort_files['application'] = $first_file;
        foreach ($filename_files as $key => $file) {
            $sort_files[ $key ] = $file;
        }
        if( !empty( $pre_pre_last_file ) ) {
            $sort_files['seo'] = $pre_pre_last_file;
        }
        if( !empty( $pre_last_file ) ) {
            $sort_files['local'] = $pre_last_file;
        }
        if( !empty( $last_file ) ) {
            $sort_files['development'] = $last_file;
        }
        return $sort_files;

    }

    /**
     * return merged config
     * @return $result array
     */
    public static function getConfig($editDomain = false)
    {
        $constants_file_path = LIBRARY_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'constants.php';
        
        require_once $constants_file_path;
        if($editDomain and isset($_SERVER['HTTP_HOST']) and $_SERVER['HTTP_HOST'])
            $mainConstants['DEFAULTDOMAIN'] = $_SERVER['HTTP_HOST'];
        if(isset($mainConstants) and count($mainConstants))
            foreach($mainConstants as $key => $value)
                if(!defined($key))
                    define($key, $value);
        self::init();
        $files = self::findFiles();
        //искуственно добавляем путь к файлу констант в наблюдаемые зендом пути. 
        //для перегенеривания конфига
        $files_for_master = array_merge($files, array('constants_file_name' => $constants_file_path));
        
        //настраиваем и вытаскиваем из фабрики экземпляр кеша
        $frontendOptions = array();
        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH . DIRECTORY_SEPARATOR
            . 'data' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR
        );
	
        $frontend = new Zend_Cache_Frontend_File(
                array(
                    'automatic_serialization' => true,
                    'master_files' => $files_for_master)
        );

        $cache = Zend_Cache::factory($frontend, 'File', $frontendOptions, $backendOptions);

        $key_conf = 'config' . APPLICATION_ENV;
        if (($result = $cache->load($key_conf)) === false) {
            // save in $result
            //мержим все конфиги
            $config_app = false;
            
            foreach ($files as $file) {
                if( $config_app === false  ) {
                    $config_app = new Zend_Config_Ini($file, APPLICATION_ENV, array('allowModifications' => true));
                } else {
                    $add_conf = new Zend_Config_Ini($file, APPLICATION_ENV, array('allowModifications' => true));
                    $config_app = $config_app->merge( $add_conf );
                }
                
            }
            $result = $config_app->toArray();

            $cache->save($result, $key_conf);
        }
        if($editDomain and isset($_SERVER['HTTP_HOST']) and $_SERVER['HTTP_HOST'])
            $result['resources']['frontController']['baseUrl'] = 'http://'.$_SERVER['HTTP_HOST'].'/';
        return $result;
    }
    

}

?>
