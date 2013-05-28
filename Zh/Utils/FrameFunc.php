<?php
namespace Zh\Utils;
use Zend\Http\Client;

use Zh\Cache\Apc;

/**
 * 框架通用函数
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class FrameFunc{
	/**
	 * 获知php版本
	 *
	 * @param String $version
	 * @return bool
	 */
	public static function is_php($version = '5.0.0') {
		static $_is_php=array();
		$version = (string) $version;
		if (!isset($_is_php[$version])) {
			$_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
		}
		return $_is_php[$version];
	}
	/**
	 * 压缩转换成十六进制
	 *
	 * @param String $string
	 * @return String
	 */
	public static function compress($string) {
		return bin2hex(gzdeflate($string, 9));
	}

	/**
	 * 转成二进制并解压
	 *
	 * @param String $string
	 * @return String
	 */
	public static function uncompress($string) {
		return gzinflate(pack('H' . strlen($string), $string));
	}
	/**
	 * 对URL进行转码(主要防范+)
	 *
	 * @param String $input
	 * @return String
	 */
	public static function base64_url_encode($input) {
		return strtr(base64_encode($input), '+/=', '-_.');
	}

	/**
	 * 对URL进行还原
	 *
	 * @param String $input
	 * @return String
	 */
	public static function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_.', '+/='));
	}

	/**
	 * 对Str进行转码(主要防范+)
	 *
	 * @param String $input
	 * @return String
	 */
	public static function base64_str_encode($input) {
		return strtr($input, '+/=', '-_.');
	}

	/**
	 * 对Str进行还原
	 *
	 * @param String $input
	 * @return String
	 */
	public static function base64_str_decode($input) {
		return strtr($input, '-_.', '+/=');
	}

	/**
	 * 判断IP
	 *
	 * 使用字符比较
	 *
	 * @param String $str
	 * @return bool
	 */
	public static function is_ip($str) {
		if (!strcmp(long2ip(sprintf("%u", ip2long($str))), $str))
			return true;
		else
			return false;
	}

	public static function insert($array, $val, $pos = null) {
		if (null == $pos) {
			$array[] = $val;
			return $array;
		}
		else {
			$array2 = array_splice($array, $pos);
			$array[] = $val;
			$array = array_merge($array, $array2);
			return $array;
		}
	}

	/**
	 * 安全的数组合并，保持字符串和数字索引，字符串请使用array_merger以提高效率
	 *
	 * @param array $array
	 * @return array
	 */
	public static function safeMerge($array) {
		$num = func_num_args();
		for ($i = 1; $i < $num; $i++) {
			$ary = func_get_arg($i);
			foreach ($ary as $k => $v) {
				$array[$k] = $v;
			}
		}
		return $array;
	}
	/*
	 * 组合数组
	*/
	public static function safeCombine(array $keys, array $values) {
		$keys = array_values($keys);
		$values = array_values($values);
		$return = array();
		$counter = count($keys);
		for ($i = 0; $i < $counter; $i++) {
			$return[$keys[$i]] = isset($values[$i]) ? $values[$i] : false;
		}
		return $return;
	}

	public static function headLastModified($url) {
		if (!Apc::isEnabled()) {
			return time();
		}
		$key = 'resHLM.' . md5($url);
		$lastModified = Apc::get($key);
		if ($lastModified) {
			return $lastModified;
		}
		$ZHC = new Client($url);
		$lastModified = intval(strtotime($ZHC->request('HEAD')->getHeader('Last-Modified')));
		Apc::set($key, $lastModified);
		return $lastModified;
	}

	public static function sortLastModified(array $data) {
		$lastModified = 0;
		foreach ($data as $url) {
			$_lastModified = self::headLastModified($url);
			if ($_lastModified > $lastModified) {
				$lastModified = $_lastModified;
			}
		}
		return $lastModified;
	}
	/**
	 * 取GB2312字符串首字母,原理是GBK汉字是按拼音顺序编码的.
	 * @param String $input
	 * @return String
	 **/
	public static function getLetter($input) {
		$dict = array(
				'a' => 0xB0C4,
				'b' => 0xB2C0,
				'c' => 0xB4ED,
				'd' => 0xB6E9,
				'e' => 0xB7A1,
				'f' => 0xB8C0,
				'g' => 0xB9FD,
				'h' => 0xBBF6,
				'j' => 0xBFA5,
				'k' => 0xC0AB,
				'l' => 0xC2E7,
				'm' => 0xC4C2,
				'n' => 0xC5B5,
				'o' => 0xC5BD,
				'p' => 0xC6D9,
				'q' => 0xC8BA,
				'r' => 0xC8F5,
				's' => 0xCBF9,
				't' => 0xCDD9,
				'w' => 0xCEF3,
				'x' => 0xD188,
				'y' => 0xD4D0,
				'z' => 0xD7F9
		);
		$str_1 = substr($input, 0, 1);
		if ($str_1 >= chr(0x81) && $str_1 <= chr(0xfe)) {
			$num = hexdec(bin2hex(substr($input, 0, 2)));
			foreach ($dict as $k => $v) {
				if ($v >= $num) {
					break;
				}
			}
			return $k;
		}
		else {
			return $str_1;
		}
	}
	/**
	 *
	 * Api json返回数据
	 * @param Integer $code
	 * @param String $app_secret
	 * @param String $message
	 * @param mixed $data
	 * @return String
	 */
	public static function apiJsonResult($code, $app_secret = null, $message = null, $data = null) {
		$dataString = json_encode($data);
		$result = array(
				'code' => $code,
				'message' => $message,
				'data' => $data,
				'sign' => strtoupper(md5($dataString . $app_secret))
		);
		return $result;
	}

	/**
	 * 计算存放服务器(有问题）
	 *
	 * @param Integer $threshold
	 * @return String
	 */
	public static function getAvliableNode( $threshold = 100 ) {
		$config_file = '/upload_config/weight.config.php';
		if (!file_exists($config_file)) {
			return false;
		}
		include_once ($config_file);
		if (!is_array($weights)) {
			return false;
		}
		arsort($weights);
		$node_ary = array_keys($weights);
		foreach ($weights as $key => $value) {
			if (($weights[$node_ary[0]] - $value) < $threshold) {
				$tmp_node[$key] = $value;
			}
		}
		return array_rand($tmp_node);
	}
}