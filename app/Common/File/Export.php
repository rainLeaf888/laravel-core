<?php
/**
 * @导出文件抽象类
 *
 * @author guojinli@yazuo.com
 */
namespace App\Common\File;

use App\Exceptions\BusinessException;
use ZipArchive;
use App\Common\Facades\FastDfs;
use Illuminate\Support\Facades\Log;

class Export
{

    //保存配置选项
    protected $config = null;

    //初始化配置
    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->config->setDownloadPath(storage_path().'/app');
        $this->config->setPath('export');
    }

    /**
     * 为外部提供的导出方法
     *
     * @param array $config 必须传得条目
     *            [
     *                exportType(默认csv)
     *                fileName
     *                exportModel
     *                exportFunction(默认getList)
     *                modelLimit(默认50000)
     *                fileLimit(默认50000)
     *                request
     *                isZip
     *                titleType
     *                zipdir
     *            ]
     */
    public function export(array $config)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->execExport($config);
    }

    public function save(array $config)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        //将文件保存在服务器上
        $filepath = $this->execSave($config);
        Log::info('保存到本地路径 '.$filepath);
        //将文件传递到远程服务器上
        $info = FastDfs::uploadFile($filepath);
        Log::info('保存到远程路径 ' . var_export($info, true));
        $size = fileSize($filepath);
        //删除本地文件
        @delDir(dirname($filepath));
        return [
            'local' => '',
            'size'  => $size,
            'remote' => $info,
        ];
    }

    /**
     * 哪些参数是需要初始化的字段
     *
     * @return []
     */
    private function getMustFields()
    {
        return ['exportType', 'fileName', 'exportModel', 'exportFunction', 'modelLimit', 'fileLimit', 'isZip',
                'titleSource', 'titleType', 'titleFunction', 'customConfig', 'path', 'zipdir', 'request'];
    }

    /**
     * 初始化配置，根据参数中配置项覆盖原始配置
     *
     * @param  array  $config 配置选项
     * @return null
     */
    protected function initConfig(array $config)
    {
        $fields = $this->getMustFields();
        foreach ($fields as $field) {
            if (isset($config[$field])) {
                $method = 'set'.ucfirst($field);
                $value = $config[$field];
                if (in_array($field, ['fileName', 'zipdir'])) {
                    $value  = iconv('UTF-8', 'GBK//IGNORE', $value);
                }
                $this->config->$method($value);
                $this->$field = $value;
            } elseif ($field == 'request') {
                $request = new Request;
                $this->config->setRequest($request);
                $this->$field = $request;
            } else {
                $method = 'get'.ucfirst($field);
                $this->$field = $this->config->$method();
            }
        }
    }

    /**
     * 检验title源，数据源，支持类型
     * @return bool
     */
    protected function filterExport()
    {
        $ret = true;
        // 如果需要导出title
        if ($this->titleType) {
            if (!class_exists($this->titleSource)) {
                throw new BusinessException('表头数据model{' . $this->titleSource . '}不存在!');
                $ret = false;
            }
            $titleModel = new $this->titleSource();
            if (!method_exists($titleModel, $this->titleFunction)) {
                throw new BusinessException('数据model{'. $this->titleSource .'}中方法{'. $this->titleFunction .'}不存在!');
                $ret = false;
            }
            $this->keydata = $titleModel->{$this->titleFunction}($this->titleType);

            unset($titleModel);
        } else {
            $this->keydata = $this->customConfig['keydata'];
        }

        // 初始化数据源model
        if (!class_exists($this->exportModel)) {
            throw new BusinessException('数据model{' . $this->exportModel . '}不存在!');
            $ret = false;
        }
        $this->dataModel = new $this->exportModel();

        if (!method_exists($this->dataModel, $this->exportFunction)) {
            throw new BusinessException('数据model{' . $this->exportModel .'}中方法{'. $this->exportFunction .'}不存在!');
            $ret = false;
        }
        $this->dataFunction = $this->exportFunction;

        $exportModel = 'App\\Common\\File\\'.ucfirst($this->exportType);

        if (!class_exists($exportModel)) {
            throw new BusinessException('目前不支持' . $this->exportType . '类型的导出');
            $ret = false;
        }
        return $ret;
    }

    /**
     * 执行导出
     *
     * @param array $config
     * @param bool
     */
    protected function execExport(array $config)
    {
        $this->initConfig($config);
        $check = $this->filterExport();
        if (!$check) {
            return false;
        }
        $exportModel = 'App\\Common\\File\\'.ucfirst($this->exportType);
        $model = new $exportModel();
        $model->exec();

        return true;
    }

    /**
     * 生成数据保存到文件
     *
     * @param array $config
     * @return string
     */
    protected function execSave(array $config)
    {
        $this->initConfig($config);
        $check = $this->filterExport();
        if (!$check) {
            return false;
        }
        $exportModel = 'App\\Common\\File\\'.ucfirst($this->exportType);
        $model = new $exportModel();
        return $model->saveFile();
    }

    /**
     * 设置类中临时使用的数据
     *
     * @param string $propertyName
     * @param unknown_type $value
     */
    public function __set($propertyName, $value)
    {
        $this->config->setParam($propertyName, $value);
    }

    /**
     * 获取类中临时使用的数据
     *
     * @param string $propertyName
     * @param unknown_type $value
     * @return 类中产生的其他property
     */
    public function __get($propertyName)
    {
        return $this->config->getParam($propertyName);
    }

    /**
     * 拼装virtual(虚拟卡)
     *
     * @param array $data
     * @return array
     */
    protected function virtualdata($data)
    {
        $temp = array();
        if (isset($data['lists']['virtual'])) {
            foreach ($data['lists'] as $val) {
                if (isset($data['lists']['virtual'][$val['id']])) {
                    $temp[] = $val;
                    foreach ($data['lists']['virtual'][$val['id']] as $value) {
                        $temp[] = $value;
                    }
                } else {
                    $temp[] = $val;
                }
            }
            array_pop($temp); // 去除虚拟卡数据
            $data['lists'] = $temp;
            return $data;
        } else {
            return $data;
        }
    }

    /**
     * 处理需要导出的表头$keyvalue
     *
     * @param array $result fetchAll 得到的数据
     * @return array
     */
    protected function getKeyvalue(array $result)
    {
        $result = $this->filterKey($result);
        $result = $this->priorityKey($result);
        // 组合后得到的$keyvalue格式为：
        // array(字段名=>字段描述)
        $objectKey = array();
        $objectDesc = array();
        foreach ($result as $key => $val) {
            $objectKey[] = $val['object_key'];
            $objectDesc[] = $val['object_desc'];
        }
        $keyvalue = array_combine($objectKey, $objectDesc);

        return $keyvalue;
    }

    /**
     * 过滤需要导出的数据
     *
     * @param array $keyvalue
     * @param array $data
     * @return array
     */
    protected function getData(array $keyvalue, array $data)
    {
        $listkeys = array_keys($keyvalue);
        $lists = array();
        $temp = $keyvalue;
        $a = array_fill(0, count($temp), '');
        $temp = array_combine(array_keys($temp), $a);
        unset($a);
        if (isset($data["lists"]) && !empty($data["lists"])) {
            foreach ($data["lists"] as $keyOut => $list) {
                if (is_array($list)) {
                    foreach ($list as $key => $val) {
                        if (in_array($key, $listkeys)) {
                            $temp0[$key] = $data["lists"][$keyOut][$key];
                        }
                    }
                    $lists[] = $temp0;
                }
            }
        }
        unset($data);

        return $lists;
    }

    /**
     * 获取导出时的数据类型
     *
     * @param array $result
     *            为fetchAll得到的数据
     * @return array
     */
    protected function getDataType(array $result)
    {
        $result = $this->filterKey($result);
        $objectKey = array();
        $objectType = array();
        foreach ($result as $key => $val) {
            $objectKey[] = $val['object_key'];
            $objectType[] = $val['object_type'];
        }
        $datatype = array_combine($objectKey, $objectType);
        return $datatype;
    }

    /**
     * 过滤隐藏字段
     *
     * @param array $result fetchAll 返回的数组
     * @return array
     */
    protected function filterKey(array $result)
    {
        $result = array_filter($result, array($this, 'filter'));
        return $result;
    }

    /**
     * 回调函数
     *
     * @param array $val
     * @return boolean
     */
    protected function filter($val)
    {
        return $val['custom_object_value'] == 1;
    }

    /**
     * 为字段排序
     *
     * @param array $result fetchAll 返回的数组
     * @return array
     */
    protected function priorityKey(array $result)
    {
        // 为数组排序
        $priority = array();
        foreach ($result as $val) {
            array_push($priority, $val['priority']);
        }
        array_multisort($priority, SORT_ASC, $result);
        return $result;
    }

    /**
     * 删除目录
     *
     * @param string $path
     */
    public function delDir($path)
    {
        if (is_file($path)) {
            $path = dirname($path);
        }
        $fileArr = scandir($path);
        foreach ($fileArr as $key => $value) {
            if ($value == '.' || $value == '..') {
                continue;
            }
            $filePath = $path . '/' . $value;
            if (is_dir($filePath)) {
                $this->delDir($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($path);
    }

    /**
     * 对生成的下载文件进行打包
     *
     * @return string
     */
    protected function zip()
    {
        if (!class_exists('ZipArchive')) {
            $zipName = $this->zipdir . '.zip';
            $file = $this->fileDir . '/' . $zipName;
            system("zip -qj -r $file {$this->fileDir}");
        } else {
            // 利用ZipArchive对文件进行压缩
            $zip = new ZipArchive();
            $zipName = $this->zipdir . '.zip';
            $file = $this->fileDir . '/' . $zipName;

            if ($zip->open($file, ZIPARCHIVE::CREATE) !== true) {
                exit("cannot open <$file>\n");
            }

            // 循环将每个报表文件添加到zip压缩文件包里
            foreach ($this->files as $rep) {
                $zip->addFile($this->fileDir . '/' . $rep, $rep);
            }
            $zip->close();
            // 进行下载
            if (!file_exists($file)) {
                exit('zip file not found !');
            }
        }
        return $file;
    }

    /**
     * 下载指定文件
     *
     * @param
     *            $file
     */
    protected function download($file)
    {
        if ($this->isZip) {
            $zipName = $this->zipdir . '.zip';
            $apachemodoules = apache_get_modules();
            if (in_array('mod_xsendfile', $apachemodoules)) {
                header('X-Sendfile:' . $file);
                header('Cache-Control: public');
                header('Content-Description: File Transfer');
                header('Content-disposition: attachment; filename=' . $zipName); // 文件名
                header('Content-Type: application/zip'); // zip格式的
                header('Content-Transfer-Encoding: binary'); // 告诉浏览器，这是二进制文件
            } else {
                header('Cache-Control: public');
                header('Content-Description: File Transfer');
                header('Content-disposition: attachment; filename=' . $zipName); // 文件名
                header('Content-Type: application/zip'); // zip格式的
                header('Content-Transfer-Encoding: binary'); // 告诉浏览器，这是二进制文件
                header('Content-Length: ' . filesize($file)); // 告诉浏览器，文件大小
                @readfile($file);
                $this->delDir($file);
            }
        } else {
            $filename = $this->zipdir ? $this->zipdir . "." . $this->exportType : basename($file);
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-Type: application/' . $this->exportType);
            header("Content-Transfer-Encoding: binary"); // 二进制文件
            header('Content-Disposition: attachment;filename=' . $filename);
            @readfile($file);
            $this->delDir($file);
        }

        exit();
    }
}
