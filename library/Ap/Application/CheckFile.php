<?php

/**
 * Description of nvCheckGlobalDisable
 *
 * @author pavel
 */
class Ap_Application_CheckFile implements Ap_Application_Checker
{

    public $env;

    /**
     * тут хак для тестов. отключать тестовое окружение не возможно во время теста )
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->env = APPLICATION_ENV;

        if (array_key_exists('env', $params))
            $this->env = $params['env'];

        $this->_tmp_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR
            . 'data' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    }

    /**
     * реализует проверку блокировки
     * @return boolean
     */
    public function hasLock()
    {
        global $argv;
        global $tasks;
        $lockFile = $this->_tmp_dir . $this->env . '.lck';
        if ('cli' == php_sapi_name() && array_key_exists(2, $argv) && ($argv[2] == 'enable-project' || $argv[2] == $tasks['enable-project'])) {
            return false;
        }

        if (file_exists($lockFile))
            return true;
        return false;
    }

}