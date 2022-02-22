<?php

/**
 * 返回值
 * @author auto create
 */
class ModifyGuestPhoneDto
{
	
	/** 
	 * 是否有资格修改手机号
	 **/
	public $can_modify_guest_phone;
	
	/** 
	 * 当前已操作次数
	 **/
	public $cur_count;
	
	/** 
	 * 风控值
	 **/
	public $risk_times;	
}
?>