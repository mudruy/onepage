<?php 

class Ap_Validate_Guid extends Zend_Validate_Abstract
{
	public function isValid($value)
    {
        $value = (string) $value;
        $this->_setValue($value);

        if (preg_match('/^[\w\d]{32}$/', $value)) {
            return true;
        }
        return false;
    }
}