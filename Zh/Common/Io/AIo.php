<?php
namespace Zh\Common\Io;
/**
 * 文件操作抽象类
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
abstract class AIo{
	private $locking = false;

	private $isLocked = true;

	/**
	 *
	 * @var Handler
	 */
	private $handler;

	/**
	 * 锁定
	 *
	 */
	public function lock() {
		$this->locking = true;
	}

	/**
	 * 释放锁定
	 *
	 */
	public function release() {
		$this->locking = false;
		$this->isLocked = false;
		if ($this->handler) {
			$this->handler->release();
		}
	}

	/**
	 * 关闭当前处理器
	 *
	 */
	public function close() {
		if ($this->handler) {
			$this->release();
			$this->handler->close();
			$this->handler = null;
		}
	}

	/**
	 * 取得文件句柄
	 *
	 * @param Q_Common_Io_File $file 文件对象
	 * @param string $mode 文件打开模式
	 * @param string $lockType 锁定类型
	 * @return Q_Common_Io_Handler
	 */
	protected function fileHandler(File $file, $mode, $lockType) {
		if (!$this->handler) {
			$this->handler = new Handler($file, $mode);
			if ($this->handler->isReady()) {
				if ($this->locking == true && !$this->isLocked) {
					$this->handler->lock($lockType);
					$this->isLocked = true;
				}
			}
		}
		return $this->handler;
	}

	/**
	 * 取得文件句柄
	 *
	 * @return resource
	 */
	public abstract function handler();
}