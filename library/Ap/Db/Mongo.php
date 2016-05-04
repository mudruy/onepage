<?php

class Ap_Db_Mongo {
    
    static protected $connections  = array();
    static public $is_object_exist = false;

    /**
     * return instanse of table
     * @param string $table_name
     * @return svTable
     */
    public static function getInstance( $target ) {
        if (array_key_exists( $target, self::$connections ) ) {
            if (is_object( self::$connections[$target]) && self::$connections[$target] instanceof MongoClient ) {
                self::$is_object_exist = true;
                return self::$connections[$target];
            } else {
                return self::createConnection( $target );
            }
        } else {
            return self::createConnection( $target );
        }

    }

    /**
     * create mongo connection
     * @param string $target name
     * @return MongoClient
     */
    protected static function createConnection( $target ) {
        try {
            return self::$connections[$target] = new MongoClient(Zend_Registry::get('Config')->mongo->$target);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }

}