<?php

class GoodsOptGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.opt.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
    if (!isset($this->apiParas['parent_opt_id']) {
            $errMsg = 'not exists param : parent_opt_id';
            throw new Exception($errMsg,0);
        }
    }

    private $parent_opt_id;

    public function setParentOptId($parent_opt_id){
        $this->parent_opt_id = $parent_opt_id;
        $this->apiParas["parent_opt_id"] = $parent_opt_id;
    }

    public function getParentOptId(){
        return $this->parent_opt_id;
    }
}