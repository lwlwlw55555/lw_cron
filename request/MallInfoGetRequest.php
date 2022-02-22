<?php

class MallInfoGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.mall.info.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
    }
}