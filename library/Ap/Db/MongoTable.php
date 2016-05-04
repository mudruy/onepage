<?php

class Ap_Db_MongoTable {
    
    protected $_db_name         = 'dbname';
    protected $_collection_name = 'collection';
    protected $_connection, $_db;


    public function __construct($target = 'masterconnection') {
        $this->_connection = Ap_Db_Mongo::getInstance( $target );
    }
    
    public function setDb($db_name){
        $this->_db_name = $db_name;
        return $this;
    }

    public function getCollection() {
        $this->setDb($this->_db_name);
        $db = $this->_db_name;
        $this->_db = $this->_connection->$db;
        $collection = $this->_collection_name;
        
        // select a collection (analogous to a relational database's table)
        return $this->_db->$collection;
    }

}