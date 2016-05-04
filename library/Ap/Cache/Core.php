<?php

class Ap_Cache_Core extends Zend_Cache_Core
{
    
    
    public function save($data, $id = null, $tags = array(), $specificLifetime = false, $priority = 8)
    {
        if (!$this->_options['caching']) {
            return true;
        }
        if ($id === null) {
            $id = $this->_lastId;
        } else {
            $id = $this->_id($id);
        }
        self::_validateIdOrTag($id);
        self::_validateTagsArray($tags);

        // automatic cleaning
        if ($this->_options['automatic_cleaning_factor'] > 0) {
            $rand = rand(1, $this->_options['automatic_cleaning_factor']);
            if ($rand==1) {
                //  new way                 || deprecated way
                if ($this->_extendedBackend || method_exists($this->_backend, 'isAutomaticCleaningAvailable')) {
                    $this->_log("Zend_Cache_Core::save(): automatic cleaning running", 7);
                    $this->clean(Zend_Cache::CLEANING_MODE_OLD);
                } else {
                    $this->_log("Zend_Cache_Core::save(): automatic cleaning is not available/necessary with current backend", 4);
                }
            }
        }

        $this->_log("Zend_Cache_Core: save item '{$id}'", 7);
        if ($this->_options['ignore_user_abort']) {
            $abort = ignore_user_abort(true);
        }
        if (($this->_extendedBackend) && ($this->_backendCapabilities['priority'])) {
            $result = $this->_backend->save($data, $id, $tags, $specificLifetime, $priority);
        } else {
            $result = $this->_backend->save($data, $id, $tags, $specificLifetime);
        }
        if ($this->_options['ignore_user_abort']) {
            ignore_user_abort($abort);
        }

        if (!$result) {
            // maybe the cache is corrupted, so we remove it !
            $this->_log("Zend_Cache_Core::save(): failed to save item '{$id}' -> removing it", 4);
            $this->_backend->remove($id);
            return false;
        }

        if ($this->_options['write_control']) {
            $data2 = $this->_backend->load($id, true);
            if ($data!=$data2) {
                $this->_log("Zend_Cache_Core::save(): write control of item '{$id}' failed -> removing it", 4);
                $this->_backend->remove($id);
                return false;
            }
        }

        return true;
    }


}
