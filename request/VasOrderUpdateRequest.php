<?php

class VasOrderUpdateRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.vas.order.update";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        if (!isset($this->apiParas['out_id'])) {
            $errMsg = 'not exists param : out_id';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['time_length'])) {
            $errMsg = 'not exists param : time_length';
            throw new Exception($errMsg,0);
        }
    }

    private $out_id;
    private $time_length;

    /**
     * @return mixed
     */
    public function getOutId()
    {
        return $this->out_id;
    }

    /**
     * @param mixed $outer_id
     */
    public function setOutId($out_id)
    {
        $this->out_id = $out_id;
        $this->apiParas["out_id"] = $out_id;
    }

    /**
     * @return mixed
     */
    public function getTimeLength()
    {
        return $this->time_length;
    }

    /**
     * @param mixed $time_length
     */
    public function setTimeLength($time_length)
    {
        $this->time_length = $time_length;
        $this->apiParas["time_length"] = $time_length;
    }
}