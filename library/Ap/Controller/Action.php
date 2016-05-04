<?php

require_once(APPLICATION_PATH."/../library/base64.php");
class Ap_Controller_Action extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->filterForm = new Application_Model_ResultSearchForm();
        $this->view->formatter = new Ap_View_Formatter();
        $access = $this->getRequest()->getParam('allow', '');
        if($access == '404'){
            Am_SEO::setMeta($this,'Page not found','Page not found','');
            throw new Zend_Controller_Action_Exception('This page does not exist.', 404); 
        }
        
        $robots = $this->getRequest()->getParam('robots', 'index, follow');
        if(!empty($robots)){
            $this->view->headMeta()->setName("robots",$robots);
        }
    }
}