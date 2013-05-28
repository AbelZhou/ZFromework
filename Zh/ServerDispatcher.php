<?php
namespace Zh;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-15
 */

class ServerDispatcher {

	/**
	 * 服务器列表
	 *
	 * @var Array
	 */
	private static $_serverList = array(
		'Cache',
		'Db',
		'File',
		'Clouds',
		'Search',
		'Ws',
		'Redis'
	);

	/**
	 * 服务器标记
	 *
	 * @var String
	 */
	private static $_server;

	/**
	 * 驱动标记
	 *
	 * @var String
	 */
	private static $_driver;

	/**
	 * server 参数
	 *
	 * @var array
	 */
	private static $_options = array();

	/**
	 * Q_Server 对象
	 *
	 * @var Object
	 */
	private static $qServer;

	/**
	 * 服务器工厂
	 *
	 * @param String $server
	 * @param String $driver
	 * @param Array $options
	 * @return Array
	 */
	public static function factory($server, $driver, array $options = array()) {
		if (!in_array(ucfirst($server), self::$_serverList)) {
			throw new ZhException('System does not support this service (' . ucfirst($server) . ').');
		}
		$deffOptions = array_diff($options, self::$_options);
		if (self::$_server == $server && self::$_driver == $driver && empty($deffOptions)) {
			return self::$qServer;
		}
		self::$_server = (string) $server;
		self::$_driver = (string) $driver;
		self::$_options = (array) $options;
		$classname = 'Zh\\Server\\Adapter\\' . ucfirst(self::$_server) . '\\' . ucfirst($driver);
		// require_once str_replace ( '_', DIRECTORY_SEPARATOR, $classname ) . '.php';
		$class = new $classname();
		//$class = new Zh\Server\Adapter\Cache\Memcached();
		return self::$qServer = $class->loadBalanceServer(self::$_options);
	}
}