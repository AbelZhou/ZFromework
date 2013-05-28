<?php
namespace Zh\Common\Io;
/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Writer extends AIo{

	/**
	 *
	 * @var File
	 */
	private $file;

	/**
	 * 构造
	 *
	 * @param Q_Common_Io_File $file 文件对象
	 * @since 1.0
	 */
	public function __construct(File $file) {
		$this->file = $file;
	}

	/**
	 * 写入字符串
	 *
	 * 1.1.0加入了$force参数，如果设为true，则强制创建文件所在的多级目录不存在的部分
	 *
	 * @param string $string 字符串
	 * @param boolean $force 是否强制创建多级目录
	 * @return boolean
	 * @since 1.0
	 */
	public function write($string, $force = false) {
		if ($force) {
			@mkdir(dirname($this->file->path()), null, true);
		}
		$handler = $this->handler();
		if ($handler->isReady()) {
			return fwrite($handler->handler(), $string);
		}
		return false;
	}

	/**
	 * 取得文件句柄
	 *
	 * @return Q_Common_Io_Handler
	 * @since 1.0
	 */
	public function handler() {
		return parent::fileHandler($this->file, "wb+", Handler::LOCK_EXCLUSIVE);
	}
}