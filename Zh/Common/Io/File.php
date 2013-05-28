<?php

namespace Zh\Common\Io;

/**
 * 文件操作，权限：Linux/Uinx
 *
 * @author Abel
 * @version 0.0.1
 *          2013-4-16
 */
class File {
	private $filename;
	/**
	 * 用户权限
	 */
	const PER_USER = 1;

	/**
	 * 用户所在组权限
	 */
	const PER_GROUP = 2;

	/**
	 * 其他组权限
	 */
	const PER_OTHER = 4;

	/**
	 * 所有组权限
	 */
	const PER_ALL = 8;

	/**
	 * 文章列表
	 *
	 * @var array
	 */
	private $files;

	/**
	 * 构造器
	 *
	 * @param string $filename
	 *        	文件名
	 * @since 1.0
	 */
	public function __construct($filename) {
		$this->filename = $filename;
	}

	/**
	 * 判断是否可以执行
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function canExecute() {
		return is_executable ( $this->filename );
	}

	/**
	 * 判断该对象是否可读
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function canRead() {
		return is_readable ( $this->filename );
	}

	/**
	 * 判断该对象是否可写
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function canWrite() {
		return is_writeable ( $this->filename );
	}

	/**
	 * 创建文件
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function create() {
		if (! $this->isFile ()) {
			$fp = fopen ( $this->filename, "w+" );
			fclose ( $fp );
			return ( bool ) $fp;
		}
		return true;
	}

	/**
	 * 清空当前文件
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function clear() {
		if ($this->isFile ()) {
			$fp = fopen ( $this->filename, "w+" );
			fclose ( $fp );
			return ( bool ) $fp;
		} else if ($this->isDir ()) {
			foreach ( $this->lists () as $file ) {
				$file->delete ( true );
			}
		}
		return true;
	}

	/**
	 * 删除当前文件
	 *
	 * 如果要删除的非空目录，需指定$recursively为true才能删除
	 *
	 * @param boolean $recursively
	 *        	是否递归地删除
	 * @return boolean
	 * @since 1.0
	 */
	public function delete($recursively = true) {
		if ($this->isFile ()) {
			return unlink ( $this->filename );
		} else if ($this->isDir ()) {
			if ($recursively) {
				foreach ( $this->lists () as $file ) {
					$file->delete ( true );
				}
			}
			return rmdir ( $this->filename );
		}
		return false;
	}

	/**
	 * 检查文件/目录是否存在
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function exists() {
		return file_exists ( $this->filename );
	}

	/**
	 * 取得绝对路径的文件对象
	 *
	 * @return Q_Common_Io_File
	 * @since 1.0
	 */
	public function absFile() {
		return new File ( $this->absPath () );
	}

	/**
	 * 得到绝对路径
	 *
	 * 如果文件不存在，则返回null
	 *
	 * @return string null
	 * @since 1.0
	 */
	public function absPath() {
		if ($this->exists ()) {
			return realpath ( $this->path () );
		}
		return null;
	}

	/**
	 * 取得权限组
	 *
	 * 结果为：
	 *
	 * array(Q_Common_Io_File::PER_USER, Q_Common_Io_File::PER_GROUP,
	 * Q_Common_Io_File::PER_OTHER, Q_Common_Io_File::PER_ALL)
	 *
	 * @return array
	 * @since 1.0
	 */
	public function permissions() {
		return array (
				self::PER_USER,
				self::PER_GROUP,
				self::PER_OTHER,
				self::PER_ALL
		);
	}

	/**
	 * 得到文件名
	 *
	 * @return string
	 * @since 1.0
	 */
	public function name() {
		return basename ( $this->filename );
	}

	/**
	 * 得到上级目录路径
	 *
	 * 如果文件不存在，也返回其所在的目录
	 *
	 * @return string
	 * @since 1.0
	 */
	public function parent() {
		return dirname ( $this->filename );
	}

	/**
	 * 得到上级文件对象
	 *
	 * @return Q_Common_Io_File
	 * @since 1.0
	 */
	public function parentFile() {
		return new File ( $this->parent () );
	}

	/**
	 * 取得当前对象的路径
	 *
	 * @param boolean $original
	 *        	是否显示处理前的文件路径
	 * @return string
	 * @since 1.0
	 */
	public function path($original = false) {
		return $original ? $this->filename : preg_replace ( "/[\\/\\\\]+/", self::separator (), $this->filename );
	}

