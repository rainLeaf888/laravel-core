<?php

namespace App\Jobs;

use App\Common\File\Export;
use App\Exceptions\BusinessException;
use Modules\Operations\Services\Task\TaskQueue;
use Modules\Assistant\Repositories\EmployeeDb;
use Modules\Assistant\Services\Employee;
use App\Common\File\Request;
use App\Common\Facades\FastDfs;
use Modules\Operations\Services\Task\TaskConst;
use Illuminate\Support\Facades\Log;
use Exception;

class ExportQrcode extends YazuoJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message  = '导出成功';
        $status   = TaskConst::TASK_FAILED;
        $fileName = '';
        $fileSize = 0;
        try {
            // 先将任务状态标记为处理中
            $this->updateTask(TaskConst::TASK_DOING);

            $file = $this->setBody($this->getRequest());

            $fileName = $file['path'];
            $fileSize = $file['size'];
            $status = TaskConst::TASK_SUCCESS;
        } catch (BusinessException $e) {
            Log::info($e);
            $message = $e->getMessage();
        } catch (Exception $e) {
            Log::info($e);
            $message = $e->getMessage();
        }
        // 更新任务状态
        $this->updateTask($status, $message, $fileName, $fileSize);
    }

    /**
     * 设置必要的参数到request 对象
     *
     * @return object
     */
    public function getRequest()
    {
        //获取任务信息
        $taskInfo = $this->getTaskById();
        $paramters = unserialize($taskInfo['request']);
        //设置任务需要用到的必要参数参数
        $request = new Request();
        $request->setParam('userId', $taskInfo['user_info_id']);
        $request->setParam('brandId', $taskInfo['brand_id']);
        $fields = ['belongMerchantId', 'employeeName', 'phoneNumber', 'employeeTagId'];
        foreach ($fields as $key => $value) {
            $request->setParam($value, '');
            if (isset($paramters[$value])) {
                $request->setParam($value, $paramters[$value]);
            }
            $filter[$value] = $request->getParam($value);
        }
        $request->setParam('filter', $filter);

        return $request;
    }

    /**
     * 导出的主体内容
     *
     * @param $request 参数对象
     */
    public function setBody($request)
    {
        $finalFile= ['path' => '', 'size'=>0];
        $service  = new Employee();
        $brandId  = $request->getParam('brandId');
        $userId   = $request->getParam('userId');
        $loop     = 1;
        $pageSize = 100;
        $filter   = $request->getParam('filter');
        $result   = $service->getEmployeeList($brandId, $filter);
        if (isset($result['totalSize']) && $result['totalSize']) {
            if ($pageSize < $result['totalSize']) {
                $loop = ceil($result['totalSize']/$pageSize);
            }
        }
        $folderName = $brandId.time();
        $dirName = storage_path().'/'.$folderName;
        mkdir($dirName);
        for ($i=1; $i <= $loop; $i++) {
            Log::info("查询次数[$i]");
            $filter['page'] = $i;
            $filter['pageSize'] = $pageSize;
            $list = $service->getEmployeeList($brandId, $filter);
            if (!empty($list['rows'])) {
                foreach ($list['rows'] as $key => $value) {
                    $this->createQrcode($brandId, $userId, $dirName, $value);
                }
            }
        }
        $zipPath = createZip($folderName, storage_path(), $dirName);
        if ($zipPath !== false) {
            $info = FastDfs::uploadFile($zipPath);
            $finalFile['path'] = $info['path'];
            $finalFile['size'] = fileSize($zipPath);
        }
        delDir($dirName);
        @unlink($zipPath);

        return $finalFile;
    }

    /**
     * 创建二维码
     *
     * @param  $brandId
     * @param  $userId  当前用户的ID
     * @param  $dirName 文件夹名称
     * @param  $row     结果集
     * @return
     */
    private function createQrcode($brandId, $userId, $dirName, $row)
    {
        $service    = new Employee();
        $employeeId = $row['employeeId'];
        $employeeNo = $row['employeeNo'];
        $brandId    = $brandId;
        $mercahntId = $row['merchantNo'];
        $tmpImage = $service->createTmpImage($userId, $employeeId, $employeeNo, $brandId, $mercahntId);
        $fileName = $row['merchantName'].'-'.$row['employeeName'].$employeeNo.'-'.$employeeId.'.png';
        \QrCode::format('png')->size(200)->merge($tmpImage['basename'], .17)
        ->margin(0)->generate($tmpImage['qrcodelink'], $dirName.'/'.$fileName);
        if (file_exists($tmpImage['fullpath'])) {
            @unlink($tmpImage['fullpath']);
        }
    }
}
