<?php

/**
 *
 * @author pavel
 */
class Ap_Application_PayTime 
{

    /**
     * return timestamp payment day
     * @return int timestamp 
     */
    public static function getTime(){
        
        //$date = new Zend_Date;
        //net 30
//        $date_from = strtotime(' - 1 month', mktime(0, 0, 0, $date->get('MM'), 1, $date->get('y')));
        
        //net 15
//        if($date->get('d')>15)
//            $date_from = mktime(0, 0, 0, $date->get('MM'), 1, $date->get('y'));
//        else
//            $date_from = strtotime(' - 1 month', mktime(0, 0, 0, $date->get('MM'), 16, $date->get('y')));


        //net 14
        $start_date   = Zend_Registry::get('Config')->net14->start_day;
        $current_date = time();
        $sec          = $current_date - strtotime($start_date);

        $weeks = floor($sec/(86400*14));

        $pay_date = strtotime($start_date) + $weeks*86400*14 - 86400*14;
        return $pay_date;
    }
    
    public static function getFutureEndTime() {
        //net 14
        return self::getTime() + 86400*14;
    }

}