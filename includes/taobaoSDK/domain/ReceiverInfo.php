<?php

/**
 * 收货人信息
 * @author auto create
 */
class ReceiverInfo
{
	
	/** 
	 * 区域, string (50)
	 **/
	public $receiver_infoarea;
	
	/** 
	 * 城市, string (50) , 必填
	 **/
	public $receiver_infocity;
	
	/** 
	 * 公司名称, string (200)
	 **/
	public $receiver_infocompany;
	
	/** 
	 * 国家二字码，string（50）
	 **/
	public $receiver_infocountry_code;
	
	/** 
	 * 详细地址, string (200) , 必填
	 **/
	public $receiver_infodetail_address;
	
	/** 
	 * 电子邮箱, string (50)
	 **/
	public $receiver_infoemail;
	
	/** 
	 * 证件号，string(50)
	 **/
	public $receiver_infoid;
	
	/** 
	 * 移动电话, string (50)
	 **/
	public $receiver_infomobile;
	
	/** 
	 * 姓名
	 **/
	public $receiver_infoname;
	
	/** 
	 * 省份, string (50) , 必填
	 **/
	public $receiver_infoprovince;
	
	/** 
	 * 固定电话, string (50)
	 **/
	public $receiver_infotel;
	
	/** 
	 * 村镇, string (50)
	 **/
	public $receiver_infotown;
	
	/** 
	 * 邮编, string (50)
	 **/
	public $receiver_infozip_code;	
}
?>