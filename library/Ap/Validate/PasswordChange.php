<?php 

class Ap_Validate_PasswordChange extends Zend_Validate_Abstract{
	const NOT_MATCH = 'notMatch';
	protected $_messageTemplates = array(
		self::NOT_MATCH => 'Active password wrong'
	);
    protected $_user_row;


    public function __construct($row) {
        $this->_user_row = $row;
    }
    public function isValid($value, $context = null){
        $value = (string) $value;
        $this->_setValue($value);
        
        if($this->_user_row->isPassword($context['password_active'])) {
            return true;
        }

        $this->_error(self::NOT_MATCH);
        return false;
    }
    
}