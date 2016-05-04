<?php 

class Ap_Validate_DatePeriodMonth extends Zend_Validate_Abstract{
	const NOT_MATCH = 'notMatch';
	protected $_messageTemplates = array(
		self::NOT_MATCH => 'Password confirmation does not match'
	);
    protected $_period = 0;
    
    public function __construct($period = '-3 month', $inputDateStart = 'date_from') 
    {
        $this->_dateStart = $inputDateStart;
        $this->_period = $period;
    }
	public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);
        if (is_array($context)) {
            if (isset($context[$this->_dateStart])){
                $date_from = new Zend_Date($context[$this->_dateStart], 'dd-MM-y');
                $date_to = new Zend_Date($value, 'dd-MM-y');
                $limitTime = strtotime($this->_period, $date_to->getTimestamp());
                if($limitTime<=$date_from->getTimestamp() 
                    and $date_from->getTimestamp() <=$date_to->getTimestamp()
                ) {
                    return true;
                }
                
            }
        }

        $this->_error(self::NOT_MATCH);
        return false;
    }
}