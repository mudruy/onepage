<?php
class Ap_Application_Lock {

    function lock($lock_fname) {
        if(file_exists($lock_fname)){
            $pid = file_get_contents($lock_fname);
            if (file_exists( "/proc/$pid" )){
                return false;
            } else {
                file_put_contents($lock_fname, getmypid());
                return true;
            }
        } else {
            file_put_contents($lock_fname, getmypid());
            return true;
        }
    }


}