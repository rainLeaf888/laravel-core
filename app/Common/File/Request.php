<?php
/**
 * @导出csv公共类
 */
namespace App\Common\File;

class Request
{
    private $params = [];

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function getParam($key = '')
    {
        if ($key) {
            return $this->params[$key];
        } else {
            return $this->params;
        }
    }
}
