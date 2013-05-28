<?php
namespace Zh\Common\Io;
use Zend\Form\Element\File;

/**
 * 文件句柄
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Handler{
	private $handler;

	const LOCK_SHARED = LOCK_SH;

	const LOCK_EXCLUSIVE = LOCK_EX;

	const LOCK_RELEASE = LOCK_UN;

	const LOCK_NOT_BLOCK = LOCK_NB;

	/**
	 *@param string $mode 文件打开模式，如 r,w,a...
	 * @param File $file
	 * @param unknown $mode
	 */
	public function __construct(File $file, $mode) {
		$this->handler = fopen($file->path(), $mode);
	}

	/**
	 * 句柄是否建立成功
	 *
	 * @return boolean
	 */
	public function isReady() {
		return (bool)$this->handler;
	}

	/**
	 * 取得文件句柄
	 *
	 * @return resource
	 */
	public function handler() {
		return $this->handler;
	}

	/**
	 * 锁定文件
	 *
	 * - 要取得共享锁定（读取的程序），将 operation 设为 LOCK_SHARED（PHP 4.0.1 以前的版本设置为 1）。
	 * - 要取得独占锁定（写入的程序），将 operation 设为 LOCK_EXCLUSIVE（PHP 4.0.1 以前的版本中设置为 2）。
	 * - 要释放锁定（无论共享或独占），将 operation 设为 LOCK_RELEASE（PHP 4.0.1 以前的版本中设置为 3）。
	 * - 如果不希望 flock() 在锁定时堵塞，则给 operation 加上 LOCK_NOT_BLOCK（PHP 4.0.1 以前的版本中设置为 4）。
	 */
	public function lock($operation) {
		if ($this->isReady()) {
			return flock($this->handler, $operation);
		}
		return false;
	}

	/**
	 * 释放锁定
	 *
	 * @return boolean
	 */
	public function release() {
		if ($this->isReady()) {
			return flock($this->handler, LOCK_UN);
		}
		return false;
	}

	/**
	 * 关闭句柄
	 *
	 * @return boolean
	 */
	public function close() {
		if ($this->isReady()) {
			return fclose($this->handler);
		}
		return false;
	}

	/**
	 * 移动文件指针
	 *
	 * @param integer $offset 偏移量
	 * @param string $whence 开始位置
	 * @return boolean
	 */
	public function seek($offset, $whence = null) {
		if ($this->isReady()) {
			return fseek($this->handler, $offset, $whence);
		}
		return false;
	}

	/**
	 * 读文件
	 *
	 * @param integer $length 长度
	 * @return string|null
	 */
	public function read($length) {
		if ($this->isReady()) {
			return fread($this->handler, $length);
		}
		return null;
	}
}