	/**
	 * 判断当前对象的路径是否为绝对路径
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function isAbs() {
		$path = $this->path ();
		if (self::isAbsPath ( $path )) {
			return true;
		}
		return false;
	}

	/**
	 * 判断是否是绝对路径
	 *
	 * @param string $path
	 *        	路径
	 * @return boolean
	 * @since 1.0
	 */
	public static function isAbsPath($path) {
	return is_absolute_path ( $path );
	}

	/**
	 * 检查当前路径是否为目录
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function isDir() {
		return is_dir ( $this->filename );
	}

	/**
	 * 检查当前路径是否为文件
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function isFile() {
		return is_file ( $this->filename );
	}

	/**
	 * 取得最后修改时间
	 *
	 * @return integer
	 * @since 1.0
	 */
	public function lastModified() {
		if ($this->exists ()) {
			return filemtime ( $this->filename );
		}
		return null;
	}

	/**
	 * 取得文件尺寸
	 *
	 * @return integer
	 * @since 1.0
	 */
	public function length() {
		return filesize ( $this->filename );
	}

	/**
	 * 取得当前目录下的文件列表
	 *
	 * 返回一个包含每一个子文件对象的数组，并排除了.和..两个路径。
	 *
	 * 从1.0开始加入$recursively参数，可以递归地查找一个目录下所有的子文件对象，包括目录。
	 *
	 * @param string $filter
	 *        	过滤规则，比如*.php
	 * @param boolean $recursively
	 *        	是否递归查找
	 * @return array
	 * @since 1.0
	 */
	public function lists($filter = null, $recursively = false) {
		if ($this->isDir ()) {
			if (! $filter) {
				$files = scandir ( $this->filename );
			} else {
				$files = glob ( $this->path () . self::separator () . $filter, GLOB_BRACE );
			}
			$list = array ();
			$found = array ();
			foreach ( $files as $file ) {
				$file = basename ( $file );
				$found [] = $file;
				if ($file != "." && $file != "..") {
					$childFile = new File ( $this->path () . "/" . $file );
					array_push ( $list, $childFile );

					// 递归地加入子文件对象
					if ($recursively && $childFile->isDir ()) {
						foreach ( $childFile->lists ( $filter, true ) as $newChildFile ) {
							array_push ( $list, $newChildFile );
						}
					}
				}
			}

			// 尝试查找目录不匹配但是文件名匹配的项
			if ($filter && $recursively) {
				$files = scandir ( $this->filename );
				foreach ( $files as $file ) {
					$file = basename ( $file );
					if (! in_array ( $file, $found ) && $file != "." && $file != "..") {
						// 递归地加入子文件对象
						$childFile = new File ( $this->path () . "/" . $file );
						if ($recursively && $childFile->isDir ()) {
							foreach ( $childFile->lists ( $filter, true ) as $newChildFile ) {
								array_push ( $list, $newChildFile );
							}
						}
					}
				}
			}
			return $list;
		}
		return array ();
	}

	/**
	 * 创建目录
	 *
	 * @param integer $mode
	 *        	模式，使用八进制表示
	 * @return boolean
	 * @since 1.0
	 */
	public function mkdir($mode = 0700) {
		if (! $this->exists ()) {
			return @mkdir ( $this->filename, $mode );
		}
		return true;
	}

	/**
	 * 创建目录
	 *
	 * 与mkdir不同，如果要创建的目录上级不存在，将自动创建
	 *
	 * @param integer $mode
	 *        	模式，使用八进制表示
	 * @return string
	 * @see mkdir
	 * @since 1.0
	 */
	public function mkdirs($mode = 0700) {
		if (! $this->exists ()) {
			return @mkdir ( $this->filename, $mode, true );
		}
		return true;
	}

	/**
	 * 改名为新的文件对象
	 *
	 * @param Q_Common_Io_File $dest
	 *        	目标文件对象
	 * @since 1.0
	 */
	public function renameTo(File $dest) {
		if (rename ( $this->filename, $dest->path () )) {
			$this->filename = $dest->path ();
		}
	}

	/**
	 * 取得文件模式（八进制）
	 *
	 * @return string
	 * @since 1.0
	 */
	public function mode() {
		if ($this->exists ()) {
			$perms = fileperms ( $this->filename );
			$oct = sprintf ( "%o", $perms );
			return substr ( $oct, - 4 );
		}
		return "0000";
	}

