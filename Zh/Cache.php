<?php
namespace Zh;
use Zh\Cache\Adapter\Memcached;

use Zend\Cache\StorageFactory;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-15
 */
class Cache {

	public static $drivers = array(
		'Memcache',
		'Memcached'
	);

	private static $_cache;
	/**
	 *缓存对象工厂
	 * @param array $options
	 * @param string $driver
	 * @throws ZhException
	 * @return Memcached
	 */
	public static function factory(array $options = array(), $driver = 'Memcached') {
		$driver = ucfirst($driver);
		if (empty($driver)) {
			throw new ZhException(__CLASS__ . '/' . __FUNCTION__ . 'Driver can not be empty.');
		}
		elseif (!in_array($driver, self::$drivers)) {
			throw new ZhException(__CLASS__ . '/' . __FUNCTION__ . ' System does not support this drive (' . $driver . ')!');
		}
		//取得方法适配器
		$classname = 'Zh\\Cache\\Adapter\\' . ucfirst($driver);
		//取得均衡服务器
		$servers = ServerDispatcher::factory('cache', $driver);
		return self::$_cache = new $classname($servers, $options['domain'], $options['class']);
	}
}