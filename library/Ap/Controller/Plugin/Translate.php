<?php

class Ap_Controller_Plugin_Translate extends Zend_Controller_Plugin_Abstract {
	
    public function preDispatch(Zend_Controller_Request_Abstract $request){
        if(!$lang = $request->getParam('lang', false)){
            try{
                $locale = new Zend_Locale(Zend_Locale::BROWSER);
                $lang = $locale->getLanguage();
            }  catch (Zend_Locale_Exception $e){
            }
        }
        if(in_array($lang, Zend_Registry::get('Zend_Translate')->getOptions('validLangs')))
            Zend_Registry::get('Zend_Translate')->setLocale($lang);
        $Router = Zend_Controller_Front::getInstance()->getRouter();
        $Router->setGlobalParam('lang', $lang);
    }
}