	// ####################### 以下方法用于支持模式的计算 ####################
	private function _octToBinary($mode) {
		return sprintf ( "%016s", base_convert ( $mode, 8, 2 ) );
	}
	private function _plusMode($newMode) {
		return bindec ( $this->_octToBinary ( $this->mode () ) | $this->_octToBinary ( $newMode ) );
	}
	private function _minusMode($newMode) {
		$a = $this->_octToBinary ( $this->mode () );
		$b = $this->_octToBinary ( $newMode );
		return bindec ( $a ) ^ bindec ( $a & $b );
	}
	private function _modeByGroup($mode, $group, $number) {
		foreach ( $this->permissions () as $permissionGroup ) {
			if ($permissionGroup & $group) {
				switch ($permissionGroup) {
					case self::PER_USER :
						$mode = substr ( $mode, 0, 1 ) . $number . substr ( $mode, 2 );
						break;
					case self::PER_GROUP :
						$mode = substr ( $mode, 0, 2 ) . $number . substr ( $mode, 3 );
						break;
					case self::PER_OTHER :
						$mode = substr ( $mode, 0, 3 ) . $number;
						break;
					case self::PER_ALL :
						$mode = "0" . str_repeat ( $number, 3 );
						break;
				}
			}
		}
		return $mode;
	}
	// ####################### 模式计算辅助方法结束 ####################

	/**
	 * 设置是否可执行
	 *
	 * @param boolean $executable
	 *        	是否可执行
	 * @param integer $group
	 *        	应用到的权限组
	 * @return boolean
	 * @since 1.0
	 */
	public function setExecutable($executable, $group = null) {
		if ($group === null) {
			$group = File::PER_ALL;
		}
		if ($this->exists ()) {
			$newMode = "0000";
			$diffMode = $this->_modeByGroup ( "0000", $group, "1" );
			if ($executable) {
				$newMode = $this->_plusMode ( $diffMode );
			} else {
				$newMode = $this->_minusMode ( $diffMode );
			}
			return chmod ( $this->filename, $newMode );
		}
		return false;
	}

	/**
	 * 设置文件最后修改时间
	 *
	 * @param integer $time
	 *        	时间戳
	 * @return boolean
	 * @since 1.0
	 */
	public function setLastModified($time) {
		if ($this->exists ()) {
			return touch ( $this->filename, $time );
		}
		return false;
	}

	/**
	 * 设置文件是否可读
	 *
	 * @param boolean $readable
	 *        	是否可读
	 * @param integer $group
	 *        	应用到的权限组
	 * @return boolean
	 * @since 1.0
	 */
	public function setReadable($readable, $group = null) {
		if ($group === null) {
			$group = File::PER_ALL;
		}
		if ($this->exists ()) {
			$newMode = 0;
			$diffMode = $this->_modeByGroup ( "0000", $group, "4" );
			if ($readable) {
				$newMode = $this->_plusMode ( $diffMode );
			} else {
				$newMode = $this->_minusMode ( $diffMode );
			}
			return chmod ( $this->filename, $newMode );
		}
		return false;
	}

	/**
	 * 设置文件只可以读
	 *
	 * @return boolean
	 * @since 1.0
	 */
	public function setReadOnly() {
		if ($this->exists ()) {
			return chmod ( $this->filename, 0444 );
		}
		return false;
	}

	/**
	 * 设定文件是否可写
	 *
	 * @param boolean $writable
	 *        	是否可写
	 * @param integer $group
	 *        	应用到的权限组
	 * @return boolean
	 * @since 1.0
	 */
	public function setWritable($writable, $group = null) {
		if ($group === null) {
			$group = File::PER_ALL;
		}
		if ($this->exists ()) {
			$newMode = 0;
			$diffMode = $this->_modeByGroup ( "0000", $group, "2" );
			if ($writable) {
				$newMode = $this->_plusMode ( $diffMode );
			} else {
				$newMode = $this->_minusMode ( $diffMode );
			}
			return chmod ( $this->filename, $newMode );
		}
		return false;
	}

	/**
	 * 返回文件的类型。可能的值有 fifo，char，dir，block，link，file 和 unknown。
	 *
	 * @return string
	 * @since 1.0
	 */
	public function type() {
		if ($this->exists ()) {
			return filetype ( $this->filename );
		}
		return null;
	}

	/**
	 * 取得目录分隔符
	 *
	 * unix/linux为/，windows为\
	 *
	 * @return string
	 * @since 1.0
	 */
	public static function separator() {
		return DIRECTORY_SEPARATOR;
	}

	/**
	 * 清除文件缓存
	 *
	 * @since 1.0
	 */
	public static function clearCache() {
		clearstatcache ();
	}

