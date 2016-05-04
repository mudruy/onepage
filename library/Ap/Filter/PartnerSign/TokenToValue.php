<?php

class Ap_Filter_PartnerSign_TokenToValue 
    extends Ap_Filter_PartnerSign_Abstract 
    implements Zend_Filter_Interface
{
    public function filter($token)
    {
        return $this->_decoded($token);
    }
}