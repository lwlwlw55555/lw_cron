<?php

class GoodsLogisticsTemplateCreateRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.logistics.template.create";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		// if (!isset($this->apiParas['parent_cat_id'])) {
  //           $errMsg = 'not exists param : parent_cat_id';
  //           throw new Exception($errMsg,0);
  //       }
    }

    private $cost_template_list;
    private $free_province_list;
    private $free_deliver_house_area_list;
    private $cost_type;
    private $free_deliver_house;
    private $template_name;

    public function setCostTemplateList($cost_template_list){
        $this->cost_template_list = $cost_template_list;
        $this->apiParas["cost_template_list"] = $cost_template_list;
    }

    public function getCostTemplateList(){
        return $this->cost_template_list;
    }

    public function setFreeProvinceList($free_province_list){
        $this->free_province_list = $free_province_list;
        $this->apiParas["free_province_list"] = $free_province_list;
    }

    public function getFreeProvinceList(){
        return $this->free_province_list;
    }

    public function setFreeDeliverHouseAreaList($free_deliver_house_area_list){
        $this->free_deliver_house_area_list = $free_deliver_house_area_list;
        $this->apiParas["free_deliver_house_area_list"] = $free_deliver_house_area_list;
    }

    public function getFreeDeliverHouseAreaList(){
        return $this->free_deliver_house_area_list;
    }

    public function setCostType($cost_type){
        $this->cost_type = $cost_type;
        $this->apiParas["cost_type"] = $cost_type;
    }

    public function getCostType(){
        return $this->cost_type;
    }

    public function setFreeDeliverHouse($free_deliver_house){
        $this->free_deliver_house = $free_deliver_house;
        $this->apiParas["free_deliver_house"] = $free_deliver_house;
    }

    public function getFreeDeliverHouse(){
        return $this->free_deliver_house;
    }

    public function setTemplateName($template_name){
        $this->template_name = $template_name;
        $this->apiParas["template_name"] = $template_name;
    }

    public function getTemplateName(){
        return $this->template_name;
    }

}