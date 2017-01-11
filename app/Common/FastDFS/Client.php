<?php

namespace App\Common\FastDFS;

use FastDFS\Storage;
use FastDFS\Tracker;

class Client
{
    private $storageInfo; //storage信息数组
    private $storage;  //storage连接
    private $group = 'group1';    //storage中的组名,可以为空
    private $host;
    /**
     * 构造方法
     *
     * @param $config
     *
     * @throws \FastDFS\FastDFSException
     */
    public function __construct($config)
    {
        $this->group = $config['group'];
        $this->host = $config['host'];
        if (null === $this->storageInfo) {
            $tracker = new Tracker(
                $config['tracker_host'],
                $config['tracker_port']
            );
            $this->storageInfo = $tracker->applyStorage($this->group);
        }
        $this->storage = new Storage(
            $this->storageInfo['storage_addr'],
            $this->storageInfo['storage_port']
        );
    }

    /**
     * 返回fastDFS服务器www域名
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }


    /**
     * 上传文件
     *
     * @param    string $filename 要上传的文件名，包括目录路径
     * @param    string $ext             文件的后缀名,不包含点'.'
     *
     * @example  array(
     *           'group' => 'group1',
     *           'path'  => 'M00/00/02/wKjsQFSrcvGARkfFAAHjcDFl_1A789.jpg'
     *           );
     * @return array
     */
    public function uploadFile($filename, $ext = '')
    {
        $index = $this->storageInfo['storage_index'];
        $uploadInfo = $this->storage->uploadFile($index, $filename, $ext);
        $uploadInfo['url'] = rtrim($this->getHost(), '/').'/'. $uploadInfo['group'].'/'.$uploadInfo['path'];
        $uploadInfo['image_path'] = $uploadInfo['group'].'/'.$uploadInfo['path'];
        return $uploadInfo;
    }

    /**
     * 上传从文件
     *
     * @param    string $filename       要上传的从文件名
     * @param    string $masterFilePath 主文件名
     * @param    string $prefixName     从文件的标识符;    例如,主文件为abc.jpg,从文件需要大图,添加'_b',则$prefixname = '_b';
     * @param    string $ext            从文件后缀名
     *
     * @return     Array              返回包含文件组名和文件名的数组,array('group'=>'ab','path'=>'kajdsf');
     */
    public function uploadSlaveFile(
        $filename,
        $masterFilePath,
        $prefixName,
        $ext = ''
    ) {
        $uploadInfo = $this->storage->uploadSlaveFile(
            $filename,
            $masterFilePath,
            $prefixName,
            $ext
        );

        return $uploadInfo;
    }

    /**
     * 下载文件
     *
     * @param     $filePath    要下载的文件
     * @param     $targetPath  下载的目标地址
     * @param int $offset
     * @param int $length
     *
     * @return bool
     */
    public function downloadFile(
        $filePath,
        $targetPath,
        $offset = 0,
        $length = 0
    ) {
        return $this->storage->downloadFile(
            $this->group,
            $filePath,
            $targetPath,
            $offset,
            $length
        );
    }

    /**
     * 得到文件信息
     *
     * @param string $filePath  文件路径
     *
     * @return array(
     * 'file_size'  => $fileSize,
     * 'timestamp'  => $timestamp,
     * 'crc32'      => $crc32,
     * 'storage_id' => $storageId
     * );
     */
    public function getFileInfo($filePath)
    {
        return $this->storage->getFileInfo($this->group, $filePath);
    }

    /**
     * 删除文件
     *
     * @param string $filePath  文件路径
     *
     * @return boolean 删除成功与否
     */
    public function deleteFile($filePath)
    {
        return $this->storage->deleteFile($this->group, $filePath);
    }
}
