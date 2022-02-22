<?php

class DdyPdpUsersGetRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.ddy.pdp.users.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
    }

    private $ownerId;
    private $endModified;
    private $startModified;
    private $pageNo;
    private $pageSize;

    public function setOwnerId($ownerId){
        $this->ownerId = $ownerId;
        $this->apiParas["owner_id"] = $ownerId;
    }

    public function getOwnerId(){
        return $this->ownerId;
    }

    public function setPageNo($pageNo){
        $this->pageNo = $pageNo;
        $this->apiParas["page_no"] = $pageNo;
    }

    public function getPageNo(){
        return $this->pageNo;
    }


    public function setPageSize($pageSize){
        $this->pageSize = $pageSize;
        $this->apiParas["page_size"] = $pageSize;
    }

    public function getPageSize(){
        return $this->pageSize;
    }

    public function setEndModified($ownerId){
        $this->endModified = $endModified;
        $this->apiParas["end_modified"] = $endModified;
    }

    public function getEndModified(){
        return $this->endModified;
    }

    public function setStartModified($startModified){
        $this->startModified = $startModified;
        $this->apiParas["start_modified"] = $startModified;
    }

    public function getStartModified(){
        return $this->startModified;
    }
}
