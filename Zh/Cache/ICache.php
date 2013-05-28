<?php
namespace Zh\Cache;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-14
 */
interface ICache {

	/**
	 * 存入值
	 *
	 * @param String $key
	 * @param String $value
	 */
	public function set( $key, $value, $expiration = 0 );

	/**
	 * 获取值
	 *
	 * @param String | Array $key
	 */
	public function get( $key );

	/**
	 * 删除缓存
	 *
	 * @param String $key
	 * @param Integer $delay
	 */
	public function delete( $key, $delay = 0 );

	/**
	 * 自动减一
	 *
	 * @param String $key
	 * @param Integer $num
	 */
	public function decrement( $key, $num = 1 );

	/**
	 * 自动加一
	 *
	 * @param String $key
	 * @param Integer $num
	 */
	public function increment( $key, $num = 1 );
}