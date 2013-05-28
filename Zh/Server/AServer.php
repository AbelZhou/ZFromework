<?php

namespace Zh\Server;

use Zh\Config\Factory;

use Zh\Cache\Apc;

use Zh\Common\Io\File;

use Zh\Utils\FrameFunc;

/**
 *Server抽象类
 *
 * @author Abel
 * @version 0.0.1
 *          2013-4-16
 */
abstract class AServer implements IServer {
	/**
	 * 默认模式
	 *
	 * @var String
	 */
	protected $schema_default = Zh_SCHEMA_LOCAL;

	/**
	 * 配置初始化
	 *
	 * @var Array
	 */
	protected $_config = array();

	/**
	 * 服务器配置
	 *
	 * @var Array
	*/
	protected $_server = array();

	/**
	 * 存储Tracker延迟时间(5秒)
	*/
	const TIMEOUT = 2;

	/**
	 * 时间正常情况下测试间隔时间(300秒)
	 *
	 */
	const EXPIRE = 300;

	/**
	 * 服务器非正常情况下检测间隔时间(1200秒)
	 *
	 */
	const GOOG_EXPIRE = 1200;

	/**
	 * 解析服务器字符
	 *
	 * @param String $svrstring
	 * @param String $section
	 * @return Array
	 */
	protected function parseServerString(array $servers, $section = '') {
		if (empty($servers)) {
			throw new ServerException(__CLASS__ . ': Did Not Find The (' . $section . ') config.');
		}
		if (!isset( $servers['server'])) {
			throw new ServerException(__CLASS__ . ': Server config format error.');
		}
		$tempServers = $servers['server'];
 		$serverResults = array();
 		foreach ($tempServers as $server){
 			$results = $this->_checkSvrAry($server);
 			if (!empty($results)) {
 				$serverResults[] = $results;
 			}
 		}
		return $serverResults;
	}

	/**
	 * 拆分字符组合服务器地址
	 *
	 * @param String $serverStr
	 * @return array
	 */
	private function _checkSvrAry($serverStr) {
		$servers = explode(':', $serverStr);
		$strCount = count($servers);
		$results = array();
		switch ($strCount) {
			case 2 :
				list($host, $port) = $servers;
				if (FrameFunc::is_ip($host)) {
					$results = array(
							'host' => (string) $host,
							'port' => (int) intval($port)
					);
				}
				break;
			case 3 :
				list($host, $port, $weight) = $servers;
				if (FrameFunc::is_ip($host)) {
					$results = array(
							'host' => (string) $host,
							'port' => (int) intval($port),
							'weight' => (int) intval($weight)
					);
				}
				break;
			default :
				$results = array();
		}
		return $results;
	}

	/**
	 * 加载 ini 配置文件
	 *
	 * @param String $server
	 * @param String $section
	 * @return Array
	 */
	protected function loadConfig($server = 'db', $section = 'mysql') {
		$sEnv = defined('RELEASE_ENV') ? RELEASE_ENV : $this->schema_default;
		#组合配置地址
		$configFile = defined('Zh_CONFIG_PATH') ? Q_CONFIG_PATH : Zh_FW_PATH . '/Config';
		$configFile .= '/' . $server . '.' . $sEnv . '.config.xml';
		$realConfigFile = realpath($configFile);
		if ($realConfigFile == false) {
			throw new ServerException(' Server File ' . $configFile . ' Not Find.');
		}
		$file = new File($realConfigFile);
		$lastModified = $file->lastModified();
		unset($file);
		$cacheFileKey = "file://" . $realConfigFile;
		if (Apc::isEnabled()) {
			$apcFileObj = Apc::get($cacheFileKey);
			if (isset($apcFileObj['config']) && !empty($apcFileObj['config']) && $lastModified == $apcFileObj['lastModified']) {
				return $apcFileObj['config'];
			}
		}
		//var_dump(Factory::fromFile($realConfigFile));exit;
		/**需要修正**/
		//$svrobj = Factory::fromFile($realConfigFile, $section);
		$svrobj = Factory::getServerArray($realConfigFile, $section);
// 		var_dump($svrobj);
// 		exit();
		if (Apc::isEnabled()) {
			$val = array(
					'config' => $svrobj,
					'lastModified' => $lastModified
			);
			Apc::set($cacheFileKey, new \ArrayObject($val));
		}
		return $svrobj;
	}

	/**
	 * 检验Server
	 *
	 * @param String $host
	 * @param Integer $port
	 * @param String $type
	 * @return bool
	 */
	protected function check($host, $port = '3306', $type = 'db') {
		if (empty($host) || empty($port) || empty($type)) {
			throw new ServerException('下列参数不合法: host:' . $host . ' port:' . $port . ' type:' . $type);
		}
		$statusKeyName = 'check://' . $type . '_' . $host . '_' . $port . '_status';
		$lastModified = time();
		$key = $host . ':' . $port;
		$apcObj = array();
		if (Apc::isEnabled()) {
			$apcObj = Apc::get($statusKeyName);
			#服务器状态为true
			if (!empty($apcObj[$key]) && $apcObj[$key]['status'] == true && ($lastModified - $apcObj[$key]['lastModified']) <= self::GOOG_EXPIRE) {
				return $apcObj[$key]['status'];
			}
			#服务器状态为false
			if (!empty($apcObj[$key]) && $apcObj[$key]['status'] == false && ($lastModified - $apcObj[$key]['lastModified']) <= self::EXPIRE) {
			return $apcObj[$key]['status'];
			}
			if (empty($apcObj)) {
			$apcObj = array();
			}
			}
			$errno = 0;
			$errstr = '';
			$status = true;
			$fs = @fsockopen($host, $port, $errno, $errstr, self::TIMEOUT);
			if (!$fs) {
			$status = false;
			}
			@fclose($fs);
			if (Apc::isEnabled()) {
			$apcObj[$host . ':' . $port] = array(
					'status' => $status,
					'lastModified' => $lastModified
			);
			Apc::set($statusKeyName, new \ArrayObject($apcObj));
			}
			return $status;
		}

		/**
		* 选择服务器
		 *
		 * @param array $servers
		 * @return array
		 */
		 public function selectServer(array $servers) {
			if (defined('Q_DB_WEIGHT') && Q_DB_WEIGHT == true) {
			$weight = Weight::get($servers);
			if ($weight != false) {
			return $weight;
			}
			}
			return $this->randServer($servers);
			}

			/**
		 * 随机选择服务器并验证
		 *
		  * @param array $servers
		  * @param String $type
		  * @return Array
		  */
		  public function randServer(array $servers, $type = 'db') {
		  $hitServer = array();
		  while (true) {
				shuffle($servers);
				if (empty($servers)) {
				break;
				}
					$randNum = mt_rand(0, count($servers) - 1);
					$hitServer = $servers[$randNum];
					unset($servers[$randNum]);
					if ($this->check($hitServer["host"], $hitServer["port"], $type)) {
					break;
					}
					}
					return $hitServer;
		  }

		  /**
		   * 替换空格
		   *
		   * @param String $str
		   * @return String
		   */
		  public function replace($str) {
		  	return str_replace(' ', '', $str);
		  }
}