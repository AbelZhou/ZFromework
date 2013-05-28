<?php
namespace Zh\Cache;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Apc{
	/**
	 * 判断是否开启了apc.enabled选项
	 *
	 * 用来取代 isEnabed
	 *
	 * @return boolean
	 * @since 0.2.1
	 */
	public static function isEnabled() {
		return ( bool ) ini_get ( "apc.enabled" );
	}

	/**
	 * 存入
	 *
	 * @param String $key
	 * @param mixed $value
	 * @param integer $expire
	 * @return boolean
	 */
	public static function set($key, $value, $expire = 7200) {
		return apc_store ( $key, $value, $expire );
	}

	/**
	 * 取出
	 *
	 * @param String $key
	 * @return unknown
	 */
	public static function get($key) {
		return apc_fetch ( $key );
	}

	/**
	 * 删除
	 *
	 * @param String $key
	 * @param integer $delay
	 * @return bool
	 */
	public static function delete($key, $delay = 0) {
		return apc_delete ( $key );
	}
	/**
	 * 减1
	 *
	 * @param String $key
	 * @return unknown
	 */
	public static function decrement($key, $num = 1) {
		$val = self::get ( $key );
		if (! empty ( $val ) && is_int ( $val )) {
			$val = $val - $num;
			self::set ( $val - $num );
		}
		return $val;
	}
	/**
	 * 加1
	 *
	 * @param String $key
	 * @return unknown
	 */
	public static function increment($key, $num = 1) {
		$val = self::get ( $key );
		if (! empty ( $val ) && is_int ( $val )) {
			$val = $val + $num;
			self::set ( $val + $num );
		}
		return $val;
	}

	/**
	 * 清除
	 *
	 * @param bool $expired
	 * @return bool
	 */
	public static function clear($expired = false) {
		if (! $expired) {
			return apc_clear_cache ( "user" );
		}
		$users = apc_cache_info ( "user" );
		$list = $users ["cache_list"];
		if (! empty ( $list )) {
			foreach ( $list as $item ) {
				self::delete ( $item ["info"] );
			}
		}
		return true;
	}
	/**
	 * 缓存一个文件
	 *
	 * @param String $filename
	 * @return unknown
	 */
	public static function compileFile($filename) {
		return apc_compile_file ( $filename );
	}

	/**
	 * 存
	 *
	 * @param String $key
	 * @param unknown_type $value
	 * @param integer $ttl
	 * @return unknown
	 */
	public static function add($key, $value, $ttl = 3600) {
		return apc_add ( $key, $value, $ttl );
	}
}