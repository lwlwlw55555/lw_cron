<?php

class LogisticsTicketGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.logistics.ticket.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        $mustParas =['start_updated_at', 'end_updated_at'];
		foreach ($mustParas as $p) {
            if (!isset($this->apiParas[$p])) {
                $errMsg = 'not exists param : '.$p;
                throw new Exception($errMsg,0);
            }
        }
    }

    private $start_updated_at;
    private $end_updated_at;
    private $page;
    private $page_size;

    public function setStartUpdateAt($start_updated_at){
        $this->start_updated_at = $start_updated_at;
        $this->apiParas["start_updated_at"] = $start_updated_at;
    }

    public function getStartUpdateAt(){
        return $this->start_updated_at;
    }

    public function setEndUpdateAt($end_updated_at){
        $this->end_updated_at = $end_updated_at;
        $this->apiParas["end_updated_at"] = $end_updated_at;
    }

    public function getEndUpdateAt(){
        return $this->end_updated_at;
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