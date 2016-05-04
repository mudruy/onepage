<?php

class Ap_Validate_NoHttpsUrl extends Ap_Validate_Url {
    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(self::INVALID_URL => "'%value%' is not a valid URL. It must start with http:// and be valid.");

    /**
     * override
     * @param string $value 
     */
    public function isValid($value) {
        $res = parent::isValid($value);
        if (0 === strpos($value, 'https')) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        return $res;
    }


}