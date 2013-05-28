<?php
namespace Zh\Cache;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-14
 */
abstract class ACache implements ICache {

	protected $_cache;

	protected $domain = 'domain.com';

	protected $class = 'default';

	protected $_servers = array();

	protected $status;

	protected $tagName = 'zh_tags';

	protected $_prefix;

	protected $_tmp_tagName = '';

	protected $_tmp_tag_val = '';

	protected function __construct() {
		$this->class = defined('Zh_CACHE_DEFAULT_CLASS') ? Zh_CACHE_DEFAULT_CLASS : 'default';
		$this->domain = defined('Zh_CACHE_DEFAULT_DOMAIN') ? Zh_CACHE_DEFAULT_DOMAIN : 'domain.com';
		$this->tagName = defined('Zh_CACHE_DEFAULT_TAG') ? Zh_CACHE_DEFAULT_TAG : '';
	}

	final public function status() {
		return $this->status;
	}

	/**
	 * 设置前缀
	 * @param String $tagName
	 */
	protected function _makeTagKey( $tagName ) {
		$tn = empty($tagName) ? '' : '_' . $tagName;
		return md5($this->_prefix . $tn);
	}
	/**
	 * 制作 key 前缀
	 *
	 * @return String
	 */
	protected function _keyPrefix() {
		$_tag_key = $this->_makeTagKey($this->tagName);
		if (!empty($this->_tmp_tag_val) && $_tag_key == $this->_tmp_tagName) {
			return $this->_tmp_tag_val;
		}
		if (empty($this->tagName)) {
			return $_tag_key;
		}
		$_tag_val = $this->_cache->get($_tag_key);
		if (empty($_tag_val)) {
			$_tag_val = md5(microtime() . mt_rand() . uniqid());
			$this->_cache->set($_tag_key, $_tag_val, 0);
		}
		$this->_tmp_tagName = $_tag_key;
		$this->_tmp_tag_val = $_tag_val;
		return $_tag_val;
	}
	/**
	 * 验证key
	 *
	 * @param String $key
	 */
	protected function _verifyKey( $str, $class = '', $function = '' ) {
		if (empty($str) || !is_string($str)) {
			throw new CacheException($class . '/' . $function . ' Key must be a String');
		}
	}
}