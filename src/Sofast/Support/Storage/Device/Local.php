<?php
namespace Sofast\Support\Storage\Device;
use Sofast\Core\Exception;
use Sofast\Support\Storage\Device;

class Local extends Device {
	/**
	 * @var string
	 */
	protected $root = '/up_files/';

	/**
	 * 构造器
	 *
	 * @param string $root
	 */
	public function __construct($root = '') {
		$this->root = $root;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return '本地存储';
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return '装载到物理机或虚拟机上的本地存储适配器。';
	}

	/**
	 * 返回更目录
	 * @return string
	 */
	public function getRoot(): string {
		return rtrim($this->root, '/') . "/";
	}

	/**
	 * @param string $filename 文件名
	 *
	 * @return string
	 */
	public function getPath($filename): string {
		$path = date("Y") . '/' . date("m");
		return $this->getRoot() . '/' . $path . '/' . $filename;
	}

	/**
	 * 上传
	 *
	 * 保存文件到本地存储中
	 *
	 * @param string $source
	 * @param string $path
	 *
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public function upload($source, $path): bool {
		$path = $this->getRoot() . $path;
		if (!file_exists(dirname($path))) {
			//检查目录是否存在
			if (!@mkdir(dirname($path), 0777, true)) {
				throw new Exception('不能创建目录: ' . \dirname($path));
			}
		}

		if (@copy($source, $path)) {
			return true;
		}

		return false;
	}

	/**
	 * 读取制定的文件
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function read(string $path): string {
		if (!$this->exists($path)) {
			throw new Exception("文件没有找到", 404);
		}
		return file_get_contents($this->getRoot() . $path);
	}

	/**
	 * 将文件内容写入存储
	 *
	 * @param string $path
	 * @param string $data
	 *
	 * @return bool
	 */
	public function write(string $path, string $data, string $contentType = ''): bool {
		$path = $this->getRoot() . $path;
		if (!file_exists(dirname($path))) {
			// Checks if directory path to file exists
			if (!@mkdir(dirname($path), 0777, true)) {
				throw new Exception('不能创建目录' . dirname($path));
			}
		}

		return (bool) file_put_contents($path, $data);
	}

	/**
	 * 将文件从给定源移动到给定路径，成功时返回true，失败时返回false。
	 *
	 * @param string $source
	 * @param string $target
	 *
	 * @return bool
	 */
	public function move(string $source, string $target): bool {
		$source = $this->getRoot() . $source;
		$target = $this->getRoot() . $target;
		if (!file_exists(dirname($target))) {
			if (!@mkdir(dirname($target), 0777, true)) {
				throw new Exception('不能创建目录' . dirname($target));
			}
		}

		if (rename($source, $target)) {
			return true;
		}

		return false;
	}

	/**
	 * 删除指定的文件，成功返回true，失败返回false。
	 *
	 * @param string $path
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete(string $path, bool $recursive = false): bool {
		$path = $this->getRoot() . $path;
		if (is_dir($path) && $recursive) {
			$files = glob($path . '*', GLOB_MARK);

			foreach ($files as $file) {
				$this->delete($file, true);
			}

			rmdir($path);
		} elseif (is_file($path)) {
			return unlink($path);
		}

		return false;
	}

	/**
	 * 检查文件是否存在
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function exists(string $path): bool {
		$path = $this->getRoot() . $path;
		return file_exists($path);
	}

	/**
	 * 返回指定文件的大小
	 *
	 * @param $path
	 *
	 * @return int
	 */
	public function getFileSize(string $path): int {
		$path = $this->getRoot() . $path;
		return filesize($path);
	}

	/**
	 * 返回文件的mine类型。
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public function getFileMimeType(string $path): string {
		$path = $this->getRoot() . $path;
		return mime_content_type($path);
	}

	/**
	 * 返回文件的MD5值
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public function getFileHash(string $path): string {
		$path = $this->getRoot() . $path;
		return md5_file($path);
	}

	/**
	 * 获取目录的大小，错误返回-1。
	 *
	 * @param $path
	 *
	 * @return int
	 */
	public function getDirectorySize(string $path): int {
		$size = 0;
		$path = $this->getRoot() . $path;
		$directory = opendir($path);

		if (!$directory) {
			return -1;
		}

		while (($file = readdir($directory)) !== false) {
			if ($file[0] == '.') {
				continue;
			}

			if (is_dir($path . $file)) {
				$size += $this->getDirectorySize($path . $file . DIRECTORY_SEPARATOR);
			} else {
				$size += filesize($path . $file);
			}
		}

		closedir($directory);

		return $size;
	}

	/**
	 * 获取存储剩余空间
	 *
	 * @return float
	 */
	public function getPartitionFreeSpace(): float {
		return disk_free_space($this->getRoot());
	}

	/**
	 * 获取存储总空间，不支持
	 *
	 * @return float
	 */
	public function getPartitionTotalSpace(): float {
		return disk_total_space($this->getRoot());
	}
}
