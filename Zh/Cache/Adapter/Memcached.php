<?php
namespace Zh\Cache\Adapter;

use Zh\Cache\ACache;
use Zh\Cache\CacheException;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-14
 */
class Memcached extends ACache {

	private $servers = array();
	public $calssss='';

	/**
	 * 初始化
	 *
	 * @param array $servers
	 * @param String $domain
	 * @param String $class
	 */
	public function __construct(array $servers, $domain = 'domain.com', $class = 'default') {
		$this->calssss=__CLASS__;
		if (empty($servers)) {
			throw new CacheException(__CLASS__ . ' :  Servers Is Null');
		}
		$this->_unset();
		parent::__construct();
		if (!empty($domain)) {
			$this->domain = $domain;
		}
		if (!empty($class)) {
			$this->class = $class;
		}
		$this->_prefix = $domain . '_' . $class;
		$this->servers = $servers;
		#初始化memcached
		$this->_cache = new \Memcached();
		$this->_cache->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
		$this->_cache->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
		$this->_cache->setOption(\Memcached::OPT_PREFIX_KEY, $domain . '.');
		$this->_cache->addServers($this->servers);
	}

	/**
	 * 设置TAG
	 *
	 * @param String $tagName
	 * @return Q_Cache_Adapter_Memcached
	 */
	public function tag($tagName) {
		$this->_verifyKey($tagName, __CLASS__, __FUNCTION__);
		$this->tagName = $tagName;
		return $this;
	}

	/**
	 * 删除Tag
	 *
	 * @param String $tagName
	 * @param Integer $delay
	 * @return boolean | array
	 */
	public function deleteTag($tagName, $delay = 0) {
		if (empty($tagName)) {
			return false;
		}
		$_tagName = $tagName;
		if (is_scalar($tagName)) {
			$_tagName = array(
				$tagName
			);
		}
		$results = array();
		foreach ($_tagName as $val) {
			$results[$val] = $this->_cache->delete($this->_makeTagKey($val), $delay);
		}
		return is_array($tagName) ? $results : $results[$tagName];
	}

	/**
	 * 替换
	 *
	 * @param String $key
	 * @param Mixed $val
	 * @param Integer $expiration
	 * @return boolean
	 */
	public function replace($key, $val, $expiration = 0) {
		$this->_verifyKey($key, __CLASS__, __FUNCTION__);
		return $this->_cache->replace($this->_keyPrefix() . '.' . md5($key), $val, intval($expiration));
	}

	/**
	 * 存入缓存数据
	 *
	 * @param String $key
	 * @param Mixed $val
	 * @param Integer $expiration
	 * @return boolean
	 */
	public function set($key, $val, $expiration = 0) {
		$this->_verifyKey($key, __CLASS__, __FUNCTION__);
		return $this->_cache->set($this->_keyPrefix() . '.' . md5($key), $val, intval($expiration));
	}

	/**
	 * 存入多个缓存
	 *
	 * @param array $items
	 * @param Integer $expiration
	 * @return bool
	 */
	public function setMulti(array $items, $expiration = 0) {
		return $this->_cache->setMulti($this->_makeItems($items), intval($expiration));
	}

	/**
	 * 重组items数据
	 *
	 * @param array $data
	 * @return array
	 */
	private function _makeItems(array &$data) {
		/**
		 * 使用数组进行重组数据 （速度慢）
		$_keys = array_keys ( $data );
		$_values = array_values ( $data );
		$_keyPrefix = array_fill ( 0, count ( $_keys ), $this->_keyPrefix () );
		$keys = array_map ( array ($this, '_realKey' ), $_keyPrefix, $_keys );
		$results = array_combine ( $keys, $_values );
		 **/
		$key_prefix = $this->_keyPrefix();
		$results = array();
		foreach ($data as $key => $val) {
			$results[$key_prefix . '.' . md5($key)] = $val;
		}
		return $results;
	}

	/**
	 * 获取缓存
	 *
	 * @param String | Array $key
	 * @return Mixed
	 */
	public function get($key) {
		$results = false;
		if (!empty($key) && is_string($key)) {
			$results = $this->_cache->get($this->_keyPrefix() . '.' . md5($key));
		}
		elseif (!empty($key) && is_array($key)) {
			$results = $this->getMulti($key);
		}
		return $results;
	}

