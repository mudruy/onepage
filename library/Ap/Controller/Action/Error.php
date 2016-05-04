<?php

class Ap_Controller_Action_Error extends Zend_Controller_Action{
    
    public function init()
    {
        $this->_helper->layout->setLayout('error');
    }
    
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                return $this->_forward('notfound');
                break;
            default:
                $this->getResponse()->setHttpResponseCode(500);
                break;
        }
        
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        } else {
            $logger = Zend_Registry::get('Log');

            $logger->log( Ap_Log_Aggregate::ERRORLOGDESCRIPTION . ' : ' . $errors->exception->getMessage(), Zend_Log::ERR);
            $logger->log('    Request: '.$this->_request->getRequestUri(), Zend_Log::ERR);
            $logger->log('    File: '.$errors->exception->getFile().":".$errors->exception->getLine(), Zend_Log::ERR);
            $logger->log('    Trace: ', Zend_Log::ERR);
            foreach(explode(PHP_EOL, $errors->exception->getTraceAsString()) AS $row)
                $logger->log('        '.$row, Zend_Log::ERR);
        }
        $this->view->request   = $errors->request;
    }
    
    public function notfoundAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }
}
