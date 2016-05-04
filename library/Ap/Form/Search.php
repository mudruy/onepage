<?php

class Ap_Form_Search extends Ap_Form
{
    protected $_no_filter_elements = array(
    	'limit', 'sort', 'order', 'cols', 'group_by', 'q', 'page'
    );
    protected $_total;
    protected $_section = array();
    protected $_search_options = array(
    	'id'=>'Default:ID'
    );
    protected $_limit_options = array(
    	5     => '5', 
        20    => '20', 
        50    => '50', 
        100   => '100'
    );
    
    protected $_sort_options = array(
        'asc'=>'ASC', 
        'desc'=>'DESC'
    );
    
    protected $_order_options = array(
        'id'=>'ID'
    );
    
    protected $_defaults = array(
    	'limit'     =>  20,
        'order'     =>  'id',
        'sort'      =>  'asc',
        'group_by'  =>  'date',
        'page'      =>  1
    );
    
    protected $_group_options = array(
        'status'    =>  false,
        'values'    =>  array()
    );
    
    function initDefaultElement()
    {
        $this->addElement('text', 'q');
        $this->q->addFilters(array(
            'StripNewlines',
    		'StripTags',
    		'StringTrim'
        ));
        
        $this->addElement('select', 'cols');
        $this->cols
            ->addMultiOptions($this->_search_options);

        $this->addElement('text', 'page');
        
        $this->addElement('select', 'limit');
        $this->limit->addMultiOptions($this->_limit_options);
            
        $this->addElement('select', 'sort');
        $this->sort->addMultiOptions($this->_sort_options);
            
        $this->addElement('select', 'order');
        $this->order->addMultiOptions($this->_order_options);
        
        if($this->_group_options['status']){
            $this->addElement('select', 'group_by');
            $this->group_by->addMultiOptions($this->_group_options['values']);
        }
        $this->setDefaults($this->_defaults);
    }
    
    public function isValid($data)
    {
        $valid = true;
        foreach($data AS $name=>$value)
            if($element = $this->getElement($name))
                if(!$element->isValid($value, $data)){
                    if(isset($this->_defaults[$name]))
                        $element->setValue($this->_defaults[$name]);
                    $valid = false;
                }
        return $valid;
    }
    
    public function getRouterParams()
    {
    	$params = $this->getValues();
    	foreach($params AS $key=>$value)
    	    if($value == '' or $value === null)
    	        unset($params[$key]);
		return $params;
    }
    
    public function initSelect(Zend_Db_Select $select)
    {
        if($this->isSearch()){
            if($this->cols->getValue()=='_all_'){
                $sqls = array();
                foreach($this->_search_options AS $key=>$value){
                    if($key!='_all_'){
                        if (strpos($key, 'EXPR:') !== false )  {
                            $clean_key = str_replace('EXPR:', '', $key);
                            $sqls[] = $select->getAdapter()->quoteInto( $clean_key . ' LIKE ?', '%' . $this->q->getValue() . '%');
                        }   else {
                            $sqls[] = $select->getAdapter()->quoteInto('`' . $key . '` LIKE ?', '%' . $this->q->getValue() . '%');
                        }
                    }
                }
                $select->where('('.implode(' OR ', $sqls).')');
            }else{
                if (strpos($this->cols->getValue(), 'EXPR:') !== false )  {
                    $clean_key = str_replace('EXPR:', '', $this->cols->getValue());
                    $select->where( $clean_key.' LIKE ?', '%'.$this->q->getValue().'%');
                }   else {
                    $select->where('`' . $this->cols->getValue().'` LIKE ?', '%'.$this->q->getValue().'%');
                }

            }
        }

        foreach($this->getElements() AS $Element){
        	$name = $Element->getName();
        	$value = $Element->getValue();
            if(!in_array($name, $this->_no_filter_elements)){
                if($value!==null and $value!=''){
                	$operator = '=';
                    $col = $name;
	                foreach($this->_section AS $var=>$data){
			        	if(in_array($name, $data)){
			        		if($data[0]==$name){
			        			$operator = '>=';
			        		}elseif($data[1]==$name){
			        			$operator = '<=';
			        		}
			        		$col = $var;
			        		break;
			        	}
                    }
                    if(($name == 'date_to' or $name == 'date_from') 
                        AND preg_match('/^\d{4}\-\d{2}\-\d{2}$/',$value)
                    )
                        $value = $value.(($name=='date_from')?' 00:00:00':' 23:59:59');
                    $select->where($col.' '.$operator.' ?', $value);
                }
            }
        }
    }
    
