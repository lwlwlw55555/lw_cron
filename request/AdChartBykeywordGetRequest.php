<?php

class AdChartBykeywordGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.ad.chart.bykeyword.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        $mustParas =['date'];
		foreach ($mustParas as $p) {
            if (!isset($this->apiParas[$p])) {
                $errMsg = 'not exists param : '.$p;
                throw new Exception($errMsg,0);
            }
        }
    }

    private $date;
    private $page;
    private $page_size;

    public function setDate($date){
        $this->date = $date;
        $this->apiParas["date"] = $date;
    }

    public function getDate(){
        return $this->date;
    }

    public function setPage($page){
        $this->page = $page;
        $this->apiParas["page"] = $page;
    }

    public function getPage(){
        return $this->page;
    }

    public function setPageSize($page_size){
        $this->page_size = $page_size;
        $this->apiParas["page_size"] = $page_size;
    }

    public function getPageSize(){
        return $this->page_size;
    }
}