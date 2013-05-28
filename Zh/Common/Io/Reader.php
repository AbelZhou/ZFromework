<?php
namespace Zh\Common\Io;
use Zh\Common\Io\Handler;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Reader extends AIo{

	/**
	 *
	 * @var File
	 */
	private $file;

	/**
	 *
	 * @param File $file
	 */
	public function __construct(File $file) {
		$this->file = $file;
	}

	/**
	 * 读取文件内容
	 *
	 * @param int $offset 开始文件指针位置
	 * @param int $length 读取的长度
	 * @return string
	 */
	public function read($offset = 0, $length = 0) {
		if ($length < 0) {
			return null;
		}
		if ($offset != 0 && $length == 0) {
			return null;
		}
		if ($offset == 0 && $length == 0) {
			return file_get_contents($this->file->path());
		}
		else {
			$handler = $this->handler();
			if ($handler->isReady()) {
				$handler->seek($offset);
				return $handler->read($length);
			}
		}
		return null;
	}

	/**
	 * 读取一行内容
	 *
	 * 返回内容包括换行符
	 *
	 * @return string|null
	 */
	public function readLine() {
		$handler = $this->handler();
		if ($handler->isReady()) {
			return fgets($handler->handler());
		}
		return null;
	}

	/**
	 * 读取单个字符
	 *
	 * @return string|null
	 */
	public function readChar() {
		$handler = $this->handler();
		if ($handler->isReady()) {
			return fgetc($handler->handler());
		}
		return null;
	}

	/**
	 * 判断指针是否到文件末尾
	 *
	 * @return boolean
	 */
	public function eof() {
		$handler = $this->handler();
		if ($handler->isReady()) {
			return feof($handler->handler());
		}
		return true;
	}

	/**
	 * 取得当前指针的位置
	 *
	 * 失败时返回-1
	 *
	 * @return integer
	 * @since 1.0
	 */
	public function pos() {
		$handler = $this->handler();
		if ($handler->isReady()) {
			return ftell($handler->handler());
		}
		return -1;
	}

	/**
	 * 取得文件句柄
	 *
	 * @return Q_Common_Io_Handler
	 */
	public function handler() {
		return parent::fileHandler($this->file, "rb+", Handler::LOCK_SHARED);
	}

	/**
	 * 读取所有行
	 *
	 * 保留行末尾的换行符
	 *
	 * @return array
	 * @since 1.0.1
	 */
	public function lines() {
		return $this->file->exists() ? file($this->file->path()) : array();
	}
}