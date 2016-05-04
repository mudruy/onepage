<?php

class Ap_Validate_Json extends Zend_Validate_Abstract {

    const NOT_JSON = 'errorJson';
    protected $_messageTemplates = array(
		self::NOT_JSON => 'Json no valid'
	);
    
    public function isValid($value) {
        $value = (string) $value;
        $value = trim($value);
        $this->_setValue($value);
        
        $res = json_decode($value);

        if (!is_null($res)) {
            return true;
        }
        $this->_error(self::NOT_JSON);
        return false;
    }

}
