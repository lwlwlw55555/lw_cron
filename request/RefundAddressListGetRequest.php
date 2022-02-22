<?php

class RefundAddressListGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.refund.address.list.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
    }
}