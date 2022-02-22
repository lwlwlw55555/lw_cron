<?php

/**
 * tob出库确认对象
 * @author auto create
 */
class ToBDeliveryOrderConfirmRequest
{
	
	/** 
	 * B2B
	 **/
	public $arrive_channel_type;
	
	/** 
	 * 13456
	 **/
	public $cn_order_code;
	
	/** 
	 * 1
	 **/
	public $confirm_type;
	
	/** 
	 * 13456
	 **/
	public $delivery_order_code;
	
	/** 
	 * PO0001
	 **/
	public $delivery_receipt_id;
	
	/** 
	 * 发货要求
	 **/
	public $delivery_requirement;
	
	/** 
	 * key=value;key1=value1;
	 **/
	public $extend_field;
	
	/** 
	 * 发票信息
	 **/
	public $invoince_confirms;
	
	/** 
	 * YTO
	 **/
	public $logistics_code;
	
	/** 
	 * 圆通
	 **/
	public $logistics_name;
	
	/** 
	 * 2018-09-01 12:00:00
	 **/
	public $order_confirm_time;
	
	/** 
	 * 订单明细列表
	 **/
	public $order_lines;
	
	/** 
	 * TOBCK
	 **/
	public $order_type;
	
	/** 
	 * S123124
	 **/
	public $out_biz_code;
	
	/** 
	 * 123
	 **/
	public $owner_code;
	
	/** 
	 * 包裹列表
	 **/
	public $packages;
	
	/** 
	 * 123
	 **/
	public $seller_id;
	
	/** 
	 * StoreXYZ
	 **/
	public $store_code;
	
	/** 
	 * +0800
	 **/
	public $time_zone;
	
	/** 
	 * 13456
	 **/
	public $warehouse_code;	
}
?>