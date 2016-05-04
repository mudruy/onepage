<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->users = Ap_Db_Core::getTable('User')->fetchAll();
    }
    
    public function addAction()
    {
        $form = new User_Form_Add;
        if ($this->getRequest()->isPost() and $form->isValid($_POST)) {
            Ap_Db_Core::getTable('User')->add($form->getObject());
            $this->_helper->getHelper('FlashMessenger')
                    ->addMessage(array('success' => 'Saved'));
            return $this->_redirect();
        }
        $this->view->form = $form;
    }


}