	/**
	 * 获取多个缓存
	 *
	 * @param Array $keys
	 * @param Array $cas_tokens
	 * @param Integer $flags
	 * @return Mixed
	 */
	public function getMulti(array $keys, &$cas_tokens = null, $flags = Memcached::GET_PRESERVE_ORDER) {
		if (empty($keys)) {
			return false;
		}
		$_keyPrefix = array_fill(0, count($keys), $this->_keyPrefix());
		$realKey = array_map(array(
			$this,
			'_realKey'
		), $_keyPrefix, $keys);
		$_mc_returns = $this->_cache->getMulti($realKey, $cas_tokens, $flags);
		if (empty($_mc_returns)) {
			return $_mc_returns;
		}
		$_returns = array_combine($realKey, $keys);
		return $this->_realReturns($_mc_returns, $_returns);
	}
	/**
	 * 还原对应数据
	 *
	 * @param array $data
	 * @param array $keyMap
	 * @return array
	 */
	private function _realReturns(array $data, $keyMap) {
		$_returns = array();
		foreach ($data as $key => $val) {
			$_returns[$keyMap[$key]] = $val;
		}
		return $_returns;
	}
	/**
	 * 组合key
	 *
	 * @param String $keyPrefix
	 * @param String $key
	 * @return String
	 */
	private function _realKey($keyPrefix, $key) {
		return $keyPrefix . '.' . md5($key);
	}

	/**
	 * 删除缓存
	 *
	 * @param String $key
	 * @param Integer $delay
	 * @return bool
	 */
	public function delete($key, $delay = 0) {
		$_key = $key;
		if (is_scalar($key)) {
			$_key = array(
				$key
			);
		}
		$results = array();
		$_prefix = $this->_keyPrefix();
		foreach ($_key as $val) {
			$results[$val] = $this->_cache->delete($_prefix . '.' . md5($val), intval($delay));
		}
		return is_array($key) ? $results : $results[$key];
	}

	/**
	 * 自动减一
	 *
	 * @param String $key
	 * @param Integer $offset
	 * @return bool
	 */
	public function decrement($key, $offset = 1) {
		$this->_verifyKey($key, __CLASS__, __FUNCTION__);
		return $this->_cache->decrement($this->_keyPrefix() . '.' . md5($key), $offset);
	}

	/**
	 * 自动加一
	 *
	 * @param String $key
	 * @param Integer $offset
	 * @return bool
	 */
	public function increment($key, $offset = 1) {
		$this->_verifyKey($key, __CLASS__, __FUNCTION__);
		return $this->_cache->increment($this->_keyPrefix() . '.' . md5($key), $offset);
	}

	/**
	 * 添加缓存
	 *
	 * @param String $key
	 * @param Mixed $value
	 * @param Integer $expiration
	 * @return bool
	 */
	public function add($key, $value, $expiration = 0) {
		$this->_verifyKey($key, __CLASS__, __FUNCTION__);
		return $this->_cache->add($this->_keyPrefix() . '.' . md5($key), $value, intval($expiration));
	}

	/**
	 *
	 * 在指定服务器上的一个新的key下增加一个元素
	 * @param String $server_key
	 * @param String $key
	 * @param mixed $value
	 * @param integer $expiration
	 * @return bool
	 */
	public function addByKey($server_key, $key, $value, $expiration) {
		return $this->_cache->addByKey($server_key, $this->_keyPrefix() . '.' . md5($key), $value, $expiration);
	}

	/**
	 * 追加数据到最到
	 *
	 * @param String $key
	 * @param Mixed $value
	 * @return bool
	 */
	public function append($key, $value) {
		$this->_verifyKey($key, __CLASS__, __FUNCTION__);
		$this->isCompression();
		return $this->_cache->append($this->_keyPrefix() . '.' . md5($key), $value);
	}

	/**
	 * 追加数据到最前
	 *
	 * @param String $key
	 * @param Mixed $value
	 * @return bool
	 */
	public function prepend($key, $value) {
		$this->_verifyKey($key, __CLASS__, __FUNCTION__);
		$this->isCompression();
		return $this->_cache->prepend($this->_keyPrefix() . '.' . md5($key), $value);
	}

	/**
	 * 判断是否系统启用压缩
	 *
	 */
	private function isCompression() {
		$_compression = $this->_cache->getOption(Memcached::OPT_COMPRESSION);
		if ($_compression == true) {
			throw new CacheException('Note: If the Memcached::OPT_COMPRESSION is enabled, the operation will fail and a warning will be issued, because appending compressed data to a value that is potentially already compressed is not possible. ');
		}
	}

	/**
	 * 获取系统key前缀 (用于用户自己使用默认set、get ... ...)
	 *
	 * @return String
	 */
	public function keyPrefix() {
		return $this->_keyPrefix();
	}

	/**
	 * 获取缓存原始对象
	 *
	 * @return Memcached
	 */
	public function cache() {
		return $this->_cache;
	}

	private function _unset() {
		unset($this->_cache);
		unset($this->_servers);
		unset($this->_tmp_tag_val);
		unset($this->_tmp_tagName);
	}

	public function __destruct() {
		$this->_unset();
	}
}