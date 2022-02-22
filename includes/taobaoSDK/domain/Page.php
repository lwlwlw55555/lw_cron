<?php

/**
 * result数据集
 * @author auto create
 */
class Page
{
	
	/** 
	 * 一页总长度
	 **/
	public $length;
	
	/** 
	 * 结果列表
	 **/
	public $result_list;
	
	/** 
	 * 数据开始标号
	 **/
	public $start;
	
	/** 
	 * 本次调用是否成功
	 **/
	public $success;
	
	/** 
	 * 符合条件的总条数
	 **/
	public $total_num;	
}
?>