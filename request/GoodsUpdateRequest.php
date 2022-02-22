<?php

class PddGoodsUpdateRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.information.update";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		  if (!isset($this->apiParas['goods_name'])
          || !isset($this->apiParas['goods_type'])
          || !isset($this->apiParas['goods_desc'])
          || !isset($this->apiParas['cat_id'])
          || !isset($this->apiParas['country_id'])
          || !isset($this->apiParas['market_price'])
          || !isset($this->apiParas['is_pre_sale'])
          || !isset($this->apiParas['shipment_limit_second'])
          || !isset($this->apiParas['cost_template_id'])
          || !isset($this->apiParas['is_refundable'])
          || !isset($this->apiParas['second_hand'])
          || !isset($this->apiParas['is_folt'])
          || !isset($this->apiParas['sku_list'])
          || !isset($this->apiParas['hd_thumb_url'])
          || !isset($this->apiParas['thumb_url'])
          || !isset($this->apiParas['carousel_gallery'])
          || !isset($this->apiParas['detail_gallery'])
        ) {
            $errMsg = 'not exists param for goods';
            throw new Exception($errMsg,0);
        }

        foreach (json_decode($this->apiParas['sku_list']) as $sku) {
          if (!isset($sku['spec_id_list'])
            || !isset($sku['weight'])
            || !isset($sku['quantity'])
            || !isset($sku['thumb_url'])
            || !isset($sku['multi_price'])
            || !isset($sku['price'])
            || !isset($sku['limit_quantity'])
            || !isset($sku['is_onsale'])
          ) {
              $errMsg = 'not exists param for sku';
              throw new Exception($errMsg,0);
          }
        }
        
    }

    private $goods_name;
    private $goods_type;
    private $goods_desc;
    private $cat_id;
    private $tiny_name;
    private $country_id;
    private $warehouse;
    private $customs;
    private $is_customs;
    private $market_price;
    private $is_pre_sale;
    private $pre_sale_time;
    private $shipment_limit_second;
    private $cost_template_id;
    private $customer_num;
    private $buy_limit;
    private $order_limit;
    private $is_refundable;
    private $second_hand;
    private $is_folt;
    private $fabric;
    private $fabric_content;
    private $warm_tips;
    private $shelf_life;
    private $start_production_date;
    private $end_production_date;
    private $production_standard_number;
    private $out_goods_id;
    private $hd_thumb_url;
    private $thumb_url;
    private $image_url;
    private $carousel_gallery;
    private $detail_gallery;
    private $production_license;
    private $paper_net_weight;
    private $paper_length;
    private $paper_width;
    private $paper_plies_num;
    private $sku_list;

    private $spec_id_list;
    private $weight;
    private $quantity;
    private $out_sku_sn;
    private $thumb_url;
    private $multi_price;
    private $price;
    private $limit_quantity;
    private $is_onsale;


    public function setSku($spec_id_list,$weight,$quantity,$out_sku_sn,$thumb_url,$multi_price,$price,$limit_quantity,$is_onsale){
      $sku = [];
      $sku['spec_id_list'] = $spec_id_list;
      $sku['weight'] = $weight;
      $sku['quantity'] = $quantity;
      $sku['out_sku_sn'] = $out_sku_sn;
      $sku['thumb_url'] = $thumb_url;
      $sku['multi_price'] = $multi_price;
      $sku['price'] = $price;
      $sku['limit_quantity'] = $limit_quantity;
      $sku['is_onsale'] = $is_onsale;
      $this->sku_list[] = $sku;
    }

    public function setGoodsName($goods_name){
      $this->goods_name = $goods_name;
      $this->apiParas['goods_name'] = $goods_name;
    }
    public function getGoodsName(){
      return $this->goods_name;
    }


    public function setGoodsType($goods_type){
      $this->goods_type = $goods_type;
      $this->apiParas['goods_type'] = $goods_type;
    }
    public function getGoodsType(){
      return $this->goods_type;
    }


    public function setGoodsDesc($goods_desc){
      $this->goods_desc = $goods_desc;
      $this->apiParas['goods_desc'] = $goods_desc;
    }
    public function getGoodsDesc(){
      return $this->goods_desc;
    }


    public function setCatId($cat_id){
      $this->cat_id = $cat_id;
      $this->apiParas['cat_id'] = $cat_id;
    }
    public function getCatId(){
      return $this->cat_id;
    }


    public function setTinyName($tiny_name){
      $this->tiny_name = $tiny_name;
      $this->apiParas['tiny_name'] = $tiny_name;
    }
    public function getTinyName(){
      return $this->tiny_name;
    }


    public function setCountryId($country_id){
      $this->country_id = $country_id;
      $this->apiParas['country_id'] = $country_id;
    }
    public function getCountryId(){
      return $this->country_id;
    }


    public function setWarehouse($warehouse){
      $this->warehouse = $warehouse;
      $this->apiParas['warehouse'] = $warehouse;
    }
    public function getWarehouse(){
      return $this->warehouse;
    }


    public function setCustoms($customs){
      $this->customs = $customs;
      $this->apiParas['customs'] = $customs;
    }
    public function getCustoms(){
      return $this->customs;
    }


    public function setIsCustoms($is_customs){
      $this->is_customs = $is_customs;
      $this->apiParas['is_customs'] = $is_customs;
    }
    public function getIsCustoms(){
      return $this->is_customs;
    }


    public function setMarketPrice($market_price){
      $this->market_price = $market_price;
      $this->apiParas['market_price'] = $market_price;
    }
    public function getMarketPrice(){
      return $this->market_price;
    }


    public function setIsPreSale($is_pre_sale){
      $this->is_pre_sale = $is_pre_sale;
      $this->apiParas['is_pre_sale'] = $is_pre_sale;
    }
    public function getIsPreSale(){
      return $this->is_pre_sale;
    }


    public function setPreSaleTime($pre_sale_time){
      $this->pre_sale_time = $pre_sale_time;
      $this->apiParas['pre_sale_time'] = $pre_sale_time;
    }
    public function getPreSaleTime(){
      return $this->pre_sale_time;
    }


    public function setShipmentLimitSecond($shipment_limit_second){
      $this->shipment_limit_second = $shipment_limit_second;
      $this->apiParas['shipment_limit_second'] = $shipment_limit_second;
    }
    public function getShipmentLimitSecond(){
      return $this->shipment_limit_second;
    }


    public function setCostTemplateId($cost_template_id){
      $this->cost_template_id = $cost_template_id;
      $this->apiParas['cost_template_id'] = $cost_template_id;
    }
    public function getCostTemplateId(){
      return $this->cost_template_id;
    }


    public function setCustomerNum($customer_num){
      $this->customer_num = $customer_num;
      $this->apiParas['customer_num'] = $customer_num;
    }
    public function getCustomerNum(){
      return $this->customer_num;
    }


    public function setBuyLimit($buy_limit){
      $this->buy_limit = $buy_limit;
      $this->apiParas['buy_limit'] = $buy_limit;
    }
    public function getBuyLimit(){
      return $this->buy_limit;
    }


    public function setOrderLimit($order_limit){
      $this->order_limit = $order_limit;
      $this->apiParas['order_limit'] = $order_limit;
    }
    public function getOrderLimit(){
      return $this->order_limit;
    }


    public function setIsRefundable($is_refundable){
      $this->is_refundable = $is_refundable;
      $this->apiParas['is_refundable'] = $is_refundable;
    }
    public function getIsRefundable(){
      return $this->is_refundable;
    }


    public function setSecondHand($second_hand){
      $this->second_hand = $second_hand;
      $this->apiParas['second_hand'] = $second_hand;
    }
    public function getSecondHand(){
      return $this->second_hand;
    }


    public function setIsFolt($is_folt){
      $this->is_folt = $is_folt;
      $this->apiParas['is_folt'] = $is_folt;
    }
    public function getIsFolt(){
      return $this->is_folt;
    }


    public function setFabric($fabric){
      $this->fabric = $fabric;
      $this->apiParas['fabric'] = $fabric;
    }
    public function getFabric(){
      return $this->fabric;
    }


    public function setFabricContent($fabric_content){
      $this->fabric_content = $fabric_content;
      $this->apiParas['fabric_content'] = $fabric_content;
    }
    public function getFabricContent(){
      return $this->fabric_content;
    }


    public function setWarmTips($warm_tips){
      $this->warm_tips = $warm_tips;
      $this->apiParas['warm_tips'] = $warm_tips;
    }
    public function getWarmTips(){
      return $this->warm_tips;
    }


    public function setShelfLife($shelf_life){
      $this->shelf_life = $shelf_life;
      $this->apiParas['shelf_life'] = $shelf_life;
    }
    public function getShelfLife(){
      return $this->shelf_life;
    }


    public function setStartProductionDate($start_production_date){
      $this->start_production_date = $start_production_date;
      $this->apiParas['start_production_date'] = $start_production_date;
    }
    public function getStartProductionDate(){
      return $this->start_production_date;
    }


    public function setEndProductionDate($end_production_date){
      $this->end_production_date = $end_production_date;
      $this->apiParas['end_production_date'] = $end_production_date;
    }
    public function getEndProductionDate(){
      return $this->end_production_date;
    }


    public function setProductionStandardNumber($production_standard_number){
      $this->production_standard_number = $production_standard_number;
      $this->apiParas['production_standard_number'] = $production_standard_number;
    }
    public function getProductionStandardNumber(){
      return $this->production_standard_number;
    }


    public function setOutGoodsId($out_goods_id){
      $this->out_goods_id = $out_goods_id;
      $this->apiParas['out_goods_id'] = $out_goods_id;
    }
    public function getOutGoodsId(){
      return $this->out_goods_id;
    }


    public function setHdThumbUrl($hd_thumb_url){
      $this->hd_thumb_url = $hd_thumb_url;
      $this->apiParas['hd_thumb_url'] = $hd_thumb_url;
    }
    public function getHdThumbUrl(){
      return $this->hd_thumb_url;
    }


    public function setThumbUrl($thumb_url){
      $this->thumb_url = $thumb_url;
      $this->apiParas['thumb_url'] = $thumb_url;
    }
    public function getThumbUrl(){
      return $this->thumb_url;
    }


    public function setImageUrl($image_url){
      $this->image_url = $image_url;
      $this->apiParas['image_url'] = $image_url;
    }
    public function getImageUrl(){
      return $this->image_url;
    }


    public function setCarouselGallery($carousel_gallery){
      $this->carousel_gallery = $carousel_gallery;
      $this->apiParas['carousel_gallery'] = $carousel_gallery;
    }
    public function getCarouselGallery(){
      return $this->carousel_gallery;
    }


    public function setDetailGallery($detail_gallery){
      $this->detail_gallery = $detail_gallery;
      $this->apiParas['detail_gallery'] = $detail_gallery;
    }
    public function getDetailGallery(){
      return $this->detail_gallery;
    }


    public function setProductionLicense($production_license){
      $this->production_license = $production_license;
      $this->apiParas['production_license'] = $production_license;
    }
    public function getProductionLicense(){
      return $this->production_license;
    }


    public function setPaperNetWeight($paper_net_weight){
      $this->paper_net_weight = $paper_net_weight;
      $this->apiParas['paper_net_weight'] = $paper_net_weight;
    }
    public function getPaperNetWeight(){
      return $this->paper_net_weight;
    }


    public function setPaperLength($paper_length){
      $this->paper_length = $paper_length;
      $this->apiParas['paper_length'] = $paper_length;
    }
    public function getPaperLength(){
      return $this->paper_length;
    }


    public function setPaperWidth($paper_width){
      $this->paper_width = $paper_width;
      $this->apiParas['paper_width'] = $paper_width;
    }
    public function getPaperWidth(){
      return $this->paper_width;
    }


    public function setPaperPliesNum($paper_plies_num){
      $this->paper_plies_num = $paper_plies_num;
      $this->apiParas['paper_plies_num'] = $paper_plies_num;
    }
    public function getPaperPliesNum(){
      return $this->paper_plies_num;
    }


    public function setSkuList($sku_list){
      $this->sku_list = $sku_list;
      $this->apiParas['sku_list'] = json_encode($sku_list);
    }
    public function getSkuList(){
      return $this->sku_list;
    }

}