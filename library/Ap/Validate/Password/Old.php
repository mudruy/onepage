<?php 

class Ap_Validate_Password_Old extends Zend_Validate_Abstract{
	const NOT_MATCH = 'notMatch';
	protected $_messageTemplates = array(
		self::NOT_MATCH => 'Новый пароль не совпадает со старым'
	);
	public function isValid($value, $oldpassword = null)
    {	$value = (string) $value;
        $this->_setValue($value);

        if($value == $oldpassword) {
            return true;
        }

        $this->_error(self::NOT_MATCH);
        return false;
    }
	
}