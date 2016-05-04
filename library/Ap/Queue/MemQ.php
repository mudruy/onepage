<?php

//define('MEMQ_POOL', 'localhost:11211');
//define('MEMQ_TTL', 0);

class Ap_Queue_MemQ {

    private static $mem = NULL;

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    private static function getInstance() {
        if (!self::$mem)
            self::init();
        return self::$mem;
    }

    private static function init() {
        $mem = new Memcached;
        $servers = explode(",", MEMQ_POOL);
        foreach ($servers as $server) {
            list($host, $port) = explode(":", $server);
            $mem->addServer($host, $port);
        }
        self::$mem = $mem;
    }

    public static function is_empty($queue) {
        $mem = self::getInstance();
        $head = $mem->get($queue . "_head");
        $tail = $mem->get($queue . "_tail");
        if ($head >= $tail+1 || $head === FALSE || $tail === FALSE)
            return TRUE;
        else
            return FALSE;
    }
    
    public static function count($queue) {
        $mem = self::getInstance();
        $head = $mem->get($queue . "_head");
        $tail = $mem->get($queue . "_tail");
        if ( $head === FALSE || $tail === FALSE)
            return 0;
        $res = (int)$tail-(int)$head;
        $res++;
        return $res;
    } 

    public static function dequeue($queue, $after_id = FALSE, $till_id = FALSE) {
        $mem = self::getInstance();
        if ($after_id === FALSE && $till_id === FALSE) {
            $tail = $mem->get($queue . "_tail");
            if (($id = $mem->increment($queue . "_head")) === FALSE)
                return FALSE;
            if ($id <= $tail+1) {
                return $mem->get($queue . "_" . ($id - 1));
            } else {
                $mem->decrement($queue . "_head");
                return FALSE;
            }
        } else if ($after_id !== FALSE && $till_id === FALSE) {
            $till_id = $mem->get($queue . "_tail");
        }
        $item_keys = array();
        for ($i = $after_id + 1; $i <= $till_id; $i++)
            $item_keys[] = $queue . "_" . $i;
        $null = NULL;
        return $mem->getMulti($item_keys, $null, Memcached::GET_PRESERVE_ORDER);
    }
    
    public static function enqueue($queue, $item) {
        $mem = self::getInstance();
        $id = $mem->increment($queue . "_tail");
        if ($id === FALSE) {
            if ($mem->add($queue . "_tail", 1, MEMQ_TTL) === FALSE) {
                $id = $mem->increment($queue . "_tail");
                if ($id === FALSE)
                    return FALSE;
            }
            else {
                $id = 1;
                $mem->add($queue . "_head", $id, MEMQ_TTL);
            }
        }
        if ($mem->add($queue . "_" . $id, $item, MEMQ_TTL) === FALSE)
            return FALSE;
        return $id;
    }
    
    public static function dequeuetime($queue, $sec) {
        $mem = self::getInstance();
        $queue = $queue . "_time";
        $tail = $mem->get($queue . "_tail");
        if (($id = $mem->increment($queue . "_head")) === FALSE)
            return FALSE;
        if ($id <= $tail+1) {
            $res = $mem->get($queue . "_" . ($id - 1));
            $pos = strpos($res, ' ');
            $time = substr($res, 0, $pos);
            $job = substr($res, $pos+1);
            
            if($time+$sec < time()) {
                return $job;
            } else {
                $mem->decrement($queue . "_head");
                return FALSE;
            }
        } else {
            $mem->decrement($queue . "_head");
            return FALSE;
        }
    }
    
    public static function enqueuetime($queue, $item){
        $item = time() . ' ' . $item;
        $queue = $queue . "_time";
        return self::enqueue($queue, $item);
    }
    
    public static function counttime($queue) {
        $queue = $queue . "_time";
        return self::count($queue);
    }

}