	/**
	 * 取得文件读对象
	 *
	 * 可以使用它读取文件的内容
	 *
	 * @return Q_Common_Io_Reader
	 * @since 1.0
	 */
	public function reader() {
		return new Reader ( $this );
	}

	/**
	 * 取得文件写对象
	 *
	 * 如果文件不存在，则创建新的文件；否则会先清空原来的文件内容
	 *
	 * @return Q_Common_Io_Writer
	 * @since 1.0
	 */
	public function writer() {
		return new Writer ( $this );
	}

	/**
	 * 读取文件附加对象
	 *
	 * 在文件末尾处附加新的内容
	 *
	 * @return Q_Common_Io_Appender
	 * @since 1.0
	 */
	public function appender() {
		return new Appender ( $this );
	}

	/**
	 * 取得文件扩展名
	 *
	 * 不含小数点"."符号
	 *
	 * @param boolean $tolower
	 *        	是否转换为小写
	 * @since 1.0
	 * @return string
	 */
	public function ext($tolower = false) {
		$ext = pathinfo ( $this->filename, PATHINFO_EXTENSION );
		return $tolower ? strtolower ( $ext ) : $ext;
	}

	/**
	 * 对文件进行md5计算
	 *
	 * 如果文件不存在，则返回null
	 *
	 * @return string null
	 * @since 1.0
	 */
	public function md5() {
		if ($this->exists () && $this->isFile ()) {
			return md5_file ( $this->filename );
		}
		return null;
	}

	/**
	 * 当前目录下的文件列表
	 *
	 * @return array
	 * @since 1.0.1
	 */
	public function asArray() {
		if (is_null ( $this->files )) {
			$this->refresh ();
		}
		return $this->files;
	}

	/**
	 * 刷新当前目录下的文件列表
	 *
	 * @since 1.0.1
	 */
	public function refresh() {
		$this->files = $this->lists ();
	}

	/**
	 * 判断一个偏移量是否存在
	 *
	 * 实现了 ArrayAccess 接口对应方法
	 *
	 * @param integer $index
	 * @return boolean
	 * @since 1.0
	 */
	public function offsetExists($index) {
		$this->asArray ();
		return isset ( $this->files [$index] );
	}

	/**
	 * 从一个偏移量中取得数据
	 *
	 * 实现了 ArrayAccess 接口对应方法
	 *
	 * @param integer $index
	 *        	偏移量
	 * @return mixed
	 * @since 1.0
	 */
	public function offsetGet($index) {
		$this->asArray ();
		return isset ( $this->files [$index] ) ? $this->files [$index] : null;
	}

	/**
	 * 设置偏移量位置上的值
	 *
	 * 实现了 ArrayAccess 接口对应方法
	 *
	 * @param integer $index
	 *        	偏移量
	 * @param mixed $item
	 *        	值
	 * @since 1.0
	 */
	public function offsetSet($index, $item) {
		$this->asArray ();
		$this->files [$index] = $item;
	}

	/**
	 * 删除偏移量位置对应的元素
	 *
	 * 实现了 ArrayAccess 接口对应方法
	 *
	 * @param integer $index
	 *        	偏移量
	 * @since 1.0
	 */
	public function offsetUnset($index) {
		$this->asArray ();
		if ($this->offsetExists ( $index )) {
			unset ( $this->files [$index] );
		}
	}

	/**
	 * 判断当前文件或目录是否为空
	 *
	 * @return boolean
	 * @since 1.0.1
	 */
	public function isEmpty() {
		if ($this->isDir ()) {
			return count ( $this->asArray () ) == 0;
		} else if ($this->isFile ()) {
			$reader = $this->reader ();
			$data = $reader->read ();
			$reader->close ();
			return $data == "";
		}
		return false;
	}

	/**
	 * 拷贝文件到某个路径
	 *
	 * v1.1.0支持copy一个目录
	 *
	 * @param string $path
	 *        	目标路径
	 * @return boolean
	 * @since 1.0.2
	 */
	public function copyTo($path) {
		if (! $this->exists ()) {
			return false;
		}
		if (if_is_instance_of ( $path, __CLASS__ )) {
			$path = $path->path ( true );
		}
		if ($this->isFile ()) {
			$dir = f ( $path )->parentFile ();
			if (! $dir->exists () && ! @$dir->mkdirs ()) {
				throw new IoException ( "can not copy file from '" . $this->path () . "' to '{$path}' (cannot create folder)" );
			}
			return copy ( $this->absPath (), $path );
		} elseif ($this->isDir ()) {
			$dir = f ( $path . "/" . $this->name () );
			if (! $dir->exists () && ! @$dir->mkdirs ()) {
				throw new IoException ( "can not copy file from '" . $this->path () . "' to '{$path}' (cannot create folder)" );
			}
			foreach ( $this->lists () as $subfile ) {
				$subfile->copyTo ( $dir->path () . "/" . $subfile->name () );
			}
		}
		return false;
	}

