<?php

class VasOrderMultiMallGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.vas.multimall.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        if (!isset($this->apiParas['mall_id'])) {
            $errMsg = 'not exists param : master_mall_id';
            throw new Exception($errMsg,0);
        }
    }

    private $mall_id;



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