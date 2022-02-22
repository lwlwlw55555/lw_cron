<?php

class DdyPdpUserAddRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.ddy.pdp.user.add";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        $mustParas =['rds_id'];
        foreach ($mustParas as $p) {
            if (!isset($this->apiParas[$p])) {
                $errMsg = 'not exists param : '.$p;
                throw new Exception($errMsg,0);
            }
        }
    }

    private $historyDays;
    private $rdsId;

    public function setHistoryDays($historyDays){
        $this->historyDays = $historyDays;
        $this->apiParas["history_days"] = $historyDays;
    }

    public function getHistoryDays(){
        return $this->historyDays;
    }

    public function setRdsId($rdsId){
        $this->rdsId = $rdsId;
        $this->apiParas["rds_id"] = $rdsId;
    }

    public function getRdsId(){
        return $this->$rdsId;
    }
}
