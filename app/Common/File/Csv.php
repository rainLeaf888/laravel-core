<?php
/**
 * @导出csv公共类
 */
namespace App\Common\File;

class Csv extends Export
{
    /**
     * 创建csv文件
     */
    protected function createCsv()
    {
        // 获取导出经过排序的表头
        $this->keyvalue = $this->getKeyvalue($this->keydata);
        //zip路径
        $this->zipdir = $this->zipdir . '_' . time();
        //判断目录是否存在
        if (is_dir($this->zipdir)) {
            $this->zipdir = $this->zipdir.rand(1000, 9999);
        }
        //当前集团的ID
        $domainId = $this->request->getParam('domainId');
        //拼接文件路径
        $this->fileDir = $this->downloadPath . '/' . $this->path . '/' . $domainId . '/' . $this->zipdir;
        // 如果没有的话递归创建
        if (!file_exists($this->fileDir)) {
            mkdir($this->fileDir, 0777, true);
        }
        //文件名字标识符，防止重名
        $this->user_id = $this->request->getParam('userId');
        $this->randname = rand(10, 99);

        //总共需要导出的数量
        $records      = $this->request->getParam('records');
        $readTotal    = ceil($records / $this->modelLimit);
        $fileLimit    = $this->fileLimit;
        //初始化局部变量
        $files        = array();
        $currFilePage = 0;
        $nextFilePage = 0;
        $fileNamePrefix  = $this->fileName . '(' . date('Y-m-d') . '_' . $this->user_id . '_' . $this->randname . ')';
        $fileName        = $fileNamePrefix . '_' . ($currFilePage + 1) . '.' . $this->exportType;
        $this->filepath  = $this->fileDir . '/' . $fileName;
        $this->fresource = fopen($this->filepath, 'w');
        $this->createTitle($this->keyvalue);
        $files[] = $fileName;
        for ($i = 0; $i < $readTotal; $i++) {
            $currFilePage = floor(($i * $this->modelLimit) / $fileLimit);
            if ($currFilePage > $nextFilePage) {
                fclose($this->fresource);
                $fileName = $fileNamePrefix . ($currFilePage + 1) . '.' . $this->exportType;
                $this->filepath = $this->fileDir . '/' . $fileName;
                $this->fresource = fopen($this->filepath, 'w');
                $this->createTitle($this->keyvalue);
                $nextFilePage = $currFilePage;
                $files[] = $fileName;
            }

            $readOffset = $i * $this->modelLimit;

            $this->request->setParam('reportLimit', $this->modelLimit);
            $this->request->setParam('reportOffset', $readOffset);
            $this->request->setParam('page', $i + 1);

            $data = $this->dataModel->{$this->dataFunction}($this->request);
            $this->createBody($data, $this->keyvalue);
            unset($data);
        }
        fclose($this->fresource);
        $this->files = $files;
        if ($this->isZip || count($files) > 1) {
            $tfile = $this->zip();
        } else {
            $tfile = $this->filepath;
        }
        $this->dataModel = null;

        return $tfile;
    }

    /**
     * 创建表头数据
     */
    protected function createTitle()
    {
        fwrite($this->fresource, iconv('UTF-8', 'GBK//IGNORE', implode(',', $this->keyvalue) . "\r\n"));
    }

    /**
     * 创建CSV格式文件
     *
     * @param Array $data
     * @param Array $keydata
     */
    protected function createBody(array $data)
    {
        $data = $this->virtualdata($data); // 拼装虚拟卡数据
        $result = $this->getData($this->keyvalue, $data); // 获取导出的数据
        $keytype = $this->getDataType($this->keydata);
        foreach ($result as $line) {
            $str = '';
            foreach ($line as $key => $val) {
                if ($keytype[$key] == 'Number') {
                    $str .= strip_tags($val) . ',';
                } elseif ($keytype[$key] == 'Text') {
                    $str .= "\"\t" . strip_tags($val) . '",';
                } elseif ($keytype[$key] == 'DateTime') {
                    $str .= "\"\t" . strip_tags($val) . '",';
                } else {
                    $str .= "\"" . strip_tags($val) . '",';
                }
            }
            //$str = iconv('UTF-8', 'GBK//IGNORE', $str);
            $str = iconv('UTF-8', 'GBK//TRANSLIT', $str);
            $str = trim($str, ',') . "\r\n";
            fwrite($this->fresource, $str);
        }
        unset($data);
        unset($result);
        unset($keytype);
    }

    /**
     * 执行保存并且下载
     * @return file
     */
    public function exec()
    {
        $file = $this->createCsv();
        $this->download($file);
    }

    /**
     * 只是保存文件到服务器
     * @return filepath
     */
    public function saveFile()
    {
        return $this->createCsv();
    }
}
