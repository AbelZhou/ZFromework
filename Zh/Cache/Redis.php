<?php
namespace Zh\Cache;
use Zh\ServerDispatcher;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Redis{
	private $redis;
	/**
	 *
	 */
	public function __construct($db = 9) {
		$servers = ServerDispatcher::factory('redis', 'redis', array(
				'section' => 'servers'
		));
		if (empty($servers)) {
			throw new CacheException('redis server is null.');
		}
		$this->redis = new Redis();
		$this->redis->connect($servers['host'], $servers['port']);
		$this->select($db);
	}

	public function lPush($key, $value) {
		return $this->redis->lPush($key, $value);
	}

	public function rPop($key) {
		return $this->redis->rPop($key);
	}

	public function delete($key) {
		return $this->redis->delete($key);
	}

	public function keys($keys) {
		return $this->redis->keys($keys);
	}

	public function select($db = 9) {
		return $this->redis->select($db);
	}
}