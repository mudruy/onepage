<?php

class Ap_Cache_Backend_Memcached extends Zend_Cache_Backend_Memcached
{
   
    public function offsetIncrement($value, $offset, $specificLifetime = false)
    {
        $value = (int)$value;
        $cnt = $this->load($offset);
        if($cnt === false) {
            if( $value == 0) {
                $value = 1;
            }
            $this->save($value, $offset, array(), $specificLifetime);
            return $value;
        } else {
            return $this->_memcache->increment($offset, $value);
        }
    }
    
    public function offsetDecrement($value, $offset)
    {
        $value = (int)$value;
        $cnt = $this->load($offset);
        if($cnt === false) {
            if( $value == 0) {
                $value = 1;
            }
            $this->save($value, $offset);
        } else {
            $this->_memcache->decrement($offset, $value);
        }
    }
    
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $lifetime = $this->getLifetime($specificLifetime);
        if ($this->_options['compression']) {
            $flag = MEMCACHE_COMPRESSED;
        } else {
            $flag = 0;
        }

        // ZF-8856: using set because add needs a second request if item already exists
        $result = @$this->_memcache->set($id, $data, $flag, $lifetime);

        if (count($tags) > 0) {
            $this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_MEMCACHED_BACKEND);
        }

        return $result;
    }
    
    public function test($id)
    {
        if ($tmp = $this->_memcache->get($id)) {
            return $tmp;
        }
        return false;
    }
    
    public function  load($id, $doNotTestCacheValidity = false)
    {
        $tmp = $this->_memcache->get($id);
        if ($tmp ==! false) {
            return $tmp;
        }
        return false;
    }
}