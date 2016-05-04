<?php

abstract class Ap_Filter_GridInput extends Zend_Filter_Input
{
    protected $_enabled;
    protected $_order_params = array(
        'id'
    );
    protected $_limit_params = array(5, 20, 50, 100);
    protected $_limit_default = 20;
    
    protected $_filter_cols = array();
    
    protected $_search_cols = array(
        'id'
    );
    protected $_form;
    
    public function __construct($params, $form = null)
    {
        if($form){
            $this->setForm($form);
        }
    	$filterRules = array(
    		'q'=>array(
    			'StripNewlines',
    			'StripTags',
    			'StringTrim'
    		)
    	);
    	$validatorRules = array(
    	    'sort' => array(
		    	new Zend_Validate_InArray(array('asc','desc')),
		        'default'=>'desc'
		    ),
		    'order' => array(
		    	new Zend_Validate_InArray($this->getOrderParams()),
		        'default'=>'id'
		    ),
		    'limit' => array(
		       	'default'	=>	$this->_limit_default,
				new Zend_Validate_InArray($this->_limit_params)
		    ),
		    'page' =>array(
		        'default' => 1
		    ),
		    'q'=>array(
		    	new Zend_Validate_StringLength(array('max'=>255, 'min'=>1))
		    ),
		    'cols'=>array(
		    	new Zend_Validate_InArray($this->_search_cols)
		    )
		);
		foreach($this->_filter_cols AS $key=>$row){
		    if(is_array($row))
    		    $validatorRules[$key] = array(
    		        new Zend_Validate_InArray($row)
    		    );
		}
		parent::__construct($filterRules, $validatorRules, $params);
    }
    
    public function getRouterParams()
    {
    	$params = array();
    	if($this->isValid('order') and $this->isValid('sort')) {
			$params['order'] = $this->order;
			$params['sort'] = $this->sort;
		}	
		if($this->isValid('limit')){
			$params['limit'] = $this->limit;
		}
    	if(($this->isValid('q') or $this->isValid('cols'))) {
			$params['cols'] = $this->cols;
	    	$params['q'] = $this->q;
		}
		foreach($this->_filter_cols AS $key=>$row)
            if($this->isValid($key))
        		$params[$key] = $this->{$key};
        if($this->page)
		    $params['page'] = $this->page;
		ksort($params);
		return $params;
    }
    
    public function getOrderParams()
    {
        $data = array();
        foreach($this->_order_params AS $key=>$value) {
            if(is_array($value)) {
                $data[]=$key;
            } else {
                $data[]=$value;
            }
        }
        return $data;
    }
    
    public function getForm()
    {
        return $this->_form;
    }
    
    public function setForm($form)
    {
        if(!$form instanceof Zend_Form)
            throw new Exception('Invalid form provided to setForm; must be Zend_Form');
        $this->_form = $form;
        foreach($this->_filter_cols AS $key=>$row){
            if($element = $this->_form->getElement($key)){
                if($element instanceof Zend_Form_Element_Multi){
                    $options = $element->getMultiOptions();
                    if(isset($options[0]))
                        unset($options[0]);
                    $this->_filter_cols[$key] = array_keys($options);
                }
            }
        }
        return $this;
    }
    
    public function isForm()
    {
        return (bool) $this->_form;
    }
    
    public function getLimitParams()
    {
        return $this->_limit_params;
    }
    
    public function initSelect(&$select)
    {
        if($this->isValid('q') and $this->isValid('cols')){
            $this->_enabled = true;
    		$select->where($this->cols.' LIKE ?', '%'.$this->q.'%');
    		if($this->isForm())
        		$this->getForm()->setDefaults(array(
        			'q' => $this->q,
        			'cols' => $this->cols
        		));
        }
        foreach($this->_filter_cols AS $key=>$row)
            if($this->isValid($key)){
                $this->_enabled = true;
        		$select->where($key.' = ?', $this->{$key});
        		if($this->isForm())
                   $this->getForm()->setDefault($key, $this->{$key});
            }
        if($this->isValid('order') and $this->isValid('sort')) {
            foreach($this->_order_params AS $key=>$value) {
                if(is_array($value) and $key==$this->order) {
                    foreach($value AS $row)
                        $select->order($row.' '.$this->sort);
                } elseif($value==$this->order)
                    $select->order($value.' '.$this->sort);
            }
        }
    }
    
    public function isEnabled()
    {
        return $this->_enabled;
    }
}