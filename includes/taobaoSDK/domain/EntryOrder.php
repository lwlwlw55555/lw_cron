<?php

/**
 * 入库单信息
 * @author auto create
 */
class EntryOrder
{
	
	/** 
	 * 交易单号
	 **/
	public $buy_order_code;
	
	/** 
	 * 外部单号(以前入库单编码)
	 **/
	public $entry_in_order_code;
	
	/** 
	 * 物流单号（以前仓储系统入库单ID）
	 **/
	public $entry_order_id;
	
	/** 
	 * 入库单类型
	 **/
	public $entry_order_type;
	
	/** 
	 * 对应单号编码
	 **/
	public $entry_out_order_code;
	
	/** 
	 * 外部ERP单号
	 **/
	public $erpordercode;
	
	/** 
	 * 拓展属性数据
	 **/
	public $extend_fields;
	
	/** 
	 * 操作时间
	 **/
	public $operate_time;
	
	/** 
	 * 外部业务编码
	 **/
	public $out_biz_code;
	
	/** 
	 * 入库货主编码
	 **/
	public $owner_in_code;
	
	/** 
	 * 对应单号货主编码
	 **/
	public $owner_out_code;
	
	/** 
	 * 收货人信息
	 **/
	public $receiver_info;
	
	/** 
	 * 备注
	 **/
	public $remark;
	
	/** 
	 * 入库单预约日期
	 **/
	public $reservationdate;
	
	/** 
	 * 送货人信息
	 **/
	public $sender_info;
	
	/** 
	 * 入库单状态
	 **/
	public $status;
	
	/** 
	 * 明细总行数
	 **/
	public $total_order_lines;
	
	/** 
	 * 仓库编码
	 **/
	public $warehouse_code;	
}
?>