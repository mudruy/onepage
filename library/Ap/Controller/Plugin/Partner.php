<?php

class Ap_Controller_Plugin_Partner extends Zend_Controller_Plugin_Abstract 
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if(isset($_GET['pid']) and $token = $_GET['pid']){
            User_Form_Auth_Signup::setPartnerSign($token);
            return Zend_Controller_Front::getInstance()->getResponse()->setRedirect('/signup'); 
        }
    }
}
