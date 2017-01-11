<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Operations\Services\Task\TaskQueue;
use App\Exceptions\BusinessException;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;

class YazuoJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    //任务ID
    protected $taskId = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($taskId = 0)
    {
        $this->taskId = $taskId;
    }

    /**
     * 获取任务的ID
     *
     * @return []
     */
    public function getTaskById()
    {
        $task = new TaskQueue();

        $result = $task->getTask($this->taskId);

        if (empty($result)) {
            Log::info("任务不存在[".$this->taskId."]");
            throw new BusinessException("任务不存在");
        }

        return $result;
    }

    public function updateTask($status, $message = '', $fileName = '', $fileSize = 0)
    {
        //更新tasks_queue表
        $task = new TaskQueue();
        $data['result']    = $message;
        $data['status']    = $status;
        $data['file_name'] = $fileName;
        $data['file_size'] = $fileSize;
        $data['do_time']   = date('Y-m-d H:i:s');
        $task->updateTask($this->taskId, $data);
    }
}
