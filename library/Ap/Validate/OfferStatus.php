<?php 

class Ap_Validate_OfferStatus extends Zend_Validate_Abstract
{
    const NOT_WORKER = 'notWorker';
    const NOT_CONTROLLER= 'notController';
    const NOT_WORKER_AND_CONTROLLER = 'notWorkerAndController';
    
	protected $_messageTemplates = array(
		self::NOT_WORKER => 'Not select worker',
		self::NOT_CONTROLLER => 'Not select controller',
		self::NOT_WORKER_AND_CONTROLLER => 'Not select worker and controller',
	);
	protected $_configs = array(
	    'new'        => array(),
        'disabled'   => array(),
        'progress'   => array('worker_id'),
        'controller' => array('worker_id'),
        'verifying'  => array('worker_id', 'controller_id'),
        'invalid'    => array('worker_id', 'controller_id'),
        'approved'   => array('worker_id', 'controller_id'),
	    'paid'       => array('worker_id', 'controller_id'),
	);
	public function isValid($value, $context = null)
	{
        $value = (string) $value;
        $this->_setValue($value);
        
        $valid = true;
        foreach($this->_configs AS $config=>$options)
            if($config == $value)
                foreach($options AS $option)
                    if(!$context[$option]){
                        if($option=='worker_id')
                            $this->_error(self::NOT_WORKER);
                        if($option=='controller_id')
                            $this->_error(self::NOT_CONTROLLER);
                        $valid = false;
                    }
        return $valid;
    }
	
}