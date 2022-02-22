<?php

class GoodsSpecGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.spec.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		if (!isset($this->apiParas['cat_id'])) {
            $errMsg = 'not exists param : cat_id';
            throw new Exception($errMsg,0);
        }
    }

    private $cat_id;

    public function setCatId($cat_id){
        $this->cat_id = $cat_id;
        $this->apiParas["cat_id"] = $cat_id;
    }

    public function getCatId(){
        return $this->cat_id;
    }

}