    public function isSearch()
    {
        return ($this->q->getValue());
    }
    
    public function getOrderValue()
    {
        if($this->order->getValue() == 'group_by'){
            return 'group_index';
            
        }else
            return $this->order->getValue();
    }
            
    
    public function getGroupLabel()
    {
        return $this->_group_options['values'][$this->group_by->getValue()];
    }
    
    public function isFilter()
    {
        foreach($this->getElements() AS $Element)
            if(!in_array($Element->getName(), $this->_no_filter_elements))
                if($Element->getValue()!==null and $Element->getValue()!='')
                    return true;
         return false;
    }
    
    public function isSort()
    {
        return ($this->sort->getValue()!==null and $this->order->getValue()!==null);
    }
    
    public function isEnabled()
    {
        return ($this->isFilter() or $this->isSearch());
    }
    
    public function getLimitParams()
    {
        return $this->limit->getMultiOptions();
    }
    
    public function createPaginator(Zend_Db_Select $select, $group = true)
    {
        $this->initSelect($select);
        $select->order($this->getOrderValue().' '.$this->sort->getValue());
        if($group and isset($this->group_by))
        	$select->group($this->group_by->getValue());
        $cache = Zend_Registry::get('CacheManager')->getCache('paginator');
        $cache_id = 'data_query_'.md5($select);
        if(!$items = $cache->load($cache_id) or $this->page->getValue() == 1){
            $items = $select->getTable()->fetchAll($select);
            $cache->save($items, $cache_id);
            Zend_Registry::get('Log')->info('create cache '.$cache_id);
        }
        $cache_id = $cache_id.'_total';
        if(!$this->_total = $cache->load($cache_id) or $this->page->getValue() == 1){
            $this->_total = array();
            foreach($items AS $item){
                foreach($item AS $key=>$value){
                    if(is_numeric($value)){
                        if(!isset($this->_total[$key])){
                            $this->_total[$key] = 0;
                        }
                        $this->_total[$key]+=$value;
                    }
                }
            }
            $cache->save($this->_total, $cache_id);
            Zend_Registry::get('Log')->info('create cache '.$cache_id);
        }
        $arrayItem = array();
        foreach($items AS $item){
            $arrayItem[]=$item;
        }
        $adapter = new Zend_Paginator_Adapter_Array($arrayItem);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCacheEnabled(false)
            ->setItemCountPerPage($this->limit->getValue())
            ->setPageRange(3)
            ->setCurrentPageNumber($this->page->getValue());
        return $paginator;
    }
    
    public function createTotal(Zend_Db_Select $select)
    {
        $this->initSelect($select);
        $cache = Zend_Registry::get('CacheManager')->getCache('paginator');
        $cache_id = 'total_query_'.md5($select);
        if(!$items = $cache->load($cache_id) or $this->page->getValue() == 1){
            $items = $select->getTable()->getTotalRow($select);
            $cache->save($items, $cache_id);
            Zend_Registry::get('Log')->info('create cache '.$cache_id);
        }
        return $items;
    }
    
    public function getTotal($key = false)
    {
        if(!$key)
            return $this->_total;
        else{
            if(isset($this->_total[$key])){
                return $this->_total[$key];
            }
            return 0;
        }
    }
    
}
