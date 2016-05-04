<?php

class Ap_Controller_Plugin_AjaxModal extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->AjaxModalMode = (bool)$request->getParam('AjaxModalMode', false);
    }
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        if($request->getParam('AjaxModalMode', false)){
            $titles = $view->headTitle();
            $title = (isset($titles[0]))?$view->translate($titles[0]):'';
            $vars = array('status'=>false, 'title'  =>  $title);
            if(Zend_View_Helper_Placeholder_Registry::getRegistry()->containerExists('ajaxModal')){
                $vars['html'] = $view->placeholder('ajaxModal')->toString();
                $vars['status'] = true;
            }
            $body = Zend_Json::encode($vars);
            $this->getResponse()->setBody($body);
            $this->getResponse()->setHeader('Content-Type', 'application/json');
            
            $layout = Zend_Layout::getMvcInstance();
            if (null !== $layout) {
                $layout->disableLayout();
            }
        }
    }
}
