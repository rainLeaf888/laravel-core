<?php
/**
 * @file 统计工具，包括运行时间的统计
 */
namespace App\Common\Tools;

use Illuminate\Support\Facades\Log;

class Statistic
{
    /**
     * 开始执行
     * @return 返回开始执行的时间节点
     */
    public function start()
    {
        //开始内存
        $memory = memory_get_usage();
        //开始时间
        $time = microtime(true);
        return [
            'memory' => $memory,
            'time'   => $time,
        ];
    }

    /**
     * 停止执行
     * @param  $time 开始执行的时间
     * @return 返回执行时间
     */
    public function end($start, $desc)
    {
        $time        = microtime(true);
        $memory      = memory_get_usage();
        $processTime = round($time-$start['time'], 2);
        $processMem  = round(($memory-$start['memory'])/1024/1024, 2);
        Log::info($desc . "执行时间为: " . $processTime . ' 秒'.' 内存使用为: ' . $processMem . ' MB');
    }
}
