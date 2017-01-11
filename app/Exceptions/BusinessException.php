<?php
namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    // 重定义构造器使 message 变为必须被指定的属性
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
  
    /**
     * 定义错误输出格式
     *
     * @return string
     */
    public function __toString()
    {
        $code = 'code: ' . $this->getCode() . PHP_EOL;
        $file = 'file: ' . $this->getFile() . PHP_EOL;
        $line = 'line: ' . $this->getLine() . PHP_EOL;
        $message = 'message: ' . $this->getMessage() . PHP_EOL;
        
        return $file . $line . $code . $message;
    }
    
    /**
     * 定义需要输出的提示信息
     *
     * @return string
     */
    public function notice()
    {
        return $this->getMessage();
    }
}
