<?php

class GoodsLogisticsTemplateGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.logistics.template.get";
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

    private $page;
    private $page_size;

    public function setPageSize($page_size){
        $this->page_size = $page_size;
        $this->apiParas["page_size"] = $page_size;
    }

    public function getPageSize(){
        return $this->page_size;
    }

        public function setPage($page){
        $this->page = $page;
        $this->apiParas["page"] = $page;
    }

    public function getPageSize(){
        return $this->page;
    }

}