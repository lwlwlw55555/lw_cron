<?php

/**
 * 单据信息列表
 * @author auto create
 */
class OrderLine
{
	
	/** 
	 * 实发商品数量
	 **/
	public $actual_qty;
	
	/** 
	 * 批次编号
	 **/
	public $batch_code;
	
	/** 
	 * 过期日期(YYYY-MM-DD)
	 **/
	public $expire_date;
	
	/** 
	 * 库存类型
	 **/
	public $inventory_type;
	
	/** 
	 * 商品编码
	 **/
	public $item_code;
	
	/** 
	 * 商品仓储系统编码
	 **/
	public $item_id;
	
	/** 
	 * 商品名称
	 **/
	public $item_name;
	
	/** 
	 * 单据行号
	 **/
	public $order_line_no;
	
	/** 
	 * 生产批号
	 **/
	public $produce_code;
	
	/** 
	 * 生产日期(YYYY-MM-DD)
	 **/
	public $product_date;	
}
?>