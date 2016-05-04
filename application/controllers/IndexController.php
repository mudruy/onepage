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
        $form = new Application_Form_UserAdd();
        if ($this->getRequest()->isPost() and $form->isValid($_POST)) {
            Ap_Db_Core::getTable('User')->add($form->getObject());
            $this->_helper->getHelper('FlashMessenger')
                    ->addMessage(array('success' => 'Saved'));
            return $this->_redirect();
        }
        $this->view->form = $form;
    }
    
    public function editAction()
    {
        $table = Ap_Db_Core::getTable('User');
        if (!$id = $this->getRequest()->getParam('id', false) or 
                !$userRow = $table->fetchRow($table->select()->where('id = ?', $id))) {
            $this->_helper->getHelper('FlashMessenger')
                    ->addMessage(array('danger' => 'Object not found'));
            return $this->_redirect();
        }
        $form = new Application_Form_UserEdit($userRow);
        if ($this->getRequest()->isPost() and $form->isValid($_POST)) {
            $form->getObject()->save();
            $this->_helper->getHelper('FlashMessenger')
                    ->addMessage(array('success' => 'Saved'));
            return $this->_redirect();
        }
        $this->view->form = $form;
    }


}

