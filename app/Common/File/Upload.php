<?php
/**
 * @上传文件
 *
 * @author guojinli@yazuo.com
 */
namespace App\Common\File;

use App\Exceptions\BusinessException;
use App\Common\Facades\FastDfs;
use Domain;
use Input;

class Upload
{
    /**
     * 保存文件到本地和远程服务器
     *
     * @param  $field 上传时的字段名称
     * @param  $dir   需要保存到的文件夹
     * @return []
     */
    public function saveFile($field, $dir)
    {
        $user = Domain::getSession();
        $brandId    = $user['currentChainId'];
        $merchantId = $user['currentMerchantId'];
        $fileObject = Input::file($field);
        if (!$fileObject->isValid()) {
            throw new BusinessException("上传文件不可见！");
        }
        $extension = $fileObject->getClientOriginalExtension();
        $name      = $fileObject->getClientOriginalName();
        $fileName  = date('YmdHis') . $name;
        $dir = storage_path() . '/'. $dir . '/'. $brandId .'/' . $merchantId;
        $targetFile = $fileObject->move($dir, $fileName);
        if (!file_exists($targetFile)) {
            Log::info("文件地址 [$targetFile]");
            throw new BusinessException("上传文件失败！");
        }
        //将本地文件上传到文件服务器
        $info = FastDfs::uploadFile($targetFile);
        return [
            'local'  => $targetFile,
            'remote' => $info,
        ];
    }
}
