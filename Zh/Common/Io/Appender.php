<?php
namespace Zh\Common\Io;
/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Appender extends AIo{
	private $file;

	private $handler;

	/**
	 * 构造
	 *
	 * @param File $file 文件对象
	 */
	public function __construct(File $file) {
		$this->file = $file;
	}

	/**
	 * 附加内容
	 *
	 * 加入了$force参数，如果设为true，则强制创建文件所在的多级目录不存在的部分
	 *
	 * @param string $string 需要附加的内容字符串
	 * @param boolean $force 是否强制创建多级目录
	 * @return boolean
	 */
	public function append($string, $force = false) {
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
	 * @return Q_Common_File_Handler
	 */
	public function handler() {
		return parent::fileHandler($this->file, "ab+", Handler::LOCK_EXCLUSIVE);
	}

}