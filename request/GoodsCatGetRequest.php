<?php

class GoodsCatGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.cats.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		if (!isset($this->apiParas['parent_cat_id'])) {
            $errMsg = 'not exists param : parent_cat_id';
            throw new Exception($errMsg,0);
        }
    }

    private $parent_cat_id;

    public function setParentCatId($parent_cat_id){
        $this->parent_cat_id = $parent_cat_id;
        $this->apiParas["parent_cat_id"] = $parent_cat_id;
    }

    public function getParentCatId(){
        return $this->parent_cat_id;
    }

}