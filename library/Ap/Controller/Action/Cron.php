<?php


class Ap_Controller_Action_Cron extends Zend_Controller_Action
{
    public function init()
	{
		if (PHP_SAPI != 'cli') {
        	throw new Exception('only cli usage');
        }
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}
    
    public function indexAction()
    {
        
    }
    
    public function rotateLogAction() 
    {
        Ap_Log_Aggregate::rotate();
    }
}