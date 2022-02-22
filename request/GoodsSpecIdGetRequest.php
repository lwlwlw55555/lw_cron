<?php

class GoodsSpecIdGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.spec.id.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		if (!isset($this->apiParas['parent_spec_id'] && !isset($this->apiParas['outer_id']))) {
            $errMsg = 'not exists param : image';
            throw new Exception($errMsg,0);
        }
    }

    private $parent_spec_id;
    private $spec_name;

    public function setParentSpecId($parent_spec_id){
        $this->parent_spec_id = $parent_spec_id;
        $this->apiParas["parent_spec_id"] = $parent_spec_id;
    }

    public function getParentSpecId(){
        return $this->parent_spec_id;
    }

}