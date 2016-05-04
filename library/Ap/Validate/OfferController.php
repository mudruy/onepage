<?php 

class Ap_Validate_OfferController extends Zend_Validate_Abstract
{
    const NOT_SELECTED = 'notSelected';
    const SELECTED= 'selected';
    
	protected $_messageTemplates = array(
		self::NOT_SELECTED => 'Default::Not select',
		self::SELECTED => 'Default::Should not be selected',
	);
	protected $_configs = array(
	    'new'        => false,
        'disabled'   => false,
        'progress'   => false,
        'controller' => false,
        'verifying'  => true,
        'invalid'    => true,
        'approved'   => true,
	    'paid'       => true,
	);
	public function isValid($value, $context = null)
	{
        $value = (string) $value;
        $this->_setValue($value);
        
        $valid = true;
        foreach($this->_configs AS $config=>$status)
            if($config == $context['status']){
                if($status and !$value){
                    $valid = false;
                    $this->_error(self::NOT_SELECTED);
                }
                if(!$status and $value){
                    $valid = false;
                    $this->_error(self::SELECTED);
                }
            }
                
        return $valid;
    }
	
}