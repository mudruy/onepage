<?php

/**
 * Description of Aggregate
 *
 * @author mudruy
 */
class Ap_Log_Aggregate {

    CONST ERRORLOGDESCRIPTION = 'ERRORLOGDESCRIPTION';

    public static function rotate() 
    {
        $Cfg = Zend_Registry::get('Config');
        $logDestination = $Cfg->logging->basePath;

        $pthInfo = pathinfo($logDestination);

        $logCacheFile = $pthInfo['dirname'] . '/cache.tmp';
        copy($logDestination, $logCacheFile);

        $allFileContent = file($logCacheFile);

        $logArray = self::_findError($allFileContent);
        self::_sendMail($logArray);
        self::_createArchive($pthInfo['dirname'], $logCacheFile);
        
        $fl = fopen($logDestination, 'w');
        fclose($fl);
        
        unlink($logCacheFile);
    }

    /**
     * parse file
     * @param array $allFileContent
     * @return int 
     */
    protected static function _findError($allFileContent) 
    {
        $logArray = array();
        $logNewElement = array();

        foreach ($allFileContent as $fileStr) {
            // ищем маркер ERROR: 
            $posErr = strpos($fileStr, self::ERRORLOGDESCRIPTION);
            if ($posErr <> 0) {
                $errInfo = substr($fileStr, $posErr + strlen(self::ERRORLOGDESCRIPTION . ' : ')) . '<br>';
                // пытаемся вставить это ErrInfo в новый массив
                $posInfo = -1;
                foreach ($logArray as $logIndex => $logElement)
                    if ($logElement['error'] == $errInfo)
                        $posInfo = $logIndex;

                if ($posInfo <> -1) {
                    $logArray[$posInfo]['count'] += 1;
                } else {
                    $logNewElement['error'] = $errInfo;
                    $logNewElement['count'] = 1;
                    array_push($logArray, $logNewElement);
                }
            }
        }
        return $logArray;
    }
    
    /**
     * send message
     * @param array $logArray 
     */
    protected static function _sendMail($logArray) 
    {
        $bodyErrMessage = '';
        if (count($logArray) == 0) 
            return;
        foreach($logArray as $logElement)
           $bodyErrMessage .= $logElement['error'].' : '.
                              $logElement['count']."\r\n";
       // выбираем для него список рассылки 
        $Cfg = Zend_Registry::get('Config');
        $mailAddress = $Cfg->logging->adminMail;
        $from = $Cfg->logging->from;
        $Subject = $Cfg->logging->subject;
        $mailErr = new Zend_Mail();
        $emails = explode(',',$mailAddress);
        $mailErr->setBodyText($bodyErrMessage)
            ->setFrom($from, '')
            ->setSubject($Subject);
        
        foreach($emails AS $email){
            $mailErr->addTo(trim($email));
        }
        $mailErr->send();
    }
    
    /**
     * zip log
     * @param string $dirName
     * @param string $logCacheFile 
     */
    protected static function _createArchive($dirName,$logCacheFile)
    {
       $logZip = new ZipArchive();
       $logZipFileName = $dirName.'/'.date("Y-m-d H:i:s").'.zip';
       $logZip->open($logZipFileName, ZIPARCHIVE::CREATE);
       $logZip->addFile($logCacheFile, 'sys.log');
       $logZip->close();
    }
}

?>
