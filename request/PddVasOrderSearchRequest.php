<?php

class PddVasOrderSearchRequest {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.vas.order.search";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        if (!isset($this->apiParas['page'])) {
            $errMsg = 'not exists param : page';
            throw new Exception($errMsg,0);
        }
        if (!isset($this->apiParas['page_size'])) {
            $errMsg = 'not exists param : page_size';
            throw new Exception($errMsg,0);
        }
    }

    /*订单状态：0-未完成 1-已完成 2-已取消 空-全部*/
    private $order_status;
    private $sku_id;
    private $order_sn;
    private $mall_id;
    /*订单创建时间范围 UNIX 时间戳（ms级别）*/
    private $create_time_start;
    private $create_time_end;
    /*订单付款时间范围 UNIX 时间戳（ms级别）*/
    private $pay_time_start;
    private $pay_time_end;
    /*分页页码*/
    private $page;
    /*分页大小*/
    private $page_size;

    /**
     * @return mixed
     */
    public function getOrderStatus()
    {
        return $this->order_status;
    }

    /**
     * @param mixed $order_status
     */
    public function setOrderStatus($order_status)
    {
        $this->order_status = $order_status;
        $this->apiParas["order_status"] = $order_status;
    }

    /**
     * @return mixed
     */
    public function getSkuId()
    {
        return $this->sku_id;
    }

    /**
     * @param mixed $sku_id
     */
    public function setSkuId($sku_id)
    {
        $this->sku_id = $sku_id;
        $this->apiParas["sku_id"] = $sku_id;
    }

    /**
     * @return mixed
     */
    public function getOrderSn()
    {
        return $this->order_sn;
    }

    /**
     * @param mixed $order_sn
     */
    public function setOrderSn($order_sn)
    {
        $this->order_sn = $order_sn;
        $this->apiParas["order_sn"] = $order_sn;
    }

    /**
     * @return mixed
     */
    public function getMallId()
    {
        return $this->mall_id;
    }

    /**
     * @param mixed $mall_id
     */
    public function setMallId($mall_id)
    {
        $this->mall_id = $mall_id;
        $this->apiParas["mall_id"] = $mall_id;
    }

    /**
     * @return mixed
     */
    public function getCreateTimeStart()
    {
        return $this->create_time_start;
    }

    /**
     * @param mixed $create_time_start
     */
    public function setCreateTimeStart($create_time_start)
    {
        $this->create_time_start = $create_time_start;
        $this->apiParas["create_time_start"] = $create_time_start;
    }

    /**
     * @return mixed
     */
    public function getCreateTimeEnd()
    {
        return $this->create_time_end;
    }

    /**
     * @param mixed $create_time_end
     */
    public function setCreateTimeEnd($create_time_end)
    {
        $this->create_time_end = $create_time_end;
        $this->apiParas["create_time_end"] = $create_time_end;
    }

    /**
     * @return mixed
     */
    public function getPayTimeStart()
    {
        return $this->pay_time_start;
    }

    /**
     * @param mixed $pay_time_start
     */
    public function setPayTimeStart($pay_time_start)
    {
        $this->pay_time_start = $pay_time_start;
        $this->apiParas["pay_time_start"] = $pay_time_start;
    }

    /**
     * @return mixed
     */
    public function getPayTimeEnd()
    {
        return $this->pay_time_end;
    }

    /**
     * @param mixed $pay_time_end
     */
    public function setPayTimeEnd($pay_time_end)
    {
        $this->pay_time_end = $pay_time_end;
        $this->apiParas["pay_time_end"] = $pay_time_end;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
        $this->apiParas["page"] = $page;
    }

    /**
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * @param mixed $page_size
     */
    public function setPageSize($page_size)
    {
        $this->page_size = $page_size;
        $this->apiParas["page_size"] = $page_size;
    }


}