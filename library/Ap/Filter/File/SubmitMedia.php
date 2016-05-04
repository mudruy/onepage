<?php

class Ap_Filter_File_SubmitMedia implements Zend_Filter_Interface
{
    protected $_object;
	public function __construct($Object)
	{
        $this->_object = $Object;
    }
    
    public function getFolder()
    {
        return $this->getObject()->getFolder();
    }
    public function getObject()
    {
    	return $this->_object;
    }
    public function delete()
    {
        unlink($this->getObject()->getFilepath());
    }
    
 	public function filter($fileSource)
 	{
        if(!is_file($fileSource)){
            throw new Exception('file not exit');
        }
        
        $ext = explode('.', $fileSource);
        $ext = $ext[count($ext)-1];
        $ext = substr(strrchr($fileSource, '.'), 1);
        $ext = substr($fileSource, strrpos($fileSource, '.') + 1);
        $ext = strtolower(preg_replace('/^.*\.([^.]+)$/D', '$1', $fileSource));
        $this->getObject()->setFromArray(array(
            'fileext'   => $ext,
            'filesize'  => filesize($fileSource),
            'filetitle' => basename($fileSource),
            'filename'  => md5($this->getObject()->id.'_'.uniqid().SALT)
        ))->save();
        if(!copy($fileSource, $this->getObject()->getFilePath())){
            throw new Exception('file not copy');
        }
        return true;
    }
}