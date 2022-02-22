<?php

class VasOrderSyncNoauthRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.vas.order.sync.noauth";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        if (!isset($this->apiParas['out_id'])) {
            $errMsg = 'not exists param : out_id';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['sku_id'])) {
            $errMsg = 'not exists param : sku_id';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['time_length'])) {
            $errMsg = 'not exists param : time_length';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['mall_id'])) {
            $errMsg = 'not exists param : mall_id';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['create_time'])) {
            $errMsg = 'not exists param : create_time';
            throw new Exception($errMsg,0);
        }
    }

    private $out_id;
    private $sku_id;
    private $time_length;
    private $mall_id;
    private $create_time;

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
        $this->outer_id = $out_id;
        $this->apiParas["out_id"] = $out_id;
    }

    /**
     * @return mixed
     */
    public function getSkuId()
    {
        return $this->sku_id;
    }

    /**
     * @param mixed $sku_id
     */
    public function setSkuId($sku_id)
    {
        $this->sku_id = $sku_id;
        $this->apiParas["sku_id"] = $sku_id;
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

    /**
     * @return mixed
     */
    public function getMallId()
    {
        return $this->mall_id;
    }

    /**
     * @param mixed $mall_id
     */
    public function setMallId($mall_id)
    {
        $this->mall_id = $mall_id;
        $this->apiParas["mall_id"] = $mall_id;
    }

    /**
     * @return mixed
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * @param mixed $create_time
     */
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;
        $this->apiParas["create_time"] = $create_time;
    }
}