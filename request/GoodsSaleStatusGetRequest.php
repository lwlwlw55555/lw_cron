<?php

class GoodsSaleStatusGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.sale.status.set";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		if (!isset($this->apiParas['goods_id']) {
            $errMsg = 'not exists param : goods_id';
            throw new Exception($errMsg,0);
        }

        if (!isset($this->apiParas['is_onsale']) {
            $errMsg = 'not exists param : is_onsale';
            throw new Exception($errMsg,0);
        }
    }

    private $goods_id;
    private $is_onsale;

    public function setGoodsId($goods_id){
        $this->goods_id = $goods_id;
        $this->apiParas["goods_id"] = $goods_id;
    }

    public function getGoodsId(){
        return $this->cat_id;
    }

    public function setIsOnsale($is_onsale){
        $this->is_onsale = $is_onsale;
        $this->apiParas["is_onsale"] = $is_onsale;
    }

    public function getIsOnsale(){
        return $this->is_onsale;
    }
}