<?php 

class Ap_Validate_PartnerSign extends Zend_Validate_Abstract
{
    const INVALID = 'invalid';
	protected $_messageTemplates = array(
		self::INVALID => 'Pid invalid'
	);
    protected $_user_id;
    
	public function isValid($value)
    {
        $filter = new Ap_Filter_PartnerSign_TokenToValue;
        $user_id = $filter->filter($value);
        $userIdValidator = new Zend_Validate_Db_RecordExists('user', 'id');
	 	if($userIdValidator->isValid($user_id)){
            $this->_user_id = $user_id;
            return true;
        }
        $this->_error(self::INVALID);
        return false;
    }
    
    public function getUserId()
    {
        return $this->_user_id;
    }
}