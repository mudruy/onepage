<?php

class Ap_Db_Core {
    static protected $tables       = array();
    //for test Singleton
    static public $is_object_exist = false;
    static protected $curr_table_name;
    CONST TABLE_PREQUOTE          = 'Application_Model_';

    /**
     * return instanse of table
     * @param string $table_name
     * @return svTable
     */
    public static function getTable( $table_name ) {
        self::$curr_table_name = self::TABLE_PREQUOTE . $table_name;
        if (array_key_exists( $table_name, self::$tables ) ) {
            if (is_object( self::$tables[$table_name]) && self::$tables[$table_name] instanceof self::$curr_table_name ) {
                self::$is_object_exist = true;
                return self::$tables[$table_name];
            } else {
                return self::createTable( $table_name );
            }
        } else {
            return self::createTable( $table_name );
        }

    }

    /**
     * create table
     * @param string $table_name
     * @return svTable
     */
    protected static function createTable( $table_name ) {
        try {
            $class_name = self::TABLE_PREQUOTE .$table_name;
            return self::$tables[$table_name] = new $class_name ();
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }

}