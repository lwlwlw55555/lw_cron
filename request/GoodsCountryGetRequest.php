<?php

class GoodsCountryGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.country.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		// if (!isset($this->apiParas['image'])) {
  //           $errMsg = 'not exists param : image';
  //           throw new Exception($errMsg,0);
  //       }
    }

    private $image;

    // public function setImage($image){
    //     $this->image = $image;
    //     $this->apiParas["image"] = $image;
    // }

    // public function getImage(){
    //     return $this->image;
    // }

}