<?php

/**
 * 请求
 * @author auto create
 */
class ErpBmsOrderGiftCancelRequest
{
	
	/** 
	 * 订单详情
	 **/
	public $order_items;
	
	/** 
	 * 货主ID
	 **/
	public $owner_user_id;
	
	/** 
	 * 状态
	 **/
	public $status;
	
	/** 
	 * 交易号
	 **/
	public $trade_id;
	
	/** 
	 * 店铺ID
	 **/
	public $user_id;	
}
?>