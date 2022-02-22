<?php

/**
 * 包裹信息
 * @author auto create
 */
class Package
{
	
	/** 
	 * 运单号
	 **/
	public $express_code;
	
	/** 
	 * 包裹高度(厘米)
	 **/
	public $height;
	
	/** 
	 * 商品信息列表
	 **/
	public $items;
	
	/** 
	 * 包裹长度(厘米)
	 **/
	public $length;
	
	/** 
	 * 物流公司名称
	 **/
	public $logistics_name;
	
	/** 
	 * 包裹编号
	 **/
	public $package_code;
	
	/** 
	 * 包材信息
	 **/
	public $package_material_list;
	
	/** 
	 * 包裹体积(升L)
	 **/
	public $volume;
	
	/** 
	 * 包裹重量(千克)
	 **/
	public $weight;
	
	/** 
	 * 包裹宽度(厘米)
	 **/
	public $width;	
}
?>