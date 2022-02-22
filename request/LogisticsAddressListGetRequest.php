<?php

class LogisticsAddressListGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.logistics.address.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
    }
}