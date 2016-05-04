<?php

class Ap_Filter_Datapicker implements Zend_Filter_Interface
{
    
 	public function filter($value)
 	{
 		$date = new Zend_Date($value, 'y-MM-dd');
    	return $date->getTimestamp();
    }
}