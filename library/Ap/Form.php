<?php

class Ap_Form extends Zend_Form
{
    protected $_turn_options = array();
    
    public function isValidTurn($data, $valid = true)
    {
        foreach($this->_turn_options AS $option){
            $partData = array();
            if(!is_array($option['items'])){
                $items = $this->_elseElementsName();
            }else{
                $items = $option['items'];
            }
            if(count($items))
                foreach($items AS $item)
                    $partData[$item] = isset($data[$item])?$data[$item]:null;
            if(!$this->isValidPartial($partData))
                $valid = false;
            if(isset($option['callbacks']) and is_array($option['callbacks']))
                foreach($option['callbacks'] AS $function)
                    $this->{$function}($data);
        }
        return $valid;
    }
    
    protected function _elseElementsName()
    {
        $data = array();
        foreach($this->_turn_options AS $option){
            if(is_array($option['items'])){
                $data = array_merge($data, $option['items']);
            }
        }
        $data_out = array();
        foreach($this->getElements() AS $Element) {
            if(!in_array($Element->getName(), $data)){
                $data_out[] = $Element->getName();
            }
        }
        return $data_out;
    }
}