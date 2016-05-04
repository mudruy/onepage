<?php

class Application_Model_User extends Zend_Db_Table_Abstract
{

    protected $_name = 'user';
    protected $_primary = 'id';
    protected $_rowClass = 'Application_Model_UserRow';
    
    
    /**
     * add new user in database
     * @param Zend_Form $object
     * @return Zend_Db_Table_Row_Abstract
     */
    public function add($object) {
        $this->_object = $this->createRow();
        $this->_object->name = $object->name;
        $this->_object->login = $object->login;
        $this->_object->password = $object->password;
        $this->_object->save();
        return $this->_object;
    }
}

