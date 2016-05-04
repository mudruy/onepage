<?php

class Ap_Db_Table_Row extends Zend_Db_Table_Row_Abstract
{
	protected $_cache;
    protected $_cache_name = 'memcached';
    
	public function getPrimaryKey($useDirty = true)
	{
    	return $this->_getPrimaryKey($useDirty);
    }
    
    public function getCache()
    {
        if(null === $this->_cache){
            $this->_cache = Zend_Registry::get('CacheManager')->getCache($this->_cache_name);
        }
        return $this->_cache;
    }
    
    public function saveAndLog($log_action = ''){
        $new_row = serialize($this);
        $this->refresh();
        if ($log_action)
            $this->createLog('from_' . $log_action);
        
        $new_row = unserialize($new_row);
        $new_row->setTable( $this->getTable() );
        if($log_action)
            $new_row->createLog('to_'.$log_action);
        
        return $new_row->save();
    }


    public function getCacheKey()
    {
        $data = $this->_getPrimaryKey();
        if(is_array($data)){
            $str = array();
            foreach($data AS $key=>$value){
                $str[]= $key.':'.$value;
            }
            $str = md5(implode(';',$str));
        }elseif(is_string($data)){
            $str = $data;
        }else throw new Exception('invalid type');
        return get_class($this).'_'.$str;
    }    
    
     function _refreshLite()
    {
        $db = $this->_getTable()->getAdapter();
        $primaryKey = $this->_getPrimaryKey(true);

        foreach ($primaryKey as $column => $value) {
            $columnName = $db->quoteIdentifier($column, true);
            $this->_data[$columnName] = $value;
        }
        
        $this->_cleanData = $this->_data;
        $this->_modifiedFields = array();
        
    }
    
    public function createLog($type)
    {
        $table = new User_Model_Log_Table;
        if(Zend_Registry::isRegistered('AuthUser') 
            && Zend_Registry::get('AuthUser')->role == User_Model_Row::ROLE_ADMIN){
            $logRow = $table->createRow(array(
                'user_id'   =>	Zend_Registry::get('AuthUser')->id,
                'type'      =>	$type,
            ));
            $logRow->setAtruments($this);
            return User_Model_Log_Manager::add($logRow);
        }
        return 0;
    }
}