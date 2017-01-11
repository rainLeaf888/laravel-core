<?php
/**
 * @file 统一返回格式
 */
namespace App\Common\Tools;

use Response;

class YzResponse
{
    public function json($flag = 1, $message = '', $data = [], $record = [])
    {
        $response = [
            'flag' => $flag,
            'message' => $message,
            'data' => $data
        ];
        if (!empty($record)) {
            $response['record'] = $record;
        }
        return Response::json($response);
    }

    public function file($file)
    {
        if (!file_exists($file)) {
            die('文件不存在');
        }
        $filename = preg_replace('/^.+[\\\\\\/]/', '', $file);
        header("Content-type: application/octet-stream");
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($filename);
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } elseif (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        header("Content-Length: " . filesize($file));

        readfile($file);
    }
}
