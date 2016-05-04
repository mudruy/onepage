<?php

class Application_Model_UserRow extends Zend_Db_Table_Row_Abstract
{
    protected $_tableClass='Application_Model_User';

    CONST SALT = '321';

    public function setPassword($password) 
    {
        $this->password = $this->_createPasswordHash($password);
        return $this;
    }
    
    protected function _createPasswordHash($password)
    {
        return md5(self::SALT.$password);
    }
    
}

