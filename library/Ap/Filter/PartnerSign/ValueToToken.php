<?php

class Ap_Filter_PartnerSign_ValueToToken
    extends Ap_Filter_PartnerSign_Abstract 
    implements Zend_Filter_Interface
{
    public function filter($value)
    {
        return $this->_encoded($value);
    }
}