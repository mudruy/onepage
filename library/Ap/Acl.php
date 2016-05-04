<?php

class Ap_Acl extends Zend_Acl
{
    public function __construct(Zend_Config $config)
    {
       $roles = $config->acl->roles;
       $resources = $config->acl->resources;
       $this->_addRoles($roles);
       $this->_addResources($resources);
    }
    
    protected function _addRoles($roles){
        foreach($roles as $name => $parents){
            if(!$this->hasRole($name)) {
                if(empty($parents)){
                    $parents = array();
                } else {
                    $parents = explode(',', $parents);
                }
                $this->addRole(new Zend_Acl_Role($name), $parents);
            }
        }
    }
    
    protected function _addResources($resources){
        foreach($resources as $permissions => $controllers){
            foreach($controllers as $controller => $actions){
                if('_all' == $controller){
                    $controller = null;
                } else {
                    if(!$this->has($controller)){
                        $this->add(new Zend_Acl_Resource($controller));
                    }
                }
                foreach($actions as $action => $row){
                    $roles = explode(',', $row);
                    if($action == '_all') {
                        $action = null;
                    }
                    if($permissions == 'allow'){
                        $this->allow($roles, $controller, $action);
                    }
                    if($permissions == 'deny'){
                        $this->deny($roles, $controller, $action);
                    }
                }
            }
        }
    }
}
