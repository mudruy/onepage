<?php

abstract class Ap_Filter_PartnerSign_Abstract implements Zend_Filter_Interface
{
    protected function _decoded($token)
    {
        $value = '';
        $token.= (strlen($token)%2)?'eo':'';
        for ($i=0,$n=0; $i < strlen($token); $i+=2, $n++) {
            $value.=(ord($token[$i])-100-$n);
        }
        return $value;
    }
    
    protected function _encoded($value)
    {
        $token = '';
        $value = (string)$value;
        for ($i=0; $i < strlen($value); $i++) {
            $token.=chr(100+$value[$i]+$i).chr(110+$value[$i]-$i);
        }
        return $token;
    }
}