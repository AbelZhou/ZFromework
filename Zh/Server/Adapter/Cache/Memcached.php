<?php
namespace Zh\Server\Adapter\Cache;
use Zh\Server\AServer;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Memcached extends AServer{
	/**
	 * 驱动
	 *
	 */
	const DRIVE = 'memcached';

	/**
	 *
	 *
	 */
	const SERVER = 'cache';

	/**
	 * 列表块
	 *
	 */
	private $section = 'servers';

	/**
	 * 获取均衡后的选择服务器列表
	 * @param array $options
	 * @return array
	 */
	public function loadBalanceServer(array $options = array())
	{
		if (isset($options['section']) && $this->section == $options['section'] && !empty($this->_server))
		{
			return $this->_server;
		}
		elseif (!empty($this->_server))
		{
			return $this->_server;
		}
		elseif (isset($options['section']))
		{
			$this->section = $options['section'];
		}
		$serverArray = $this->loadConfig(self::SERVER, self::DRIVE);
		$results = $this->parseServerString($serverArray, $this->section);
		foreach ($results as $v)
		{
			$_servers = array(
					$v['host'],
					$v['port']
			);
			if (count($v) == 3)
			{
				$_servers[] = $v['weight'];
			}
			$this->_server[] = $_servers;
		}
		unset($serverObject);
		unset($results);
		return $this->_server;
	}
}