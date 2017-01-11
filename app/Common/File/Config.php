<?php
/**
 * @导出文件配置项目
 *
 * @author guojinli@yazuo.com
 */
namespace App\Common\File;

class Config
{
    /**
     * 下载路径
     *
     * @var string
     */
    protected $downloadPath = null;

    /**
     * 用户可设置的路径
     *
     * @var string
     */
    protected $path = 'custom';

    /**
     * 导出文件类型
     * 目前提供支持类型
     * 1：csv 2：txt(K3格式) 3：excel(PHP_Excel类实现)
     *
     * @var string
     */
    protected $exportType = 'csv';

    /**
     * 导出文件名称
     *
     * @var string
     */
    protected $fileName = '';

    /**
     * 导出数据来源model
     *
     * @var string
     */
    protected $exportModel = null;

    /**
     * 导出文件数据来源方法
     *
     * @var string
     */
    protected $exportFunction = 'handle';

    /**
     * 数据库sql 中 limit 分页
     * 50000数据直接提取要比
     * 10000数据分页提取要快
     * 因此改为50000数据提取
     *
     * @var int
     */
    protected $modelLimit = 1000;

    /**
     * 文件中 limit 分页
     * 由于office软件最多打开65535条数据
     * 所以暂定为50000分页
     *
     * @var int
     */
    protected $fileLimit = 50000;

    /**
     * 是否执行zip压缩
     *
     * @var bool
     */
    protected $isZip = false;

    /**
     * 表头来源model
     *
     * @var null
     */
    protected $titleSource = '';

    /**
     * 表头类型
     *
     * @var int
     */
    protected $titleType = 0;

    /**
     * 获取表头的方法
     *
     * @var string
     */
    protected $titleFunction = '';

    /**
     * 用户自定义配置
     *
     * @var array
     */
    protected $customConfig = array();

    /**
     * 存储生成的文件
     *
     * @var array
     */
    protected $files = array();

    /**
     * 打开的文件资源
     *
     * @var resource
     */
    protected $fresource = null;

    /**
     * zip压缩目录名
     *
     * @var string
     */
    protected $zipdir = 'download';

    /**
     * 用于存储
     *
     * @var 类中产生的其他property
     */
    protected $temp = array();

    /**
     * request 对象
     *
     * @var null
     */
    protected $request = null;

    public function setDownloadPath($path)
    {
        $this->downloadPath = $path;
    }

    public function getDownloadPath()
    {
        return $this->downloadPath;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setExportType($value)
    {
        $this->exportType = $value;
    }

    public function getExportType()
    {
        return $this->exportType;
    }

    public function setFileName($value)
    {
        $this->fileName = $value;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setExportModel($value)
    {
        $this->exportModel = $value;
    }

    public function getExportModel()
    {
        return $this->exportModel;
    }

    public function setExportFunction($value)
    {
        $this->exportFunction = $value;
    }

    public function getExportFunction()
    {
        return $this->exportFunction;
    }

    public function setModelLimit($value)
    {
        $this->modelLimit = $value;
    }

    public function getModelLimit()
    {
        return $this->modelLimit;
    }

    public function setFileLimit($value)
    {
        $this->fileLimit = $value;
    }

    public function getFileLimit()
    {
        return $this->fileLimit;
    }

    public function setIsZip($value)
    {
        $this->isZip = $value;
    }

    public function getIsZip()
    {
        return $this->isZip;
    }
    
    public function setCustomConfig(array $value)
    {
        $this->customConfig = $value;
    }

    public function getCustomConfig()
    {
        return $this->customConfig;
    }

    public function setFiles($value)
    {
        $this->files = $value;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setFresource($value)
    {
        $this->fresource = $value;
    }

    public function getFresource()
    {
        return $this->fresource;
    }

    public function setZipdir($value)
    {
        $this->zipdir = $value;
    }

    public function getZipdir()
    {
        return $this->zipdir;
    }

    public function setTitleSource($value)
    {
        $this->titleSource = $value;
    }

    public function getTitleSource()
    {
        return $this->titleSource;
    }
    
    public function setTitleType($value)
    {
        $this->titleType = $value;
    }

    public function getTitleType()
    {
        return $this->titleType;
    }

    public function setTitleFunction($value)
    {
        $this->titleFunction = $value;
    }

    public function getTitleFunction()
    {
        return $this->titleFunction;
    }

    /**
     * 获取配置参数
     *
     * @param unknown_type $propertyName
     * @return multitype:
     */
    public function getParam($propertyName)
    {
        return isset($this->$propertyName) ? $this->$propertyName : $this->temp[$propertyName];
    }

    /**
     * 设置参数
     *
     * @param unknown_type $propertyName
     * @param unknown_type $value
     */
    public function setParam($propertyName, $value)
    {
        isset($this->$propertyName) ? $this->$propertyName = $value : $this->temp[$propertyName] = $value;
    }

    /**
     * 单例对象
     *
     * @var object
     */
    private static $object = null;

    private function __construct()
    {
    }

    /**
     * 获取单例对象
     *
     * @return object
     */
    public static function getInstance()
    {
        if (self::$object === null) {
            self::$object = new self();
        }
        return self::$object;
    }

    public function setRequest($value)
    {
        $this->request = $value;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
