<?php

/**
 * 出库单信息
 * @author auto create
 */
class DeliveryOrder
{
	
	/** 
	 * 出库单号
	 **/
	public $delivery_order_code;
	
	/** 
	 * 仓储系统出库单号
	 **/
	public $delivery_order_id;
	
	/** 
	 * 运单号
	 **/
	public $express_code;
	
	/** 
	 * 物流公司编码
	 **/
	public $logistics_code;
	
	/** 
	 * 物流公司名称
	 **/
	public $logistics_name;
	
	/** 
	 * 订单完成时间
	 **/
	public $order_confirm_time;
	
	/** 
	 * 出库单类型
	 **/
	public $order_type;
	
	/** 
	 * 出库单状态
	 **/
	public $status;
	
	/** 
	 * 仓库编码
	 **/
	public $warehouse_code;	
}
?>