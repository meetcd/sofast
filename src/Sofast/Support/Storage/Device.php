<?php
namespace Sofast\Support\Storage;
use Sofast\Core\Exception;

abstract class Device
{
    /**
     * 取得驱动类型名称
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * 获取存储设备说明和用途
     *
     * @return string
     */
    abstract public function getDescription(): string;

    /**
     * 获取root目录
     *
     * @return string
     */
    abstract public function getRoot(): string;

    /**
     * 每个设备都拥有在此方法中构建的复杂目录结构。
     *
     * @param $filename
     *
     * @return string
     */
    abstract public function getPath(string $filename): string;

    /**
     * 将文件上载到所选磁盘中的所需目标，成功时返回true，失败时返回false。
     *
     * @param string $source
     * @param string $path
     *
     * @throws \Exception
     *
     * @return bool
     */
    abstract public function upload($source, $path): bool;

    /**
     * 按给定路径读取文件。
     *
     * @param string $path
     *
     * @return string
     */
    abstract public function read(string $path): string;

    /**
     * 按给定路径写入文件。
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    abstract public function write(string $path, string $data, string $contentType): bool;

    /**
     * 将文件从给定源移动到给定路径，成功时返回true，失败时返回false。
     *
     * @param string $source
     * @param string $target
     *
     * @return bool
     */
    abstract public function move(string $source, string $target): bool;

    /**
     * 删除给定路径中的文件成功时返回true，失败时返回false。
     *
     * @param string $path
     * @param bool $recursive
     *
     * @return bool
     */
    abstract public function delete(string $path, bool $recursive = false): bool;

    /**
     * 检查文件是否存在。
     *
     * @param string $path
     *
     * @return bool
     */
    abstract public function exists(string $path): bool;

    /**
     * 返回给定的文件大小。
     *
     * @param $path
     *
     * @return int
     */
    abstract public function getFileSize(string $path): int;

    /**
     * 返回给定的文件的mime类型。
     *
     * @param $path
     *
     * @return string
     */
    abstract public function getFileMimeType(string $path): string;

    /**
     * 返回给定文件的MD5哈希值。
     *
     * @param $path
     *
     * @return string
     */
    abstract public function getFileHash(string $path): string;

    /**
     * 获取以字节为单位的目录大小，错误返回-1。
     *
     * @param $path
     *
     * @return int
     */
    abstract public function getDirectorySize(string $path): int;

    /**
     * 获取分区可用空间。
     *
     * @return float
     */
    abstract public function getPartitionFreeSpace(): float;

    /**
     * 获取分区总空间。
     *
     * @return float
     */
    abstract public function getPartitionTotalSpace(): float;
}
