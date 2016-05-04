<?php
class Ap_Filter_UserIdAndLayoutId 
{
    const LENGTH = -2;
    
    public static function filter($str, $index = false)
    {
        $values = array(
            'layout_id' =>  intval(substr($str, self::LENGTH)),
            'user_id'   =>  intval(substr($str, 0, self::LENGTH))
        );
        if($index){
            if(!isset($values[$index])){
                throw new Exception('invalid index ['.$index.']');
            }
            return $values[$index];
        }
        return $values;
    }
    
    
    public static function getId( $user_id, $index = false ) {
        if ($user_id != floor($user_id / 1000) * 1000) {
            $user_sub_id = $user_id;
            $user_id = floor($user_id / 1000) * 1000;
        }else
            $user_sub_id = $user_id;
        
        $values = array(
            'user_sub_id' =>  $user_sub_id,
            'user_id'     =>  $user_id
        );
        
        if($index){
            if(!isset($values[$index])){
                throw new Exception('invalid index ['.$index.']');
            }
            return $values[$index];
        }
        return $values;
    }
}
