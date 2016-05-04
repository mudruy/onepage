<?php

class Ap_Db_Table extends Zend_Db_Table_Abstract{

    const CASCADE_RECURSE  = 'cascadeRecurse';
    const ROWS_AMOUNT  = 0;
    const MAX_ID  = 0;
    const MIN_ID  = 1;
    
    protected static $_cache;
    protected static $_cache_name = 'memcached';
    protected $_cache_backend;
    
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->_cache_backend = Zend_Registry::get('CacheManager')->getCache(self::$_cache_name)->getBackend();
    }

    public function fetchAllAssoc(Zend_Db_Table_Select $select){
        $stmt = $this->getAdapter()->query($select);
        $stmt->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    public function getRowById($id)
    {
        $id = $this->quoteAp($id, 'INTEGER');
        if(is_array($this->_primary)) {
            $sql = $this->_primary[1] . ' = ?';
        } else
            $sql = $this->_primary . ' = ?';
        $select = $this->select()
                ->where($sql, $id);
        return $this->fetchRow($select);
    }

    public function getArrayRowById($id)
    {
        $res = $this->getRowById($id);
        if(is_null($res)) {
            return array();
        }
        return $res->toArray();
    }

    public function getArrayRowsByIds($ids)
    {
        foreach ($ids as $key => $value) {
            $ids[$key] = $this->quoteAp($value, 'INTEGER');
        }
        return $this->find($ids)->toArray();
    }

    function escapeSphinxQL($string)
    {
        $from = array ( '\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=', "'", "\x00", "\n", "\r", "\x1a" );
        $to   = array ( '\\\\', '\\\(','\\\)','\\\|','\\\-','\\\!','\\\@','\\\~','\\\"', '\\\&', '\\\/', '\\\^', '\\\$', '\\\=', "\\'", "\\x00", "\\n", "\\r", "\\x1a" );
        return str_replace ( $from, $to, $string );
    }

    public function quoteAp($val, $type = false)
    {
        if($type) {
            return $this->getAdapter()->quoteInto('?', $val, $type);
        }
        return $this->getAdapter()->quoteInto('?', $val);
    }

    public function _cascadeDelete($parentTableClassname, array $primaryKey)
 	{
        // setup metadata
    	$this->_setupMetadata();

        // get this class name
        $thisClass = get_class($this);
        if ($thisClass === 'Zend_Db_Table') {
            $thisClass = $this->_definitionConfigName;
        }

        $rowsAffected = 0;

        foreach ($this->_getReferenceMapNormalized() as $map) {
        	if ($map[self::REF_TABLE_CLASS] == $parentTableClassname && isset($map[self::ON_DELETE])) {

            	$where = array();

                // CASCADE or CASECADE_RECURSE
                if (in_array($map[self::ON_DELETE], array(self::CASCADE, self::CASCADE_RECURSE))) {
                    for ($i = 0; $i < count($map[self::COLUMNS]); ++$i) {
                        $col = $this->_db->foldCase($map[self::COLUMNS][$i]);
                        $refCol = $this->_db->foldCase($map[self::REF_COLUMNS][$i]);
                        $type = $this->_metadata[$col]['DATA_TYPE'];
                        $where[] = $this->_db->quoteInto(
                            $this->_db->quoteIdentifier($col, true) . ' = ?',
                            $primaryKey[$refCol], $type);
                    }
                }

                // CASECADE_RECURSE
                if ($map[self::ON_DELETE] == self::CASCADE_RECURSE) {

                    /**
                     * Execute cascading deletes against dependent tables
                     */
                    $depTables = $this->getDependentTables();
                    if (!empty($depTables)) {
                        foreach ($depTables as $tableClass) {
                            $t = self::getTableFromString($tableClass, $this);
                            foreach ($this->fetchAll($where) as $depRow) {
                                $rowsAffected += $t->_cascadeDelete($thisClass, $depRow->getPrimaryKey());
                            }
                        }
                    }
                }
                // CASCADE or CASECADE_RECURSE
                if (in_array($map[self::ON_DELETE], array(self::CASCADE, self::CASCADE_RECURSE))) {
                    $rowsAffected += $this->delete($where);
            	}
        	}
        }
    	return $rowsAffected;
    }

	public static function getTableFromString($tableName, Zend_Db_Table_Abstract $referenceTable = null)
    {
        if ($referenceTable instanceof Zend_Db_Table_Abstract) {
            $tableDefinition = $referenceTable->getDefinition();

            if ($tableDefinition !== null && $tableDefinition->hasTableConfig($tableName)) {
                return new Zend_Db_Table($tableName, $tableDefinition);
            }
        }
        // assume the tableName is the class name
        if (!class_exists($tableName)) {
            try {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($tableName);
            } catch (Zend_Exception $e) {
                require_once 'Zend/Db/Table/Row/Exception.php';
                throw new Zend_Db_Table_Row_Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        $options = array();

        if ($referenceTable instanceof Zend_Db_Table_Abstract) {
            $options['db'] = $referenceTable->getAdapter();
        }

        if (isset($tableDefinition) && $tableDefinition !== null) {
            $options[Zend_Db_Table_Abstract::DEFINITION] = $tableDefinition;
        }

        return new $tableName($options);
    }

    public function getRand($amount=1, $colums=array())
    {
        $total = $this->getMaxID();
        $generateAmount = min($amount * 3, $total);
        $ids = array();
        while(count($ids) < $generateAmount) {
            $i = rand(1, $total);
            $ids[$i] = $i;
        }
        $colStr = empty($colums)? '*': implode(', ', $colums);
        $queryStr = "SELECT ".$colStr." FROM ".$this->_name." WHERE ".$this->_primary." IN(".implode(", ", $ids).") LIMIT ".$amount;
        return $this->getAdapter()->query($queryStr)->fetchAll();
    }

    public function getRandII($point = null)
    {
        if(!is_array($this->_primary)) {
            $primary = $this->_primary;
        } else {
            $primary = $this->_primary[1];
        }
        if(is_null($point)) {
            $total = $this->getMaxID();
            $point = rand($this::MIN_ID, $total);
        }
        $selectmax = $this->select();
        $selectmax->where("`$primary` >= ?", $point)
                  ->limit(1);
        $row = $this->fetchRow($selectmax);
        if(is_null($row)){
            $selectmin = $this->select();
            $selectmin->where("`$primary` <= ?", $point)
                      ->where("`$primary` >= ?", $this::MIN_ID)
                      ->limit(1);
            $row = $this->fetchRow($selectmin);
            if(is_null($row))
                return array();
        }
        return $row->toArray();
    }

    public function getCount()
    {
        $total = $this::ROWS_AMOUNT;
        if ($total == 0) {
            $cache = Am_Cache::Get('core');
            $cacheid = sprintf("amount_%s",$this->_name);
            if (!($total = $cache->load($cacheid))){
                $total = $this->getAdapter()->fetchOne('SELECT COUNT('.$this->_primary.') AS count FROM '.$this->_name );
                $cache->save($total, $cacheid);
            }
        }
        return $total;
    }

    public function getMaxID()
    {
        $maxID = $this::MAX_ID;
        if ($maxID == 0) {
            $cache = Am_Cache::Get('core');
            $cacheid = sprintf("max_id_%s", $this->_name);
            if (!($maxID = $cache->load($cacheid))){
                $maxID = $this->getAdapter()->fetchOne('SELECT MAX('.$this->_primary.') AS count FROM '.$this->_name );
                $cache->save($maxID, $cacheid);
            }
        }
        return $maxID;
    }
}