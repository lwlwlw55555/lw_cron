<?php

class DdyPdpUserDeleteRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.ddy.pdp.user.delete";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        $mustParas =['owner_id'];
        foreach ($mustParas as $p) {
            if (!isset($this->apiParas[$p])) {
                $errMsg = 'not exists param : '.$p;
                throw new Exception($errMsg,0);
            }
        }
    }

    private $ownerId;

    public function setOwnerId($ownerId){
        $this->ownerId = $ownerId;
        $this->apiParas["owner_id"] = $ownerId;
    }

    public function getOwnerId(){
        return $this->ownerId;
    }
}
