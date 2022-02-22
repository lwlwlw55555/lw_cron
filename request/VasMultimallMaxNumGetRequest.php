<?php

class VasMultimallMaxNumGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.vas.multimall.maxnum.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        if (!isset($this->apiParas['service_id'])) {
            $errMsg = 'not exists param : service_id';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['mall_id'])) {
            $errMsg = 'not exists param : mall_id';
            throw new Exception($errMsg,0);
        }
    }

    private $service_id;
    private $mall_id;


    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * @param mixed $service_id
     */
    public function setServiceId($service_id)
    {
        $this->service_id = $service_id;
        $this->apiParas["service_id"] = $service_id;
    }

    /**
     * @return mixed
     */
    public function getMallId()
    {
        return $this->mall_id;
    }

    /**
     * @param mixed $master_mall_id
     */
    public function setMallId($mall_id)
    {
        $this->mall_id = $mall_id;
        $this->apiParas["mall_id"] = $mall_id;
    }


}