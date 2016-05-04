<?php

class Ap_View_Helper_Currency extends Zend_View_Helper_Currency
{
    public function currency($value = null, $currency = null)
    {
        return parent::currency(floatVal($value), $currency);
    }
}
