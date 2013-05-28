<?php
namespace Zh\Server;
/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
interface IServer{
	/**
	 * 获取均衡服务器
	 * @param array $options
	 */
	public function loadBalanceServer(array $options=array());
}