<?php
namespace Zh\Server;
/**
 *  服务器权重
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Weight{
	private static $result_path = '/tmp/weight.php';

	private static $weight;

	private static $tmpServer = array ();

	public static function get(array $server) {
		$_servers = array ();
		$_weight = array ();
		$result = require_once self::$result_path;
		foreach ( $server as $val ) {
			$_key = implode ( ':', $val );
			if (isset ( $result [$_key] )) {
				$_val = $result [$_key];
				$_servers [$_key] = $_val;
				$_weight [$_key] = $val;
			}
		}
		if (empty ( $_servers )) {
			return false;
		}
		asort ( $_servers );
		$weight_key = key ( array_slice ( $_servers, 0, 1 ) );
		return $_weight [$weight_key];
	}

}