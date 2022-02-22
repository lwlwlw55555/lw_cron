<?php
/**
 * TOP API: taobao.qimen.trade.user.delete request
 * 
 * @author auto create
 * @since 1.0, 2017.07.18
 */
require (ROOT_PATH.'includes/taobaoSDK/RequestCheckUtil.php');
class QimenTradeUserDeleteRequest
{
	
	private $apiParas = array();
	
	public function getApiMethodName()
	{
		return "taobao.qimen.trade.user.delete";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
