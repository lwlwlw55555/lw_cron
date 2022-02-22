<?php

/**
 * 发货单列表
 * @author auto create
 */
class Order
{
	
	/** 
	 * 响应码
	 **/
	public $code;
	
	/** 
	 * 发货单号
	 **/
	public $delivery_order_code;
	
	/** 
	 * 拆单情况
	 **/
	public $delivery_orders;
	
	/** 
	 * 响应结果:success|failure
	 **/
	public $flag;
	
	/** 
	 * 响应信息
	 **/
	public $message;
	
	/** 
	 * null
	 **/
	public $order;
	
	/** 
	 * 单据详情
	 **/
	public $order_info;	
}
?>