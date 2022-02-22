<?php

class GoodsFabricContentGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.fabric.content.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		// if (!isset($this->apiParas['parent_cat_id']) {
  //           $errMsg = 'not exists param : parent_cat_id';
  //           throw new Exception($errMsg,0);
  //       }
    }

}