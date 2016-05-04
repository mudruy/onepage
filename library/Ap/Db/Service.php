<?php

class Ap_Db_Service {

    protected $tn;

    public function getRowById($id)
    {
        return $this->tn->getRowById($id);
    }

    public function getArrayRowById($id)
    {
        return $this->tn->getArrayRowById($id);
    }

    public function getArrayRowsByIds($ids)
    {
        return $this->tn->getArrayRowsByIds($ids);
    }

    public function getCount()
    {
        return $this->tn->getCount();
    }
}