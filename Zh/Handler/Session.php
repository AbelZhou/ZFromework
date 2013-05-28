<?php
namespace Zh\Handler;

use Zend\Session\SessionManager;
use Zh\Cache;
use Zend\Session\SaveHandler\SaveHandlerInterface;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-14
 */
class Session implements SaveHandlerInterface {

	protected $prefix = 'Zh_';

	const LIFETIME = 1800;

	protected $domain = '.domain.com';

	protected $_lifetime = 1800;

	protected $_overrideLifetime = false;

	protected $_sessionSavePath = '';

	protected $_sessionName = '';

	protected $mem = null;
	/**
	 * Constructor
	 *
	 * @throws QLib_Exception
	 */
	public function __construct(array $potions = array()) {
		if (defined ( 'SITE_DOMAIN' )) {
			$this->domain = SITE_DOMAIN;
		} elseif (isset ( $potions ['domain'] ) && ! empty ( $potions ['domain'] )) {
			$this->domain = $potions ['domain'];
		}
		ini_set ( 'session.cookie_domain', $this->domain );
		//兼容使用 GET/POST 变量方式
		ini_set ( 'session.use_trans_sid', 1 );
		//设置垃圾收集的触发几率
		ini_set ( 'session.gc_probability', 1 );
		//设置垃圾收集的触发几率除数
		ini_set ( 'session.gc_divisor', 100 );
		//设置垃圾回收最大生存时间,浏览器生存周期
		ini_set ( 'session.gc_maxlifetime', self::LIFETIME );
		//使用 COOKIE 保存 SESSION ID 的方式
		ini_set ( 'session.use_cookies', 1 );
		ini_set ( 'session.cookie_path', '/' );
		//多主机共享保存 SESSION ID 的 COOKIE
		//将 session.save_handler 设置为 user
		//session_module_name ( 'user' );
		//定义 SESSION 各项操作所对应的方法名;
		$this->createCacheObj ();
		$this->setLifetime ( self::LIFETIME );
		$this->setOverrideLifetime ( true );
	}
	/**
	 * 创建Cache对象
	 *
	 * @return Instance of Cache | false
	 */
	private function createCacheObj() {
		$this->mem = Cache::factory ( array ('domain' => 'www' . $this->domain, 'class' => 'session' ) );
		if (! isset ( $this->mem ) || ! $this->mem) {
			throw new SessionException ( 'All cache servers down.' );
		}
		return $this->mem;
	}

	public function __destruct() {
		SessionManager::writeClose ();
		$this->mem = null;
	}

	/**
	 * Set session lifetime and optional whether or not the lifetime of an existing session should be overridden
	 * $lifetime === false resets lifetime to session.gc_maxlifetime
	 *
	 * @param int $lifetime
	 * @param boolean $overrideLifetime
	 * @throws SessionException
	 * @return \Zh\Session\Handler
	 */
	public function setLifetime($lifetime, $overrideLifetime = null) {
		if ($lifetime < 0) {
			throw new SessionException ( '生存周期不能设置为小于0!' );
		} else if (empty ( $lifetime ))
			$this->_lifetime = ( int ) ini_get ( 'session.gc_maxlifetime' );
		else
			$this->_lifetime = ( int ) $lifetime;
		if ($overrideLifetime != null)
			$this->setOverrideLifetime ( $overrideLifetime );
		return $this;
	}
	/**
	 * Retrieve session lifetime
	 *
	 * @return int
	 */
	public function getLifetime() {
		return $this->_lifetime;
	}

	/**
	 * Set whether or not the lifetime of an existing session should be overridden
	 * @param boolean $overrideLifetime
	 * @return \Zh\Session\Handler
	 */
	public function setOverrideLifetime($overrideLifetime) {
		$this->_overrideLifetime = ( boolean ) $overrideLifetime;

		return $this;
	}

	public function getOverrideLifetime() {
		return $this->_overrideLifetime;
	}

	/**
	 * Retrieve session lifetime considering
	 *
	 * @param array $value
	 * @return int
	 */
	public function open($save_path, $name) {
		$this->_sessionSavePath = $save_path;
		$this->_sessionName = $name;
		return true;
	}

	/**
	 * Retrieve session expiration time
	 *
	 * @param * @param array $value
	 * @return int
	 */
	public function close() {
		return true;
	}

	/**
	 * 读取接口
	 *
	 * @param string $id
	 * @return mixed | false
	 */
	public function read($id) {
		$return = '';
		$value = $this->mem->get ( $this->prefix . $id ); //获取数据
		if ($value) {
			if ($this->_getExpirationTime ( $value ) > time ())
				$return = $value ['data'];
			else
				$this->destroy ( $id );
		}
		return $return;
	}

	/**
	 * 写入接口
	 *
	 * @param string $id
	 * @param mixed $data
	 * @return boolean
	 */
	public function write($id, $data) {
		$return = false;
		$insertDate = array ('modified' => time (), 'data' => ( string ) $data );
		$value = $this->mem->get ( $this->prefix . $id ); //获取数据
		if ($value) {
			$insertDate ['lifetime'] = $this->_getLifetime ( $value );
			$return = $this->_overrideLifetime ? $this->mem->set ( $this->prefix . $id, $insertDate, $insertDate ['lifetime'] ) : $this->mem->set ( $this->prefix . $id, $insertDate );
		} else {
			$insertDate ['lifetime'] = $this->_lifetime;
			$return = $this->mem->set ( $this->prefix . $id, $insertDate, $this->_lifetime );
		}
		return $return;
	}

	/**
	 * 摧毁ITEM
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function destroy($id) {
		return $this->mem->delete ( $this->prefix . $id );
	}

	/**
	 * 垃圾收集
	 *
	 * @param integer $maxlifetime
	 * @return true
	 */
	public function gc($maxlifetime) {
		return true;
	}

	/**
	 * 获取生存时间
	 *
	 * @param String $value
	 * @return Integer
	 */
	protected function _getLifetime($value) {
		$return = $this->_lifetime;
		if (! $this->_overrideLifetime) {
			$return = ( int ) $value ['lifetime'];
		}
		return $return;
	}

	protected function _getExpirationTime($value) {
		return ( int ) $value ['modified'] + $this->_getLifetime ( $value );
	}
}