	/**
	 * 移动文件到某个路径
	 *
	 * v1.1.0支持move一个目录
	 *
	 * @param string $path
	 *        	目标路径
	 * @return boolean
	 * @since 1.0.2
	 */
	public function moveTo($path) {
		if ($this->copyTo ( $path )) {
			if ($this->delete ()) {
				$this->filename = if_is_instance_of ( $path, __CLASS__ ) ? $path->path () : $path;
				return true;
			}
		}
		return false;
	}

	/**
	 * 获取文件大小(本地和远程)
	 *
	 * @param string $uri
	 * @param string $type
	 *        	curl, fsock, header
	 * @param array|null $options
	 * @return integer
	 */
	public static function fileSize($uri, $type = 'curl', $options = null) {
		if (($return = @filesize ( $uri )) !== false)
			return $return;
		else {
			switch ($type) {
				case 'curl' :
					if (isset ( $options ) && isset ( $options ['user'] ) && isset ( $options ['pwd'] ))
						return self::_rmsCurl ( $uri, $options ['user'], $options ['pwd'] );
					else
						return self::_rmsCurl ( $uri );
					break;
				case 'fsock' :
					return self::_rmsFsock ( $uri );
					break;
				case 'header' :
					return self::_rmsHeader ( $uri );
					break;
				default :
					throw new IoException( get_class ( $this ) . ':不支持该种方法(' . $type . ')!' );
			}
		}
	}

	/**
	 * 使用curl方式获取远程文件大小，速度最快
	 *
	 * @param string $uri
	 * @param string $usr
	 * @param string $pwd
	 * @return integer
	 */
	private static function _rmsCurl($uri, $usr = '', $pwd = '') {
		// start output buffering
		ob_start ();
		// initialize curl with given uri
		$ch = curl_init ( $uri );
		// make sure we get the header
		curl_setopt ( $ch, CURLOPT_HEADER, 1 );
		// make it a http HEAD request
		curl_setopt ( $ch, CURLOPT_NOBODY, 1 );
		// if auth is needed, do it here
		if (! empty ( $usr ) && ! empty ( $pwd )) {
			$headers = array (
					'Authorization: Basic ' . base64_encode ( $usr . ':' . $pwd )
			);
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		}
		$okay = curl_exec ( $ch );
		curl_close ( $ch );
		// get the output buffer
		$head = ob_get_contents ();
		// clean the output buffer and return to previous
		// buffer settings
		ob_end_clean ();

		// gets you the numeric value from the Content-Length
		// field in the http header
		$regex = '/Content-Length:\s([0-9].+?)\s/';
		$count = preg_match ( $regex, $head, $matches );

		// if there was a Content-Length field, its value
		// will now be in $matches[1]
		if (isset ( $matches [1] )) {
			$size = $matches [1];
		} else {
			$size = 'unknown';
		}
		// $last=round($size/(1024*1024),3);
		// return $last.' MB';
		return ( integer ) $size;
	}

	/**
	 * 用fsock方式获得远程文件大小速度中
	 *
	 * @param string $uri
	 * @return integer
	 */
	private static function _rmsFsock($uri) {
		$uri = parse_url ( $uri );
		$fp = @fsockopen ( $uri ['host'], empty ( $uri ['port'] ) ? 80 : $uri ['port'], $error );
		if ($fp) {
			fputs ( $fp, "GET " . (empty ( $uri ['path'] ) ? '/' : $uri ['path']) . " HTTP/1.1\r\n" );
			fputs ( $fp, "Host:$uri[host]\r\n\r\n" );
			while ( ! feof ( $fp ) ) {
				$tmp = fgets ( $fp );
				if (trim ( $tmp ) == '') {
					break;
				} else if (preg_match ( '/Content-Length:(.*)/si', $tmp, $arr )) {
					return ( integer ) trim ( $arr [1] );
				}
			}
			return null;
		} else {
			return null;
		}
	}

	/**
	 * 使用headers方式获得远程文件大小速度最慢
	 *
	 * @param string $uri
	 * @return integer
	 */
	private static function _rmsHeader($uri) {
		$tmp = get_headers ( $uri, true );
		return ( integer ) $tmp ['Content-Length'];
	}
}