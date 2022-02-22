<?php

class VasOrderMultiMallAddRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.vas.multimall.add";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        if (!isset($this->apiParas['service_id'])) {
            $errMsg = 'not exists param : service_id';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['major_mall_id'])) {
            $errMsg = 'not exists param : master_mall_id';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['sub_mall_ids'])) {
            $errMsg = 'not exists param : sub_mall_ids';
            throw new Exception($errMsg,0);
        }
    }

    private $service_id;
    private $master_mall_id;
    private $sub_mall_id;


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
    public function getMasterMallId()
    {
        return $this->master_mall_id;
    }

    /**
     * @param mixed $master_mall_id
     */
    public function setMasterMallId($master_mall_id)
    {
        $this->master_mall_id = $master_mall_id;
        $this->apiParas["major_mall_id"] = $master_mall_id;
    }

    /**
     * @return mixed
     */
    public function getSubMallId()
    {
        return $this->sub_mall_id;
    }

    /**
     * @param mixed $sub_mall_id
     */
    public function setSubMallId($sub_mall_id)
    {
        $this->sub_mall_id = $sub_mall_id;
        $this->apiParas["sub_mall_ids"] = $sub_mall_id;
    }


}