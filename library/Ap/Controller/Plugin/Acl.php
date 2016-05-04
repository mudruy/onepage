<?php

class Ap_Controller_Plugin_Acl 
    extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        $session = new Zend_Session_Namespace('LoginAs');
        $table = new User_Model_Table();
        if(isset($session->admin_id) 
            and $AdminRow = $table->getRowById($session->admin_id)
        )
            Zend_Registry::set('AdminRow', $AdminRow);
        
    	if(!$auth->hasIdentity() 
            or !$User = $table->getRowById((int)Zend_Auth::getInstance()->getIdentity())
        ) {
            if(!isset($_COOKIE['sign_key'])
                or !$User = $table->getRowBySignKey($sign_key = $_COOKIE['sign_key'])
                or substr($sign_key, 32)!=md5($_SERVER['REMOTE_ADDR'].SALT)
            ){
                $User = $table->createRow();
                $User->id = 0;
                $User->role = 'guest';
            }
        }
    	$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/acl.ini', APPLICATION_ENV);
        $Acl = new Ap_Acl($config);
        Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($Acl);
        Zend_Registry::set('Acl', $Acl);
        Zend_Registry::set('AuthUser', $User);
        
       	$controller = ($request->getControllerName())?$request->getControllerName():'index';
        $action = $request->getActionName();
        $resource = strtolower($controller);
        $privellege = $action;
        if(!$Acl->has($resource)){
            return $request->setControllerName('error')
                ->setActionName('notfound')
                ->setDispatched(true);
        }
        if(!$Acl->isAllowed((($User->id)?$User->role:'guest'), $resource, $privellege)) {
            if($User->role!='guest'){
                echo 'Access denied';
                exit;
            }else return $request->setControllerName('index')
                ->setActionName('index')
                ->setDispatched(false);
        }
        
        if($User->role==User_Model_Row::ROLE_ADMIN || $User->role==User_Model_Row::ROLE_BDM){
            
            $config = Zend_Registry::get('Config');
            $parts = parse_url($config->resources->frontController->baseUrl);
            $url = 'https://' .  $parts['host'];
            if(isset($parts['port'])) {
                $url .= ':' . $parts['port'];
            }
            $url .= $parts['path'];
            
            if ($config->test->ssl && empty($_SERVER['HTTPS'])) {
                if(APPLICATION_ENV == 'production'){
                    $r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                    $r->gotoUrl($url)->redirectAndExit();
                }
            }
        }
    }
}
